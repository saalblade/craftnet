<template>
    <div class="wrapper">
        <screenshot-modal v-if="showingScreenshotModal"></screenshot-modal>
        <header :class="{sticky: stickyHeader}">
            <a ref="sidebarToggle" class="sidebar-toggle" @click.prevent="toggleSidebar()">
                <i class="fas fa-bars"></i>
                <font-awesome-icon :icon="icon" />
            </a>

            <h2><router-link to="/">Craft Plugins</router-link></h2>

            <div class="search" :class="{open: searchVisible}">
                <search-form ref="searchForm" @searchQueryBlur="searchQueryBlur()" />
                
                <a class="search-toggle" @click="showSearch()">
                    <font-awesome-icon :icon="iconSearch" />
                </a>
            </div>

            <div class="nav">
                <navigation></navigation>
            </div>
        </header>

        <div class="main">
            <div v-if="loading" class="loading-wrapper">
                <div class="loading">Loading…</div>
            </div>

            <template v-else>
                <transition :name="transitionName">
                    <!--<div v-if="computedShowingSidebar" class="sidebar showing-sidebar">-->
                    <div class="sidebar" :class="{'showing-sidebar': computedShowingSidebar, sticky: stickyHeader}">
                        <div class="sidebar-main">
                            <ul class="categories">
                                <li v-for="category in categories">
                                    <nuxt-link :to="'/categories/'+category.slug">
                                        <img :src="category.iconUrl" height="24" />
                                        {{ category.title }}
                                    </nuxt-link>
                                </li>
                            </ul>

                            <div class="nav">
                                <h3>Switch Sites</h3>
                                <navigation></navigation>
                            </div>
                        </div>
                    </div>
                </transition>

                <div ref="view" class="view">
                    <div v-if="pageMeta && showSeoMeta" class="seo-meta">
                        <ul>
                            <li><strong>Title:</strong> {{pageMeta.title}}</li>
                            <li><strong>Description:</strong> {{pageMeta.description}}</li>
                        </ul>
                    </div>
                    <nuxt/>
                </div>
            </template>
        </div>
    </div>
</template>

<script>
    import {mapState} from 'vuex'
    import Navigation from '../components/Navigation'
    import SearchForm from '../components/SearchForm'
    import ScreenshotModal from '../components/ScreenshotModal'
    import FontAwesomeIcon from '@fortawesome/vue-fontawesome'
    import faBars from '@fortawesome/fontawesome-free-solid/faBars'
    import faTimes from '@fortawesome/fontawesome-free-solid/faTimes'
    import faSearch from '@fortawesome/fontawesome-free-solid/faSearch'

    export default {

        data() {
            return {
                loading: false,
                bigScreen: false,
                searchVisible: false,
            }
        },

        components: {
            Navigation,
            SearchForm,
            FontAwesomeIcon,
            ScreenshotModal,
        },

        computed: {

            icon () {
                if (this.showingSidebar) {
                    return faTimes
                }

                return faBars
            },

            iconSearch() {
                return faSearch
            },

            ...mapState({
                showingSidebar: state => state.app.showingSidebar,
                showingScreenshotModal: state => state.app.showingScreenshotModal,
                pageMeta: state => state.app.pageMeta,
                stickyHeader: state => state.app.stickyHeader,
                searchQuery: state => state.pluginStore.searchQuery,
                categories: state => state.pluginStore.categories,
                featuredPlugins: state => state.pluginStore.featuredPlugins,
            }),

            showSeoMeta() {
                return process.env.showSeoMeta
            },

            computedShowingSidebar() {
                if(this.bigScreen) {
                    return true
                }

                return this.showingSidebar
            },

            transitionName() {
                if(this.bigScreen) {
                    return null
                }

                return 'fade'
            }

        },

        methods: {

            /**
             * Toggles the sidebar.
             */
            toggleSidebar() {
                this.$store.commit('app/toggleSidebar')
            },

            handleResize() {
                const windowWidth = document.documentElement.clientWidth;

                if(windowWidth > 991) {
                    this.bigScreen = true

                    if(this.showingSidebar) {
                        this.toggleSidebar()
                    }
                } else {
                    this.bigScreen = false
                }
            },

            onViewScroll(e) {
                this.$bus.$emit('viewScroll', e)
            },

            showSearch() {
                this.searchVisible = true
                const searchQueryInput = this.$refs.searchForm.$refs.searchQuery

                this.$nextTick(() => {
                    searchQueryInput.focus()
                })
            },

            searchQueryBlur() {
                this.searchVisible = false
            }
        },

        watch: {
            '$route.path': function() {
                this.$store.commit('app/updateStickyHeader', true)
            }
        },

        created() {
            // console.log('env', process.env.NODE_ENV);

            if (this.$route.query.q) {
                this.$store.commit('app/updateSearchQuery', this.$route.query.q)
            }
        },

        mounted () {
            window.addEventListener('resize', this.handleResize)
            // this.handleResize()
            window.dispatchEvent(new Event('resize'));

            this.$refs.view.addEventListener('scroll', this.onViewScroll)
        },
    }
</script>

<template>
    <div class="wrapper">
        <screenshot-modal v-if="showingScreenshotModal"></screenshot-modal>

        <header>
            <div>
                <a ref="sidebarToggle" class="sidebar-toggle" @click.prevent="toggleSidebar()">
                    <i class="fas fa-bars"></i>
                    <font-awesome-icon :icon="icon" />
                </a>

                <h2><router-link to="/">Craft Plugins</router-link></h2>

                <plugin-search-form></plugin-search-form>

                <navigation></navigation>
            </div>
        </header>

        <div class="main">
            <div v-if="loading" class="loading-wrapper">
                <div class="loading">Loading…</div>
            </div>

            <template v-else>
                <transition :name="transitionName">
                    <div v-if="computedShowingSidebar" class="sidebar showing-sidebar">
                    <!--<div class="sidebar" :class="{ 'showing-sidebar': showingSidebar }">-->
                        <h3 class="first">{{ "Categories" }}</h3>
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
                </transition>

                <div class="view">
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
    import PluginSearchForm from '../components/PluginSearchForm'
    import ScreenshotModal from '../components/ScreenshotModal'
    import FontAwesomeIcon from '@fortawesome/vue-fontawesome'
    import faBars from '@fortawesome/fontawesome-free-solid/faBars'
    import faTimes from '@fortawesome/fontawesome-free-solid/faTimes'

    export default {

        data() {
            return {
                loading: false,
                bigScreen: false,
            }
        },

        components: {
            Navigation,
            PluginSearchForm,
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

            ...mapState({
                showingSidebar: state => state.app.showingSidebar,
                showingScreenshotModal: state => state.app.showingScreenshotModal,
                pageMeta: state => state.app.pageMeta,
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

                if(window.outerWidth > 991) {
                    this.bigScreen = true

                    if(this.showingSidebar) {
                        this.toggleSidebar()
                    }
                } else {
                    this.bigScreen = false
                }

                // console.log('this.computedShowingSidebar', this.computedShowingSidebar);

                // console.log('window resize', window.outerWidth, this.showingSidebar)
                //
                // if(window.outerWidth > 1200) {
                //     if(!this.showingSidebar) {
                //         this.toggleSidebar()
                //     }
                // } else {
                //
                // }
            },
        },

        created() {
            // console.log('env', process.env.NODE_ENV);

            if (this.$route.query.q) {
                this.$store.commit('app/updateSearchQuery', this.$route.query.q)
            }
        },

        mounted () {
            window.addEventListener('resize', this.handleResize)
            this.handleResize()
        },
    }
</script>

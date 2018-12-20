<template>
    <div class="wrapper">
        <transition name="fade">
            <screenshot-modal v-if="showingScreenshotModal"></screenshot-modal>
        </transition>
        <header>
            <a ref="sidebarToggle" class="sidebar-toggle" @click.prevent="toggleSidebar()">
                <i class="fas fa-bars"></i>
                <font-awesome-icon :icon="icon" />
            </a>

            <h1><router-link to="/">Craft Plugin Store</router-link></h1>

            <div class="search" :class="{open: searchVisible}">
                <search-form ref="searchForm" @searchQueryBlur="searchQueryBlur()" />
                
                <a class="search-toggle" @click="showSearch()">
                    <font-awesome-icon icon="search" />
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
                <div class="sidebar" :class="{'showing-sidebar': showingSidebar}">
                    <div class="sidebar-main">
                        <ul class="categories">
                            <li v-for="category in categories">
                                <nuxt-link :to="'/categories/'+category.slug">
                                    <img :src="category.iconUrl" width="24" height="24" />
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

    export default {

        data() {
            return {
                loading: false,
                bigScreen: false,
                searchVisible: false,
                showingSidebar: false,
            }
        },

        components: {
            Navigation,
            SearchForm,
            ScreenshotModal,
        },

        computed: {

            icon () {
                if (this.showingSidebar) {
                    return 'times'
                }

                return 'bars'
            },

            ...mapState({
                // showingSidebar: state => state.app.showingSidebar,
                showingScreenshotModal: state => state.app.showingScreenshotModal,
                pageMeta: state => state.app.pageMeta,
                searchQuery: state => state.pluginStore.searchQuery,
                categories: state => state.pluginStore.categories,
                featuredPlugins: state => state.pluginStore.featuredPlugins,
            }),

            showSeoMeta() {
                return process.env.showSeoMeta
            },

        },

        methods: {

            /**
             * Toggles the sidebar.
             */
            toggleSidebar() {
                // this.$store.commit('app/toggleSidebar')
                this.showingSidebar = !this.showingSidebar
            },

            handleResize() {
                // const windowWidth = document.documentElement.clientWidth;
                //
                // if(windowWidth > 991) {
                //     this.bigScreen = true
                //
                //     if(this.showingSidebar) {
                //         this.toggleSidebar()
                //     }
                // } else {
                //     this.bigScreen = false
                // }
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

<style lang="scss">
    /* Layout */

    .wrapper {
        header {
            @apply .flex .px-6 .py-3 .bg-grey-lighter .border-b .justify-between .relative .z-50;

            &.sticky {
                @apply .sticky .pin .z-10;
            }

            a.sidebar-toggle {
                @apply .self-center .text-grey-darker .mr-4;
                width: 14px;
            }

            h1 {
                @apply .text-lg .self-center .px-6 .py-2 .flex-no-shrink .my-0 .-ml-6;

                a {
                    @apply .text-grey-darker;
                }
            }

            .search {
                @apply .self-center;

                .search-form {
                    @apply .hidden;
                }

                &.open {
                    @apply .absolute;
                    top: 0.5rem;
                    left: 1.5rem;
                    right: 1.5rem;

                    .search-form {
                        @apply .block;
                    }

                    .search-toggle {
                        @apply .hidden;
                    }
                }
            }

            .nav {
                @apply .block .ml-2 .self-center;
            }
        }

        .main {
            .sidebar {
                @apply .overflow-y-auto .pin .z-40;
                -webkit-overflow-scrolling: touch;
                top: 53px;
                @apply .fixed;

                &.sticky {
                    @apply .sticky;
                }

                .sidebar-main {
                    @apply .px-6 .py-4;
                }

                nav {
                    @apply .-mx-6 .px-6 .pb-4 .mb-4;
                }
            }

            .sidebar {
                @apply .bg-grey-lighter;

                h3 {
                    @apply .mb-2 .mt-4;

                    &.first {
                        @apply .mt-0;
                    }
                }

                ul {
                    @apply .list-reset;

                    li {
                        a {
                            @apply .block .py-2 .no-underline .-mx-6 .px-6 .text-grey-darker;

                            img {
                                @apply .align-middle;
                            }

                            &:hover {
                                @apply .text-blue;
                            }

                            &.nuxt-link-active {
                                @apply .bg-grey-light;
                            }
                        }
                    }
                }
            }


            .sidebar {
                // transition: all .5s ease-out;
                transform: translateY(-100%);
                visibility: hidden;

                &.showing-sidebar {
                    // transition: all .5s ease-out;
                    transform: translateY(0);
                    visibility: visible;
                }
            }
        }
    }

    @media (min-width: 576px) {
        .wrapper {
            header {
                .search {
                    @apply .flex-1;

                    .search-form {
                        @apply .block;
                    }

                    .search-toggle {
                        @apply .hidden;
                    }
                }
            }
        }
    }

    @media (min-width: 992px) {
        .wrapper {
            @apply .flex .flex-col .absolute .pin;

            header {
                h1 {
                    @apply .w-64;
                }

                .sidebar-toggle {
                    @apply .hidden;
                }

                .nav {
                    ul {
                        @apply .list-reset;

                        li {
                            @apply .inline-block .ml-2;

                            a {
                                @apply .p-2;
                            }
                        }
                    }
                }
            }

            .main {
                @apply .flex .flex-row .flex-1;

                .sidebar {
                    @apply .w-64 .overflow-auto .block .border-r .relative .pin-none;

                    transition: none;
                    transform: translateY(0);
                    visibility: visible;

                    .nav {
                        @apply .hidden;
                    }
                }

                .view {
                    @apply .flex-1 .overflow-auto;
                }
            }
        }
    }


    /* SEO meta */

    .seo-meta {
        @apply .bg-blue-lighter .text-blue-darker .p-4 .rounded .mb-4;

        ul {
            @apply .list-reset;

            li {
                &:not(:last-child) {
                    @apply .mb-2;
                }
            }
        }
    }
</style>
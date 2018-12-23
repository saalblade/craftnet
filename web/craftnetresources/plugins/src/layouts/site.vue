<template>
    <div class="wrapper">
        <transition name="fade">
            <screenshot-modal v-if="showingScreenshotModal"></screenshot-modal>
        </transition>

        <!-- main -->
        <div class="main">

            <!-- loading -->
            <div v-if="loading" class="loading-wrapper">
                <div class="loading">Loading…</div>
            </div>

            <template v-else>

                <!-- sidebar -->
                <div class="sidebar" :class="{'showing-sidebar': showingSidebar}">
                    <header>
                        <a ref="sidebarToggle" class="sidebar-toggle" @click.prevent="toggleSidebar()">
                            <i class="fas fa-bars"></i>
                            <font-awesome-icon :icon="icon" />
                        </a>
                        <h1><router-link to="/">Craft Plugin Store</router-link></h1>
                    </header>

                    <div class="sidebar-navigation">
                        <div class="sidebar-main">
                            <plugin-search></plugin-search>

                            <ul class="categories">
                                <li v-for="category in categories">
                                    <nuxt-link :to="'/categories/'+category.slug">
                                        <img :src="category.iconUrl" width="24" height="24" />
                                        {{ category.title }}
                                    </nuxt-link>
                                </li>
                            </ul>

                            <h3>Switch Sites</h3>
                            <ul class="list-reset mb-0">
                                <p><a :href="craftIdUrl">Craft ID</a></p>
                                <p><nuxt-link to="/" exact>Craft Plugin Store</nuxt-link></p>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- view -->
                <div ref="view" class="view">
                    <seo-meta></seo-meta>
                    <nuxt/>
                </div>
            </template>
        </div>
    </div>
</template>

<script>
    import helpers from '../mixins/helpers'
    import {mapState} from 'vuex'
    import ScreenshotModal from '../components/ScreenshotModal'
    import PluginSearch from '../components/PluginSearch'
    import SeoMeta from '../components/SeoMeta'

    export default {

        mixins: [helpers],

        data() {
            return {
                loading: false,
                bigScreen: false,
                searchVisible: false,
            }
        },

        components: {
            ScreenshotModal,
            PluginSearch,
            SeoMeta,
        },

        computed: {

            icon () {
                if (this.showingSidebar) {
                    return 'times'
                }

                return 'bars'
            },

            ...mapState({
                showingSidebar: state => state.app.showingSidebar,
                showingScreenshotModal: state => state.app.showingScreenshotModal,
                searchQuery: state => state.pluginStore.searchQuery,
                categories: state => state.pluginStore.categories,
                featuredPlugins: state => state.pluginStore.featuredPlugins,
            }),

        },

        methods: {

            toggleSidebar() {
                this.$store.commit('app/toggleSidebar')
            },

            onViewScroll(e) {
                this.$bus.$emit('viewScroll', e)
            },

        },

        mounted () {
            window.addEventListener('resize', this.handleResize)
            window.dispatchEvent(new Event('resize'));

            this.$refs.view.addEventListener('scroll', this.onViewScroll)
        },
    }
</script>

<style lang="scss">
    .wrapper {
        header {
            @apply .flex .px-6 .py-3 .bg-grey-lighter .border-b .justify-between .relative .z-20;

            &.sticky {
                @apply .sticky .pin .z-10;
            }

            a.sidebar-toggle {
                @apply .self-center .text-grey-darker .mr-4;
                width: 14px;
            }

            h1 {
                @apply .flex-1 .text-lg .self-center .px-6 .py-2 .flex-no-shrink .my-0 .-ml-6;

                a {
                    @apply .text-grey-darker;
                }
            }

            .nav {
                @apply .block .ml-2 .self-center;
            }
        }

        .main {
            .sidebar {
                @apply .sticky .pin-t .w-full;

                &.showing-sidebar {
                    .sidebar-navigation {
                        // transition: all .5s ease-out;
                        transform: translateY(0);
                        visibility: visible;
                    }
                }

                .sidebar-navigation {
                    // transition: all .5s ease-out;
                    transform: translateY(-100%);
                    visibility: hidden;
                }
            }

            .sidebar-navigation {
                @apply .fixed .overflow-y-auto .pin .z-10 .bg-grey-lighter;
                -webkit-overflow-scrolling: touch;
                top: 63px;

                &.sticky {
                    @apply .sticky;
                }

                .sidebar-main {
                    @apply .px-6 .py-4;
                }

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

                nav {
                    @apply .-mx-6 .px-6 .pb-4 .mb-4;
                }
            }
        }
    }

    @media (min-width: 992px) {
        .wrapper {
            @apply .flex .flex-row .absolute .pin;

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
                    @apply .flex .flex-col .relative .pin-none .w-64 .border-r;

                    .sidebar-navigation {
                        @apply .flex-1;
                        // transition: all .5s ease-out;
                        transform: translateY(0);
                        visibility: visible;
                    }
                }

                .sidebar-navigation {
                    @apply .overflow-auto .block .relative .pin-none;
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
</style>
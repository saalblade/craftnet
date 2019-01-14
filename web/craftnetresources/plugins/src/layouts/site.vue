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
                <!-- navigation -->
                <div class="navigation" :class="{'showing-navigation': showingNavigation}">
                    <header>
                        <a ref="navigationToggle" class="navigation-toggle" @click.prevent="toggleNavigation()">
                            <i class="fas fa-bars"></i>
                            <font-awesome-icon :icon="icon" />
                        </a>
                        <h1><router-link to="/">Craft Plugin Store</router-link></h1>
                    </header>

                    <div class="navigation-main">
                        <plugin-search></plugin-search>

                        <ul>
                            <li v-for="category in categories">
                                <nuxt-link :to="'/categories/'+category.slug">
                                    <img :src="category.iconUrl" width="24" height="24" />
                                    {{ category.title }}
                                </nuxt-link>
                            </li>
                        </ul>

                        <h3>Switch Sites</h3>
                        <ul>
                            <p><nuxt-link to="/" exact>Craft Plugin Store</nuxt-link></p>
                            <p><a :href="craftIdUrl()">Craft ID</a></p>
                        </ul>
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
    import {mapState} from 'vuex'
    import helpers from '../mixins/helpers'
    import ScreenshotModal from '../components/ScreenshotModal'
    import SeoMeta from '../components/SeoMeta'
    import PluginSearch from '../components/PluginSearch'

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
            SeoMeta,
            PluginSearch,
        },

        computed: {

            ...mapState({
                showingScreenshotModal: state => state.app.showingScreenshotModal,
                searchQuery: state => state.pluginStore.searchQuery,
                featuredPlugins: state => state.pluginStore.featuredPlugins,
                showingNavigation: state => state.app.showingNavigation,
                categories: state => state.pluginStore.categories,
            }),

            icon () {
                if (this.showingNavigation) {
                    return 'times'
                }

                return 'bars'
            },

        },

        methods: {

            onViewScroll(e) {
                this.$bus.$emit('viewScroll', e)
            },

            toggleNavigation() {
                this.$store.commit('app/toggleNavigation')
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
    // Wrapper

    .wrapper {


        // Navigation

        .navigation {
            @apply .sticky .pin-t .w-full;

            &.showing-navigation {
                .navigation-main {
                    // transition: all .5s ease-out;
                    transform: translateY(0);
                    visibility: visible;
                }
            }


            // Header

            header {
                @apply .flex .px-6 .py-3 .bg-grey-lighter .border-b .justify-between .relative .z-20;

                &.sticky {
                    @apply .sticky .pin .z-10;
                }

                a.navigation-toggle {
                    @apply .self-center .text-grey-darker .mr-4;
                    width: 14px;
                }

                h1 {
                    @apply .flex-1 .text-lg .self-center .px-6 .py-2 .flex-no-shrink .my-0 .-ml-6;

                    a {
                        @apply .text-grey-darker;
                    }
                }
            }


            // Navigation Main

            .navigation-main {
                @apply .fixed .overflow-y-auto .pin .z-10 .bg-grey-lighter .px-6 .py-4;
                -webkit-overflow-scrolling: touch;
                top: 63px;
                // transition: all .5s ease-out;
                transform: translateY(-100%);
                visibility: hidden;

                &.sticky {
                    @apply .sticky;
                }

                h3 {
                    @apply .mb-2 .mt-8 .uppercase .text-sm .text-grey;

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
        }
    }

    @media (min-width: 992px) {
        // Wrapper

        .wrapper {
            @apply .flex .flex-row .absolute .pin;


            // Main

            .main {
                @apply .flex .flex-row .flex-1;


                // Navigation

                .navigation {
                    @apply .flex .flex-col .relative .pin-none .w-64 .border-r;


                    // Header

                    header {
                        h1 {
                            @apply .w-64;
                        }

                        .navigation-toggle {
                            @apply .hidden;
                        }
                    }


                    // Navigation Main

                    .navigation-main {
                        @apply .flex-1 .overflow-auto .block .relative .pin-none;
                        transition: none;
                        transform: translateY(0);
                        visibility: visible;
                    }
                }


                // View

                .view {
                    @apply .flex-1 .overflow-auto;
                }
            }
        }
    }
</style>
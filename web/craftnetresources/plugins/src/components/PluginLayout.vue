<template>
    <div class="plugin-layout">
        <div ref="pluginDetailsHeader" class="plugin-details-header" :class="{scrolled: scrolled}">
            <div v-if="pluginSnippet" class="xcontainer">
                <div class="description">
                    <div class="icon">
                        <img v-if="pluginSnippet.iconUrl" :src="pluginSnippet.iconUrl" />
                        <img v-else :src="defaultPluginSvg" />
                    </div>

                    <div class="name">
                        <h1>
                            {{ pluginSnippet.name }}
                        </h1>

                        <div class="developer">
                            <router-link :to="'/developer/'+pluginSnippet.developerId">{{ pluginSnippet.developerName }}</router-link>
                        </div>
                    </div>

                    <a class="nav-toggle" @click="showNav=!showNav">
                        <template v-if="showNav"><font-awesome-icon icon="chevron-up" /></template>
                        <template v-else><font-awesome-icon icon="chevron-down" /></template>
                    </a>
                    <!--<div class="short-description">{{ pluginSnippet.shortDescription }}</div>-->
                    <!--<div><router-link :to="'/developer/'+pluginSnippet.developerId">{{ pluginSnippet.developerName }}</router-link></div>-->
                </div>
            </div>
        </div>

        <div class="xcontainer">
            <slot></slot>
        </div>
    </div>
</template>


<script>
    import {mapGetters} from 'vuex'
    import helpers from '../mixins/helpers'

    export default {

        mixins: [helpers],

        data() {
            return {
                scrolled: false,
                showNav: false,
            }
        },

        computed: {

            ...mapGetters({
                isCommercial: 'pluginStore/isCommercial',
                getPluginEditions: 'pluginStore/getPluginEditions',
            }),

            pluginSnippet() {
                return this.$store.getters['pluginStore/getPluginByHandle'](this.$route.params.handle)
            },

        },

        methods: {

            onScroll(scrollY) {
                if (this.$refs.pluginDetailsHeader) {
                    let headerHeight = this.$refs.pluginDetailsHeader.clientHeight

                    if (this.scrolled) {
                        headerHeight += 30
                    }

                    if (!this.scrolled && window.innerHeight < 992) {
                        headerHeight += this.$refs.pluginDetailsHeader.offsetTop
                    }

                    if (scrollY > headerHeight) {
                        this.scrolled = true
                    } else {
                        this.scrolled = false
                    }
                }
            },

            onViewScroll(e) {
                if(e) {
                    this.onScroll(e.target.scrollTop);
                }
            },

            onWindowResize() {
                if(window.innerHeight > 991) {
                    this.onViewScroll()
                } else {
                    this.onWindowScroll()
                }
            },

            onWindowScroll() {
                this.onScroll(window.scrollY);
            },
        },

        mounted() {
            this.$nextTick(() => {
                this.$store.commit('app/updateStickyHeader', false)
            })

            window.addEventListener('scroll', this.onWindowScroll)
            this.$bus.$on('viewScroll', this.onViewScroll)
            window.addEventListener('resize', this.onWindowResize)
            this.onWindowResize()
        },

        destroyed() {
            window.removeEventListener('scroll', this.onWindowScroll, true)
            this.$bus.$off('viewScroll')
            window.removeEventListener('resize', this.onWindowResize, true)
        }
    }

</script>

<style lang="scss">


    /* Plugin Details (plugin/_id) */

    .plugin-details-header {
        @apply .bg-white .mt-0 .py-6 .border-b;

        .description {
            @apply .flex;

            .icon {
                @apply .self-center;
                line-height: 0;
                width: 80px;

                img {
                    width: 100%;
                }
            }

            .name {
                @apply .ml-4 .self-center;

                h1 {
                    @apply .self-center .pb-0 .border-b-0 .text-2xl .mb-1;
                }
            }

            .nav-toggle {
                @apply .flex-1 .self-center .text-right;
            }
        }

        ul.nav {
            @apply .list-reset .self-center .py-4 .w-full;

            li {
                a:not(.btn) {
                    @apply .block .py-3 .border-t;

                    &.nuxt-link-active {
                        @apply .text-grey-dark;
                    }
                }
            }
        }

        &.scrolled {
            @apply .py-2 .sticky .pin-t .z-30 .w-full .border-b-0;
            margin-top: 30px;
            -webkit-box-shadow: 0 4px 2px -2px rgba(0,0,0,.1);
            box-shadow: 0 4px 2px -2px rgba(0,0,0,.1);

            .description {
                .icon {
                    width: 50px;
                    transition: all 0s ease-out;
                }

                .name {
                    h1 {
                        @apply .text-lg;
                    }

                    .developer {
                        @apply .hidden;
                    }
                }
            }
        }
    }

    @media (min-width: 768px) {
        .plugin-details-header {
            @apply .block;

            .xcontainer {
                @apply .flex .justify-between;
            }

            .description {
                .nav-toggle {
                    @apply .hidden;
                }
            }

            ul.nav {
                @apply .block .w-auto .py-0;

                li {
                    @apply .inline-block;

                    a:not(.btn) {
                        @apply .border-0 .px-4 .py-0;
                    }
                }

                li.buy {
                    @apply .ml-4;
                }
            }
        }
    }

</style>
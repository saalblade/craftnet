<template>
    <div class="plugin-layout">
        <div ref="pluginDetailsHeader" class="plugin-details-header">
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
            <hr>

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

    }

</script>

<style lang="scss">


    /* Plugin Details (plugin/_id) */

    .plugin-details-header {
        @apply .bg-white .mt-0 .pt-6;

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
                    @apply .self-center .pb-0 .border-b-0 .text-2xl .mt-0 .mb-1;
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
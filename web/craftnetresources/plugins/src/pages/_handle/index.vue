<template>
    <plugin-layout>
        <template v-if="pluginSnippet && plugin && !loading">
            <div class="plugin-details-body">
                <div class="plugin-description">
                    <template v-if="plugin.screenshotUrls.length">
                        <plugin-screenshots :images="plugin.screenshotUrls"></plugin-screenshots>
                        <hr>
                    </template>

                    <div v-if="longDescription" v-html="longDescription" class="readable"></div>
                    <p v-else>No description.</p>
                </div>

                <hr>

                <plugin-editions :plugin="plugin"></plugin-editions>

                <hr>

                <h2>Package Name</h2>
                <p>To install this plugin, search for its package name on the Plugin Store and click “Install”.</p>
                <copy-package :plugin="plugin"></copy-package>

                <hr>

                <h2>Informations</h2>
                <ul class="plugin-meta">
                    <li><span>{{ "Version"|t('app') }}</span> <strong>{{ plugin.version }}</strong></li>
                    <li><span>{{ "Last Update"|t('app') }}</span> <strong>{{ lastUpdate }}</strong></li>
                    <li v-if="plugin.activeInstalls > 0"><span>{{ "Active Installs"|t('app') }}</span> <strong>{{ plugin.activeInstalls | formatNumber }}</strong></li>
                    <li><span>{{ "Compatibility"|t('app') }}</span> <strong>{{ plugin.compatibility }}</strong></li>
                    <li><span>{{ "License"|t('app') }}</span> <strong>{{ licenseLabel }}</strong></li>

                    <li v-if="pluginCategories.length > 0">
                        <span>{{ "Categories"|t('app') }}</span>
                        <strong>
                            <template v-for="category, key in pluginCategories">
                                <a @click="viewCategory(category)">{{ category.title }}</a><template v-if="key < (pluginCategories.length - 1)">, </template>
                            </template>
                        </strong>
                    </li>
                </ul>
                <div class="clearfix"></div>

                <hr>

                <plugin-changelog></plugin-changelog>

                <hr>

                <div class="plugin-meta-links">
                    <h3>Links</h3>
                    <ul v-if="(plugin.documentationUrl || plugin.changelogUrl)">
                        <li v-if="plugin.documentationUrl"><a :href="plugin.documentationUrl"><font-awesome-icon icon="book" /> {{ "Documentation"|t('app') }}</a></li>
                        <li v-if="plugin.changelogUrl"><a :href="plugin.changelogUrl"><font-awesome-icon icon="certificate" /> {{ "Changelog"|t('app') }}</a></li>
                    </ul>
                </div>
            </div>
        </template>
        <template v-else>
            <div class="loading">Loading…</div>
        </template>
    </plugin-layout>
</template>

<script>
    import {mapState, mapGetters} from 'vuex'
    import PluginPricing from '../../components/PluginPricing'
    import PluginLayout from '../../components/PluginLayout'
    import CopyPackage from '../../components/CopyPackage'
    import PluginScreenshots from '../../components/PluginScreenshots'
    import PluginEditions from '../../components/PluginEditions'
    import PluginChangelog from '../../components/PluginChangelog'
    import helpers from '../../mixins/helpers'

    export default {

        async fetch({store, params}) {
            const pluginSnippet = store.getters['pluginStore/getPluginByHandle'](params.handle)

            if (!pluginSnippet) {
                return;
            }

            const pluginId = pluginSnippet.id

            let plugin = null

            if (store.state.pluginStore.plugin && store.state.pluginStore.plugin.id === pluginId) {
                plugin = store.state.pluginStore.plugin

                await store.commit('app/updatePageMeta', {
                    title: plugin.name,
                    description: plugin.shortDescription
                })

                return
            }

            store.commit('pluginStore/updatePluginDetails', null)

            await store.dispatch('pluginStore/getPluginDetails', pluginId)
                .then(response => {
                    store.commit('app/updatePageMeta', {
                        title: store.state.pluginStore.plugin.name,
                        description: store.state.pluginStore.plugin.name + ' plugin for Craft CMS.'
                    })
                })
                .catch(response => {
                    console.log('error')
                })
        },

        head () {
            if (this.pageMeta) {
                return {
                    title: this.pageMeta.title,
                    meta: [
                        { hid: 'description', name: 'description', content: this.pageMeta.description }
                    ]
                };
            }
        },

        layout: 'site',

        mixins: [helpers],

        components: {
            PluginPricing,
            PluginLayout,
            CopyPackage,
            PluginScreenshots,
            PluginEditions,
            PluginChangelog,
        },

        data() {
            return {
                actionsLoading: false,
                loading: false,
            }
        },

        computed: {

            ...mapState({
                pageMeta: state => state.app.pageMeta,
                categories: state => state.pluginStore.categories,
                plugin: state => state.pluginStore.plugin,
            }),

            ...mapGetters({
                getPluginEditions: 'pluginStore/getPluginEditions',
            }),

            lastUpdate() {
                const date = new Date(this.plugin.lastUpdate.replace(/\s/, 'T'))
                return this.$moment(date).format('l')
            },

            licenseLabel() {
                switch (this.plugin.license) {
                    case 'craft':
                        return 'Craft'

                    case 'mit':
                        return 'MIT'
                }
            },

            longDescription() {
                if (this.plugin.longDescription && this.plugin.longDescription.length > 0) {
                    return this.plugin.longDescription
                }
            },

            pluginCategories() {
                return this.categories.filter(c => {
                    return this.plugin.categoryIds.find(pc => pc == c.id)
                })
            },

            pluginSnippet() {
                return this.$store.getters['pluginStore/getPluginByHandle'](this.$route.params.handle)
            },

            editions() {
                return this.$store.getters['pluginStore/getPluginEditions'](this.pluginSnippet)
            },

        },

    }
</script>

<style lang="scss" scoped>
    /* Plugin Details Body */

    .plugin-details-body {
        @apply .mt-6 .leading-normal;

        .plugin-description {
            h1 {
                @apply .border-b-0;
            }
        }

        .plugin-meta-links {
            ul {
                @apply .list-reset;

                li {
                    @apply .my-2;
                }
            }
        }

        .plugin-carousel {
            @apply .bg-red .relative .mb-6;
            width: 100%;
            padding-top: 75%;

            .image {
                @apply .bg-grey-lightest .absolute .pin .text-center;

                img {
                    height: 100%;
                }
            }
        }
    }


    /* Plugin Meta */

    ul.plugin-meta {
        @apply .flex .flex-wrap .list-reset .-mx-4;

        li {
            @apply .mb-8 .px-4 .flex-no-shrink .flex-no-grow;
            flex-basis: 50%;

            span {
                @apply .block .text-grey;
            }
        }
    }

    @media only screen and (min-width: 672px) {
        ul.plugin-meta {
            li {
                flex-basis: 33.3333%;
            }
        }
    }

    @media only screen and (min-width: 1400px) {
        ul.plugin-meta {
            li {
                flex-basis: 25%;
            }
        }
    }

    @media only screen and (min-width: 1824px) {
        ul.plugin-meta {
            li {
                flex-basis: 20%;
            }
        }
    }
</style>
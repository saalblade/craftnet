<template>
    <plugin-layout>
        <template v-if="pluginSnippet && plugin && !loading">
            <div class="plugin-details-body">
                <div class="plugin-description">
                    <carousel identifier="plugin-carousel" :inline="true" :images="plugin.screenshotUrls"></carousel>
                    <div v-if="longDescription" v-html="longDescription" class="readable"></div>
                    <p v-else>No description.</p>
                </div>

                <div class="plugin-sidebar">
                    <div class="plugin-meta">
                        <ul class="plugin-meta-data">
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

                        <p v-if="isCommercial(pluginSnippet) && editions.length === 1" class="text-grey-dark">
                            Price includes 1 year of updates.<br />
                            {{ editions[0].renewalPrice|currency }}/year per site for updates after that.
                        </p>
                    </div>

                    <template v-if="isCommercial(pluginSnippet) && editions.length > 1">
                        <h3>Editions</h3>

                        <div class="plugin-editions mb-4">
                            <div class="plugin-editions-edition">
                                <h4><span class="edition-name">Standard</span></h4>
                                <ul>
                                    <li><font-awesome-icon icon="check"></font-awesome-icon> Drag and drop interface <font-awesome-icon icon="infoCircle" /></li>
                                    <li><font-awesome-icon icon="check"></font-awesome-icon> Multi-page forms <font-awesome-icon icon="infoCircle" /></li>
                                    <li><font-awesome-icon icon="check"></font-awesome-icon> GDPR compliant <font-awesome-icon icon="infoCircle" /></li>
                                    <li><font-awesome-icon icon="check"></font-awesome-icon> Data export <font-awesome-icon icon="infoCircle" /></li>
                                </ul>

                                <div class="buttons">
                                    <a href="#" class="btn btn-primary">{{ editions[0].price|currency }}</a>
                                </div>

                                <p class="mt-4 text-grey-dark mb-0">
                                    Price includes 1 year of updates.<br />
                                    {{ editions[0].renewalPrice|currency }}/year per site for updates after that.
                                </p>
                            </div>

                            <div class="plugin-editions-edition">
                                <h4><span class="edition-name">Pro</span></h4>
                                <ul>
                                    <li><font-awesome-icon icon="check" /> API integrations <font-awesome-icon icon="infoCircle" /></li>
                                    <li><font-awesome-icon icon="check" /> reCAPTCHA <font-awesome-icon icon="infoCircle" /></li>
                                    <li><font-awesome-icon icon="check" /> Advanced exporting <font-awesome-icon icon="infoCircle" /></li>
                                    <li><font-awesome-icon icon="check" /> Widgets <font-awesome-icon icon="infoCircle" /></li>
                                </ul>

                                <div class="buttons">
                                    <a href="#" class="btn btn-primary">$149</a>
                                </div>

                                <p class="mt-4 text-grey-dark mb-0">
                                    Price includes 1 year of updates.<br />
                                    $59/year per site for updates after that.
                                </p>
                            </div>
                        </div>
                    </template>

                    <h3>Package Name</h3>
                    <p>To install this plugin, search for its package name on the Plugin Store and click “Install”.</p>

                    <copy-package :plugin="plugin"></copy-package>

                    <hr>

                    <div class="plugin-meta-links">
                        <h3>Links</h3>
                        <ul v-if="(plugin.documentationUrl || plugin.changelogUrl)">
                            <li v-if="plugin.documentationUrl"><a :href="plugin.documentationUrl" target="_blank"><font-awesome-icon icon="book" /> {{ "Documentation"|t('app') }}</a></li>
                            <li v-if="plugin.changelogUrl"><a :href="plugin.changelogUrl" target="_blank"><font-awesome-icon icon="certificate" /> {{ "Changelog"|t('app') }}</a></li>
                        </ul>
                    </div>
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
    import PluginPricing from '../../../components/PluginPricing'
    import PluginLayout from '../../../components/PluginLayout'
    import CopyPackage from '../../../components/CopyPackage'
    import Carousel from '../../../components/Carousel'

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
            return {
                title: this.pageMeta.title,
                meta: [
                    { hid: 'description', name: 'description', content: this.pageMeta.description }
                ]
            };
        },

        layout: 'site',

        components: {
            PluginPricing,
            PluginLayout,
            CopyPackage,
            Carousel,
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
                isCommercial: 'pluginStore/isCommercial',
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
            }

        },

    }
</script>

<style lang="scss" scoped>
    .my-swiper {
        height: 300px;
        width: 100%;
        .swiper-slide {
            text-align: center;
            font-size: 38px;
            font-weight: 700;
            background-color: #eee;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .swiper-pagination {
            > .swiper-pagination-bullet {
                background-color: red;
            }
        }
    }
</style>
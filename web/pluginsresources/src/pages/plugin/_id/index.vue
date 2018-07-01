<template>
    <plugin-layout>
        <template v-if="pluginSnippet && plugin && !loading">
            <div class="plugin-details-body">
                <div class="plugin-description">
                    <div v-if="plugin.thumbnailUrls.length > 0" class="screenshots">
                        <a v-for="(screenshotUrl, screenshotKey) in plugin.screenshotUrls" @click="zoomScreenshot(screenshotKey)">
                            <img :src="screenshotUrl" />
                        </a>
                    </div>
                    <div v-if="longDescription" v-html="longDescription" class="readable"></div>
                    <p v-else>No description.</p>
                </div>

                <div class="plugin-sidebar">
                    <div class="plugin-meta">
                        <ul class="plugin-meta-data">
                            <li><span>{{ "Version"|t('app') }}</span> <strong>{{ plugin.version }}</strong></li>
                            <li><span>{{ "Last update"|t('app') }}</span> <strong>{{ lastUpdate }}</strong></li>
                            <li v-if="plugin.activeInstalls > 0"><span>{{ "Active installs"|t('app') }}</span> <strong>{{ plugin.activeInstalls | formatNumber }}</strong></li>
                            <li><span>{{ "Compatibility"|t('app') }}</span> <strong>{{ plugin.compatibility }}</strong></li>
                            <li v-if="pluginCategories.length > 0">
                                <span>{{ "Categories"|t('app') }}</span>
                                <strong>
                                    <template v-for="category, key in pluginCategories">
                                        <a @click="viewCategory(category)">{{ category.title }}</a><template v-if="key < (pluginCategories.length - 1)">, </template>
                                    </template>
                                </strong>
                            </li>
                            <li><span>{{ "License"|t('app') }}</span> <strong>{{ licenseLabel }}</strong></li>
                        </ul>
                    </div>

                    <h3>Package Name</h3>
                    <p>To install this plugin, search for its package name on the Plugin Store and click “Install”.</p>

                    <copy-package :plugin="plugin"></copy-package>


                    <div class="plugin-meta">
                        <h3>Links</h3>
                        <ul v-if="(plugin.documentationUrl || plugin.changelogUrl)" class="plugin-meta-links">
                            <li v-if="plugin.documentationUrl"><a :href="plugin.documentationUrl" class="btn fullwidth" target="_blank">{{ "Documentation"|t('app') }}</a></li>
                            <li v-if="plugin.changelogUrl"><a :href="plugin.changelogUrl" class="btn fullwidth" target="_blank">{{ "Changelog"|t('app') }}</a></li>
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
    import {mapState} from 'vuex'
    import PluginPricing from '../../../components/PluginPricing'
    import PluginLayout from '../../../components/PluginLayout'
    import CopyPackage from '../../../components/CopyPackage'

    export default {

        fetch({ params, store }) {
            const pluginId = parseInt(params.id)

            if (store.state.pluginStore.plugin && store.state.pluginStore.plugin.id === pluginId) {
                return;
            }

            store.commit('pluginStore/updatePluginDetails', null)

            return store.dispatch('pluginStore/getPluginDetails', pluginId)
                .then(response => {
                    console.log('success')
                })
                .catch(response => {
                    console.log('error')
                })
        },

        layout: 'site',

        components: {
            PluginPricing,
            PluginLayout,
            CopyPackage,
        },

        data() {
            return {
                actionsLoading: false,
                loading: false,
            }
        },

        head () {
            if (!this.plugin) {
                return
            }

            return {
                title: this.plugin.name + ' on the Plugin Store',
                meta: [
                    { hid: 'description', name: 'description', content: 'My plugin description' }
                ]
            }
        },

        computed: {

            ...mapState({
                categories: state => state.pluginStore.categories,
                plugin: state => state.pluginStore.plugin,
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
                return this.$store.getters['pluginStore/getPluginById'](this.$route.params.id)
            },
        },

    }
</script>
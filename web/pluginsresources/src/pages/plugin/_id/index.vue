<template>
    <div>
        <!--<router-link to="/">← Browse Plugins</router-link>-->

        <div v-if="pluginSnippet" class="plugin-details">
            <div class="plugin-details-header">
                <div class="plugin-icon-large">
                    <img v-if="pluginSnippet.iconUrl" :src="pluginSnippet.iconUrl" height="100" />
                    <img v-else :src="defaultPluginSvg" height="100" />
                </div>

                <div class="plugin-details-description">
                    <div class="details">
                        <h1>{{ pluginSnippet.name }}</h1>
                        <div>{{ pluginSnippet.shortDescription }}</div>
                        <div><router-link :to="'/developer/'+pluginSnippet.developerId">{{ pluginSnippet.developerName }}</router-link></div>
                    </div>

                    <div class="price">
                        <template v-if="pluginSnippet.editions[0].price != null && pluginSnippet.editions[0].price !== '0.00'">
                            <a class="price-btn" href="#pricing">Starting at {{ (pluginSnippet.editions[0].price / 4)|currency }}</a>
                        </template>
                        <template v-else>
                            <div class="price-btn">Free</div>
                        </template>
                    </div>
                </div>
            </div>

            <template v-if="plugin && !loading">
                <div class="plugin-details-body">
                    <div class="plugin-description">
                        <div v-if="plugin.thumbnailUrls.length > 0" class="screenshots">
                            <a v-for="(screenshotUrl, screenshotKey) in plugin.screenshotUrls" @click="zoomScreenshot(screenshotKey)">
                                <img :src="screenshotUrl" />
                            </a>
                        </div>
                        <div v-if="longDescription" v-html="longDescription" class="readable"></div>
                        <p v-else>No description.</p>

                        <template v-if="pluginSnippet.editions[0].price != null && pluginSnippet.editions[0].price !== '0.00'">
                            <h2 id="pricing" class="mt-4">Pricing</h2>
                            <table class="data w-full">
                                <tr>
                                    <th></th>
                                    <th>
                                        <div class="mb-2">Lite</div>
                                        <a href="#" class="btn inline-block">{{ (pluginSnippet.editions[0].price / 4)|currency }}</a>
                                    </th>
                                    <th>
                                        <div class="mb-2">Standard</div>
                                        <a href="#" class="btn inline-block">{{ (pluginSnippet.editions[0].price * 1)|currency }}</a>
                                    </th>
                                </tr>
                                <tr>
                                    <th>Feature description</th>
                                    <td>Yes</td>
                                    <td>Yes</td>
                                </tr>
                                <tr>
                                    <th>Feature description</th>
                                    <td>Yes</td>
                                    <td>Yes</td>
                                </tr>
                                <tr>
                                    <th>Feature description</th>
                                    <td>Yes</td>
                                    <td>Yes</td>
                                </tr>
                                <tr>
                                    <th>Feature description</th>
                                    <td>No</td>
                                    <td>Yes</td>
                                </tr>
                                <tr>
                                    <th>Feature description</th>
                                    <td>No</td>
                                    <td>Yes</td>
                                </tr>
                                <tr>
                                    <th>Feature description</th>
                                    <td>No</td>
                                    <td>Yes</td>
                                </tr>
                                <tr>
                                    <th>Feature description</th>
                                    <td>No</td>
                                    <td>Yes</td>
                                </tr>
                            </table>
                        </template>
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
                                <li v-if="pluginSnippet.editions[0].renewalPrice">
                                    <span>{{ "Renewal price"|t('app') }}</span>
                                    <strong>{{ "{price}/year"|t('app', { price: $root.$options.filters.currency(pluginSnippet.editions[0].renewalPrice) }) }}</strong>
                                </li>
                            </ul>

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
        </div>
    </div>
</template>

<script>
    import {mapState} from 'vuex'

    export default {

        fetch({ params, store }) {
            const pluginId = params.id

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

        data() {
            return {
                actionsLoading: false,
                loading: false,
            }
        },

        head () {
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
<template>
    <div class="xcontainer py-6">
        <h1 class="border-b py-2 mt-0 mb-0">Results for “{{ searchQuery }}”</h1>

        <plugin-grid :plugins="pluginsToRender"></plugin-grid>
    </div>
</template>

<script>
    import {mapState} from 'vuex'
    import filter from 'lodash/filter'
    import includes from 'lodash/includes'
    import PluginGrid from '../../components/PluginGrid'

    export default {

        async fetch ({ store, query }) {
            await store.commit('app/updatePageMeta', {
                title: query.q + ' - Craft Plugins Search',
                description: 'Search results for “' + query.q + '”.'
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
            PluginGrid,
        },

        watch: {

            searchQuery(val) {
                this.$store.commit('app/updatePageMeta', {
                    title: val + ' - Craft Plugins Search',
                    description: 'Search results description'
                })
            }

        },

        computed: {

            searchQuery() {
                return this.$route.query.q
            },

            ...mapState({
                plugins: state => state.pluginStore.plugins,
                pageMeta: state => state.app.pageMeta,
            }),

            pluginsToRender() {
                let searchQuery = this.searchQuery

                if (!searchQuery) {
                    this.$emit('hideResults')
                    return []
                }

                this.$emit('showResults')

                return filter(this.plugins, o => {
                    if (o.packageName && includes(o.packageName.toLowerCase(), searchQuery.toLowerCase())) {
                        return true
                    }

                    if (o.name && includes(o.name.toLowerCase(), searchQuery.toLowerCase())) {
                        return true
                    }

                    if (o.shortDescription && includes(o.shortDescription.toLowerCase(), searchQuery.toLowerCase())) {
                        return true
                    }

                    if (o.description && includes(o.description.toLowerCase(), searchQuery.toLowerCase())) {
                        return true
                    }

                    if (o.developerName && includes(o.developerName.toLowerCase(), searchQuery.toLowerCase())) {
                        return true
                    }

                    if (o.developerUrl && includes(o.developerUrl.toLowerCase(), searchQuery.toLowerCase())) {
                        return true
                    }

                    if (o.keywords.length > 0) {
                        for (let i = 0; i < o.keywords.length; i++) {
                            if (includes(o.keywords[i].toLowerCase(), searchQuery.toLowerCase())) {
                                return true
                            }
                        }
                    }
                })
            },

        },
    }
</script>
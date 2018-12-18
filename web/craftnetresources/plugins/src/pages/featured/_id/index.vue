<template>
    <div v-if="featuredPlugin" class="xcontainer py-6">
        <h1>{{ featuredPlugin.title }}</h1>
        <plugin-grid :plugins="getPluginsByIds(featuredPlugin.plugins)"></plugin-grid>
    </div>
</template>

<script>
    import {mapState, mapGetters} from 'vuex'
    import PluginGrid from '../../../components/PluginGrid'

    export default {

        async fetch ({ store, params }) {
            const featuredPlugin = store.getters['pluginStore/getFeaturedPlugin'](params.id)

            await store.commit('app/updatePageMeta', {
                title: featuredPlugin.title,
                description: featuredPlugin.description,
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

        computed: {

            ...mapState({
                pageMeta: state => state.app.pageMeta,
            }),

            ...mapGetters({
                getFeaturedPlugin: 'pluginStore/getFeaturedPlugin',
                getPluginsByIds: 'pluginStore/getPluginsByIds',
            }),

            featuredPlugin() {
                return this.getFeaturedPlugin(this.$route.params.id)
            }

        },

    }
</script>
<template>
    <div>
        <template v-if="featuredPlugins">
            <template v-for="featuredPlugin in featuredPlugins">
                <router-link class="float-right" :to="'/featured/'+featuredPlugin.slug">{{ "See all" }}</router-link>

                <div>
                    <h2 class="mb-4">{{ featuredPlugin.title }}</h2>
                    <div class="mb-8">
                        <plugin-grid :plugins="getPluginsByIds(featuredPlugin.plugins.slice(0, featuredPlugin.limit))"></plugin-grid>
                    </div>
                </div>
            </template>
        </template>

        <div class="mt-6 text-right text-grey-lighter">{{ plugins.length }}</div>
    </div>
</template>

<script>
    import {mapState, mapGetters} from 'vuex'
    import PluginGrid from '../components/PluginGrid'

    export default {

        async fetch ({ store, params }) {
            await store.commit('app/updatePageMeta', {
                title: 'Craft Plugins',
                description: 'Plugins for Craft CMS.'
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
                featuredPlugins: state => state.pluginStore.featuredPlugins,
                plugins: state => state.pluginStore.plugins,
                pageMeta: state => state.app.pageMeta,
            }),

            ...mapGetters({
                getPluginsByIds: 'pluginStore/getPluginsByIds',
            }),

        }
    }
</script>
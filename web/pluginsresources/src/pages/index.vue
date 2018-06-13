<template>
    <div>
        <template v-if="featuredPlugins">
            <template v-for="featuredPlugin in featuredPlugins">
                <router-link class="float-right" :to="'/featured/'+featuredPlugin.id">{{ "See all" }}</router-link>

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

        layout: 'site',

        components: {
            PluginGrid,
        },

        computed: {

            ...mapState({
                featuredPlugins: state => state.pluginStore.featuredPlugins,
                plugins: state => state.pluginStore.plugins,
            }),

            ...mapGetters({
                getPluginsByIds: 'pluginStore/getPluginsByIds',
            }),

        }
    }
</script>
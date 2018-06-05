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
    </div>
</template>


<script>
    import {mapState, mapGetters} from 'vuex'

    export default {

        components: {
            PluginGrid: require('../components/PluginGrid'),
            Navigation: require('../components/Navigation'),
        },

        computed: {

            ...mapState({
                featuredPlugins: state => state.pluginStore.featuredPlugins,
                plugins: state => state.pluginStore.plugins,
            }),

            ...mapGetters({
                getPluginsByIds: 'getPluginsByIds',
            }),

        }
    }
</script>
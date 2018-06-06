<template>
    <div class="wrapper">
        <div v-if="loading">
            Loading…
        </div>

        <template v-else>
            <div class="sidebar" :class="{ 'showing-sidebar': showingSidebar }">
                <div class="header">
                    <div class="actions-left">
                        <a class="sidebar-toggle" @click.prevent="toggleSidebar()"><i class="fas fa-bars"></i></a>
                    </div>

                    <div>
                        <h2><router-link to="/">Craft 3 Plugins</router-link></h2>
                    </div>
                </div>

                <!--<plugin-search @showResults="showingSearchResults = true" @hideResults="showingSearchResults = false" :plugins="plugins"></plugin-search>-->

                <navigation></navigation>
            </div>
            <div class="view">
                <template v-if="searchQuery">
                    <plugin-search-results></plugin-search-results>
                </template>
                <template v-else>
                    <router-view :key="$route.path"></router-view>
                </template>
            </div>
        </template>
    </div>
</template>

<script>
    import {mapState} from 'vuex'
    import router from './router';

    export default {
        router,

        data() {
            return {
                loading: true,
                showingSidebar: false,
            }
        },

        components: {
            PluginSearch: require('./components/PluginSearch'),
            PluginSearchResults: require('./components/PluginSearchResults'),
            Navigation: require('./components/Navigation'),
        },

        computed: {

            ...mapState({
                plugins: state => state.pluginStore.plugins,
                searchQuery: state => state.pluginStore.searchQuery,
            }),

        },

        methods: {

            /**
             * Toggles the sidebar.
             */
            toggleSidebar() {
                this.showingSidebar = !this.showingSidebar;
            },

        },

        created() {
            this.$store.dispatch('getPluginStoreData')
                .then(response => {
                    this.loading = false
                    console.log('success')
                })
                .catch(response => {
                    this.loading = false
                    console.log('error', response)
                })
        }

    }
</script>

<style lang="scss">
    @import './../sass/main.scss';
</style>
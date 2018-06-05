<template>
    <div class="wrapper">
        <div v-if="loading">
            Loading…
        </div>

        <template v-else>
            <div class="sidebar">
                <h2 class="mb-4"><router-link to="/">Craft 3 Plugins</router-link></h2>

                <!--<plugin-search @showResults="showingSearchResults = true" @hideResults="showingSearchResults = false" :plugins="plugins"></plugin-search>-->

                <plugin-search-form></plugin-search-form>

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
            }
        },

        components: {
            PluginSearch: require('./components/PluginSearch'),
            PluginSearchForm: require('./components/PluginSearchForm'),
            PluginSearchResults: require('./components/PluginSearchResults'),
            Navigation: require('./components/Navigation'),
        },

        computed: {

            ...mapState({
                plugins: state => state.pluginStore.plugins,
                searchQuery: state => state.pluginStore.searchQuery,
            }),

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
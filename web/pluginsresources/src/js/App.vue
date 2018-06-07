<template>
    <div class="wrapper">
        <div v-if="loading" class="loading-wrapper">
            <div class="loading">Loading…</div>
        </div>

        <template v-else>
            <div class="sidebar" :class="{ 'showing-sidebar': showingSidebar }">
                <div class="header">
                    <div class="actions-left">
                        <a ref="sidebarToggle" class="sidebar-toggle" @click.prevent="toggleSidebar()">
                            <i class="fas fa-bars"></i>
                        </a>
                    </div>

                    <div>
                        <h2><router-link to="/">Craft 3 Plugins</router-link></h2>
                    </div>
                </div>

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
            PluginSearchResults: require('./components/PluginSearchResults'),
            Navigation: require('./components/Navigation'),
        },

        computed: {

            ...mapState({
                plugins: state => state.pluginStore.plugins,
                searchQuery: state => state.pluginStore.searchQuery,
            }),

        },

        watch: {

            showingSidebar(value) {
                const svg = this.$refs.sidebarToggle.querySelector("[data-fa-i2svg]");

                if(value) {
                    svg.classList.remove('fa-bars')
                    svg.classList.add('fa-times')
                } else {
                    svg.classList.add('fa-bars')
                    svg.classList.remove('fa-times')
                }
            }

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
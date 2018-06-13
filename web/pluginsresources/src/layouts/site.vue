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
                <nuxt/>
            </div>
        </template>
    </div>
</template>

<script>
    import {mapState} from 'vuex'
    import Navigation from '../components/Navigation'

    export default {

        data() {
            return {
                loading: false,
                showingSidebar: false,
            }
        },

        components: {
            Navigation,
        },

        computed: {

            ...mapState({
                searchQuery: state => state.pluginStore.searchQuery,
            }),

        },

        created() {
            console.log('env', process.env.NODE_ENV);
        }
    }
</script>

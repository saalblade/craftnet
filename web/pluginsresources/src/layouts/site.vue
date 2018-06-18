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
                            <font-awesome-icon :icon="icon" />
                        </a>
                    </div>

                    <div>
                        <h2><router-link to="/">Craft Plugins</router-link></h2>
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
    import FontAwesomeIcon from '@fortawesome/vue-fontawesome'
    import faBars from '@fortawesome/fontawesome-free-solid/faBars'
    import faTimes from '@fortawesome/fontawesome-free-solid/faTimes'

    export default {

        data() {
            return {
                loading: false,
                showingSidebar: false,
            }
        },

        components: {
            Navigation,
            FontAwesomeIcon,
        },

        computed: {

            icon () {
                if (this.showingSidebar) {
                    return faTimes
                }

                return faBars
            },

            ...mapState({
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
            console.log('env', process.env.NODE_ENV);
        }
    }
</script>

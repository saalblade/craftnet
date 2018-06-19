<template>
    <div class="wrapper">
        <header>
            <div>
                <a ref="sidebarToggle" class="sidebar-toggle" @click.prevent="toggleSidebar()">
                    <i class="fas fa-bars"></i>
                    <font-awesome-icon :icon="icon" />
                </a>

                <h2><router-link to="/">Craft Plugins</router-link></h2>

                <nav>
                    <ul>
                        <li><a href="#">Craft Plugins</a></li>
                        <li><a href="#">Craft ID</a></li>
                    </ul>
                </nav>
            </div>
        </header>

        <div class="main">
            <div v-if="loading" class="loading-wrapper">
                <div class="loading">Loading…</div>
            </div>

            <template v-else>
                <div class="sidebar" :class="{ 'showing-sidebar': showingSidebar }">
                    <navigation></navigation>
                </div>
                <div class="view">
                    <nuxt/>
                </div>
            </template>
        </div>
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
                showingSidebar: state => state.app.showingSidebar,
                searchQuery: state => state.pluginStore.searchQuery,
            }),

        },

        methods: {

            /**
             * Toggles the sidebar.
             */
            toggleSidebar() {
                this.$store.commit('app/toggleSidebar')
            },

        },

        created() {
            console.log('env', process.env.NODE_ENV);
        }
    }
</script>

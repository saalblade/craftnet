<template>
    <div>
        <div class="developer-card">
            <div class="photo">
                <template v-if="!loading && developer">
                    <img :src="developer.photoUrl" />
                </template>
            </div>

            <div class="developer-details">
                <div class="developer-details-content">
                    <template v-if="loading || !developer">
                        <div class="loading">Loading…</div>
                    </template>
                    <template v-else>
                        <h1>{{ developer.developerName }}</h1>
                        <ul>
                            <li>{{ developer.location }}</li>
                        </ul>

                        <ul class="links">
                            <li><a class="btn" :href="developer.developerUrl">{{ "Website" }}</a></li>
                            <li><a class="btn" :href="developer.developerUrl">{{ "Contact" }}</a></li>
                        </ul>
                    </template>

                </div>
            </div>
        </div>

        <div class="grid-main">
            <plugin-index :plugins="plugins" columns="3"></plugin-index>
        </div>
    </div>
</template>

<script>
    import {mapState} from 'vuex'

    export default {

        data() {
            return {
                plugins: [],
                loading: false,
            }
        },

        components: {
            PluginIndex: require('../components/PluginIndex'),
        },

        computed: {

            ...mapState({
                developer: state => state.pluginStore.developer,
            }),

        },

        mounted() {
            let developerId = this.$route.params.id

            this.loading = true

            this.plugins = this.$store.getters.getPluginsByDeveloperId(developerId)

            this.$store.dispatch('getDeveloper', developerId)
                .then(developer => {
                    this.$root.pageTitle = this.$options.filters.escapeHtml(developer.developerName)
                    this.$root.loading = false
                    this.loading = false
                })
                .catch(response => {
                    this.$root.loading = false
                    this.loading = false
                })

//            this.$root.crumbs = [
//                {
//                    label: this.$options.filters.t("Plugin Store", 'app'),
//                    path: '/',
//                }
//            ]
        },

    }
</script>
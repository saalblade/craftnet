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
    import PluginIndex from '../../../components/PluginIndex'

    export default {

        fetch({ params, store }) {
            let developerId = params.id

            return store.dispatch('pluginStore/getDeveloper', developerId)
                .then(developer => {
                    console.log('success')
                })
                .catch(response => {
                    console.log('error')
                })
        },

        layout: 'site',

        data() {
            return {
                loading: false,
            }
        },

        head () {
            return {
                title: this.developer.developerName,
                meta: [
                    { hid: 'description', name: 'description', content: 'My plugin description' }
                ]
            }
        },

        components: {
            PluginIndex,
        },

        computed: {

            ...mapState({
                developer: state => state.pluginStore.developer,
            }),

            plugins() {
                let developerId = this.$route.params.id

                return this.$store.getters['pluginStore/getPluginsByDeveloperId'](developerId)
            }

        },

    }
</script>
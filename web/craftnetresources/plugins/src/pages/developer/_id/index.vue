<template>
    <div class="xcontainer">
        <div class="developer-card py-6 border-b">
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

        async fetch({store, params}) {
            let developerId = params.id

            await store.dispatch('pluginStore/getDeveloper', developerId)
                .then(response => {
                    const developer = store.state.pluginStore.developer

                    store.commit('app/updatePageMeta', {
                        title: developer.developerName,
                        description: developer.developerName + ' developer.'
                    })
                })
                .catch(response => {
                    console.log('error')
                })


            return
        },

        head () {
            return {
                title: this.pageMeta.title,
                meta: [
                    { hid: 'description', name: 'description', content: this.pageMeta.description }
                ]
            };
        },

        layout: 'site',

        data() {
            return {
                loading: false,
            }
        },

        components: {
            PluginIndex,
        },

        computed: {

            ...mapState({
                pageMeta: state => state.app.pageMeta,
                developer: state => state.pluginStore.developer,
            }),

            plugins() {
                let developerId = this.$route.params.id

                return this.$store.getters['pluginStore/getPluginsByDeveloperId'](developerId)
            }

        },

    }
</script>

<style lang="scss">
    .developer-card {
        @apply .flex;

        .photo {
            @apply .mr-6 .rounded-full .bg-grey-lighter;

            width: 150px;
            height: 150px;

            img {
                @apply .rounded-full;
            }
        }

        .developer-details {
            @apply .flex .content-center;

            .developer-details-content {
                @apply .self-center;

                h1 {
                    @apply .border-b-0;
                }

                ul {
                    @apply .list-reset;

                    &.links {
                        @apply .mt-2;

                        li {
                            @apply .inline-block .mr-2;

                            a {
                                @apply .inline-block;
                            }
                        }
                    }
                }
            }
        }
    }
</style>
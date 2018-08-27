<template>
    <plugin-layout>
        <changelog-release version="1.0.2"></changelog-release>
        <changelog-release version="1.0.1"></changelog-release>
        <changelog-release version="1.0.0"></changelog-release>
    </plugin-layout>
</template>

<script>
    import {mapState} from 'vuex'
    import PluginLayout from '../../../components/PluginLayout'
    import ChangelogRelease from '../../../components/ChangelogRelease'

    export default {

        async fetch ({ store, params }) {
            const pluginSnippet = store.getters['pluginStore/getPluginByHandle'](params.handle)

            await store.commit('app/updatePageMeta', {
                title: pluginSnippet.name + ' Changelog',
                description: pluginSnippet.name + ' Changelog',
            })
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

        components: {
            PluginLayout,
            ChangelogRelease,
        },

        computed: {

            ...mapState({
                pageMeta: state => state.app.pageMeta,
            }),

            pluginSnippet() {
                return this.$store.getters['pluginStore/getPluginByHandle'](this.$route.params.handle)
            },

        }

    }
</script>
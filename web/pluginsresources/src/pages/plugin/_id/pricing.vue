<template>
    <plugin-layout>
        <plugin-pricing :plugin-snippet="pluginSnippet"></plugin-pricing>
    </plugin-layout>
</template>

<script>
    import {mapState} from 'vuex'
    import PluginLayout from '../../../components/PluginLayout'
    import PluginPricing from '../../../components/PluginPricing'

    export default {

        async fetch ({ store, params }) {
            const pluginSnippet = store.getters['pluginStore/getPluginById'](params.id)

            await store.commit('app/updatePageMeta', {
                title: pluginSnippet.name + ' Pricing',
                description: pluginSnippet.name + ' Pricing',
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
            PluginPricing,
        },

        computed: {

            ...mapState({
                pageMeta: state => state.app.pageMeta,
            }),

            pluginSnippet() {
                return this.$store.getters['pluginStore/getPluginById'](this.$route.params.id)
            },

        },

        mounted() {
            if (!this.pluginSnippet.editions[0].price) {
                // Redirect to the plugin’s features section if plugin is free.
                this.$router.push({path: '/plugin/' + this.pluginSnippet.id});
            }
        },

    }
</script>
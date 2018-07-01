<template>
    <plugin-layout>
        <plugin-pricing :plugin-snippet="pluginSnippet"></plugin-pricing>
    </plugin-layout>
</template>

<script>
    import PluginLayout from '../../../components/PluginLayout'
    import PluginPricing from '../../../components/PluginPricing'

    export default {

        layout: 'site',

        components: {
            PluginLayout,
            PluginPricing,
        },

        computed: {

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
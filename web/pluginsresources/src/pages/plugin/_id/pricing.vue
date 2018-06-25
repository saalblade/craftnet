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

        }

    }
</script>
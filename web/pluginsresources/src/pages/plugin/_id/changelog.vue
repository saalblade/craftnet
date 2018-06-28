<template>
    <plugin-layout>
        <changelog-release version="1.0.2"></changelog-release>
        <changelog-release version="1.0.1"></changelog-release>
        <changelog-release version="1.0.0"></changelog-release>
    </plugin-layout>
</template>

<script>
    import PluginLayout from '../../../components/PluginLayout'
    import ChangelogRelease from '../../../components/ChangelogRelease'

    export default {

        layout: 'site',

        components: {
            PluginLayout,
            ChangelogRelease,
        },

        computed: {
            pluginSnippet() {
                return this.$store.getters['pluginStore/getPluginById'](this.$route.params.id)
            },
        }

    }
</script>
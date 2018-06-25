<template>
    <plugin-layout>
        <h2>Version 1.0.1</h2>
        <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Alias atque doloremque eos eum expedita illum, laboriosam, libero molestiae neque nostrum numquam omnis perspiciatis quibusdam quis ratione rerum vel veniam voluptatem!
        <h2>Version 1.0.0</h2>
        <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Alias atque doloremque eos eum expedita illum, laboriosam, libero molestiae neque nostrum numquam omnis perspiciatis quibusdam quis ratione rerum vel veniam voluptatem!</p>
    </plugin-layout>
</template>

<script>
    import PluginLayout from '../../../components/PluginLayout'

    export default {

        layout: 'site',

        components: {
            PluginLayout,
        },

        computed: {
            pluginSnippet() {
                return this.$store.getters['pluginStore/getPluginById'](this.$route.params.id)
            },
        }

    }
</script>
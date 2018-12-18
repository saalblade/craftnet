<template>
    <div>
        <div class="grid-plugins" v-if="plugins && plugins.length > 0">
            <div class="grid-box" v-for="plugin, key in plugins" :class="{'responsive-limit': (key + 1) > responsiveLimit}">
                <plugin-card :plugin="plugin"></plugin-card>
            </div>
        </div>
    </div>
</template>


<script>
    import PluginCard from './PluginCard'

    export default {

        components: {
            PluginCard,
        },

        props: ['plugins', 'responsiveLimit'],

        methods: {

            showPlugin(plugin) {
                this.$router.push({path: '/' + plugin.id});
            },

        },

    }
</script>

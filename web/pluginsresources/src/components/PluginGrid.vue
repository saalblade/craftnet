<template>
    <div>
        <div :class="cssClass" v-if="plugins && plugins.length > 0">
            <div class="grid-box" v-for="plugin in plugins">
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

        props: ['plugins', 'columns'],

        computed: {

            cssClass() {
                let cssClass = 'grid-plugins'

                if (this.columns) {
                    cssClass += ' grid-plugins-' + this.columns
                }

                return cssClass
            }

        },

        methods: {

            showPlugin(plugin) {
                this.$router.push({path: '/plugin/' + plugin.id});
            },

        },

    }
</script>

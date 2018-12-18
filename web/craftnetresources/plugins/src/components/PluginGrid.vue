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

<style lang="scss">
    .grid-plugins {
        width: 100%;
        display: grid;
        grid-template-columns: 1fr;
        grid-auto-rows: 1fr;
        grid-column-gap: 30px;
        color: #444;
        margin-bottom: 20px;

        .grid-box {
            @apply .border-b .py-6 .overflow-hidden;

            &.responsive-limit {
                @apply hidden;
            }
        }
    }

    @media (min-width: 576px) {
        .grid-plugins {
            grid-template-columns: 1fr 1fr;
        }
    }

    @media (min-width: 1200px) {
        .grid-plugins {
            grid-template-columns: 1fr 1fr 1fr;
        }
    }

    @media (min-width: 1600px) {
        .grid-plugins {
            grid-template-columns: 1fr 1fr 1fr 1fr;

            .grid-box {
                &.responsive-limit {
                    @apply block;
                }
            }
        }
    }
</style>
<template>
    <div class="plugin-editions mb-4 py-8">
        <plugin-edition v-for="edition in plugin.editions" :plugin="plugin" :edition="edition" :key="edition.handle"></plugin-edition>
    </div>
</template>

<script>
    import PluginEdition from './PluginEdition'

    export default {

        props: ['plugin'],

        components: {
            PluginEdition
        }

    }
</script>

<style lang="scss">
    .plugin-editions {
        @apply .flex .-mx-4 .justify-center;

        .plugin-editions-edition {
            @apply .w-1/3 .mx-4;
        }
    }
</style>


<template>
    <div v-if="plugin" class="plugin-actions">
        <a :href="craftIdUrl() + '/buy-plugin/' + plugin.handle + '/' + edition.handle" class="c-btn primary large block">Buy</a>
    </div>
</template>

<script>
    import helpers from '../mixins/helpers'

    export default {

        mixins: [helpers],

        props: ['plugin', 'edition'],

    }
</script>
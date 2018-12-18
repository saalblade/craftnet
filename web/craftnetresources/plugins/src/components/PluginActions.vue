<template>
    <div v-if="plugin" class="plugin-actions">
        <a :href="craftIdUrl + '/buy-plugin/' + plugin.handle + '/' + edition.handle" class="c-btn primary large block">Buy</a>
    </div>
</template>

<script>
    export default {

        props: ['plugin', 'edition'],

        computed: {

            craftIdUrl() {
                return process.env.craftIdUrl
            },

        }

    }
</script>
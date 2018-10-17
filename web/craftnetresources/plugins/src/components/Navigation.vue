<template>
    <nav>
        <ul>
            <li><nuxt-link to="/" exact>Craft Plugins</nuxt-link></li>
            <li><a :href="craftIdUrl" target="_blank">Craft ID</a></li>
        </ul>
    </nav>
</template>

<script>
    export default {

        computed: {
            craftIdUrl() {
                return process.env.VUE_APP_CRAFT_ID_URL
            }
        }

    }
</script>

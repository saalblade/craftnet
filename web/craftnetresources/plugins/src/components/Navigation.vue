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
                return process.env.NODE_ENV === 'development' ? 'https://id.craftcms.test/' : 'https://id.craftcms.com/'
            }
        }

    }
</script>

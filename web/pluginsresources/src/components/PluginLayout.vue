<template>
    <div>
        <div class="plugin-details-header">
            <div class="plugin-icon-large">
                <img v-if="pluginSnippet.iconUrl" :src="pluginSnippet.iconUrl" height="100" />
                <img v-else :src="defaultPluginSvg" height="100" />
            </div>

            <div class="plugin-details-description">
                <div class="details">
                    <h1>{{ pluginSnippet.name }}</h1>
                    <div class="short-description">{{ pluginSnippet.shortDescription }}</div>
                    <div><router-link :to="'/developer/'+pluginSnippet.developerId">{{ pluginSnippet.developerName }}</router-link></div>
                </div>
            </div>
        </div>

        <ul class="tabs">
            <li><nuxt-link :to="'/plugin/'+pluginSnippet.id" exact>Features</nuxt-link></li>

            <template v-if="pluginSnippet.editions[0].price != null">
                <li><nuxt-link :to="'/plugin/'+pluginSnippet.id+'/pricing'">Pricing</nuxt-link></li>
            </template>

            <li><nuxt-link :to="'/plugin/'+pluginSnippet.id+'/changelog'">Changelog</nuxt-link></li>
        </ul>

        <slot></slot>
    </div>
</template>

<script>
    export default {

        computed: {

            pluginSnippet() {
                return this.$store.getters['pluginStore/getPluginById'](this.$route.params.id)
            },

        },

    }
</script>
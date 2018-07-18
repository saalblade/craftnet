<template>
    <div>
        <div class="plugin-details-header">
            <div class="xcontainer">
                <div class="name">
                    <div class="icon">
                        <img v-if="pluginSnippet.iconUrl" :src="pluginSnippet.iconUrl" height="46" width="46" />
                        <img v-else :src="defaultPluginSvg" height="46" width="46" />
                    </div>
                    <h1>
                        {{ pluginSnippet.name }}
                    </h1>

                    <a class="nav-toggle" @click="showNav=!showNav"><font-awesome-icon :icon="navIcon" /></a>
                    <!--<div class="short-description">{{ pluginSnippet.shortDescription }}</div>-->
                    <!--<div><router-link :to="'/developer/'+pluginSnippet.developerId">{{ pluginSnippet.developerName }}</router-link></div>-->
                </div>

                <ul class="nav" :class="{hidden: !showNav}">
                    <li><nuxt-link :to="'/plugin/'+pluginSnippet.handle" exact>Features</nuxt-link></li>

                    <template v-if="pluginSnippet.editions[0].price != null">
                        <li><nuxt-link :to="'/plugin/'+pluginSnippet.handle+'/pricing'">Pricing</nuxt-link></li>
                    </template>

                    <li><nuxt-link :to="'/plugin/'+pluginSnippet.handle+'/changelog'">Changelog</nuxt-link></li>
                </ul>
            </div>
        </div>


        <div class="xcontainer">
            <slot></slot>
        </div>
    </div>
</template>

<script>
    import FontAwesomeIcon from '@fortawesome/vue-fontawesome'
    import faChevronDown from '@fortawesome/fontawesome-free-solid/faChevronDown'
    import faChevronUp from '@fortawesome/fontawesome-free-solid/faChevronUp'

    export default {

        data() {
            return {
                showNav: false,
            }
        },

        components: {
            FontAwesomeIcon,
        },

        computed: {

            pluginSnippet() {
                return this.$store.getters['pluginStore/getPluginByHandle'](this.$route.params.handle)
            },

            navIcon() {
                if(this.showNav) {
                    return faChevronUp
                }

                return faChevronDown
            },
        },

        mounted() {
            this.$nextTick(() => {
                this.$store.commit('app/updateStickyHeader', false)
            })
        },

    }
</script>
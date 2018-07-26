<template>
    <div class="plugin-layout">
        <div ref="pluginDetailsHeader" class="plugin-details-header" :class="{scrolled: scrolled}">
            <div class="xcontainer">
                <div class="description">
                    <div class="icon">
                        <img v-if="pluginSnippet.iconUrl" :src="pluginSnippet.iconUrl" />
                        <img v-else :src="defaultPluginSvg" />
                    </div>

                    <div class="name">
                        <h1>
                            {{ pluginSnippet.name }}
                        </h1>

                        <div class="developer">
                            <router-link :to="'/developer/'+pluginSnippet.developerId">{{ pluginSnippet.developerName }}</router-link>
                        </div>
                    </div>

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
                    <li><nuxt-link :to="'/plugin/'+pluginSnippet.handle+'/changelog'" class="btn btn-primary">Buy</nuxt-link></li>
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
                scrolled: false,
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

        methods: {

            onScroll(scrollY) {
                if (this.$refs.pluginDetailsHeader) {
                    let headerHeight = this.$refs.pluginDetailsHeader.clientHeight

                    if (this.scrolled) {
                        headerHeight += 30
                    }

                    if (!this.scrolled && window.innerHeight < 992) {
                        headerHeight += this.$refs.pluginDetailsHeader.offsetTop
                    }

                    if (scrollY > headerHeight) {
                        this.scrolled = true
                    } else {
                        this.scrolled = false
                    }
                }
            },

            onViewScroll(e) {
                this.onScroll(e.target.scrollTop);
            },

            onWindowResize() {
                if(window.innerHeight > 991) {
                    this.onViewScroll()
                } else {
                    this.onWindowScroll()
                }
            },

            onWindowScroll() {
                this.onScroll(window.scrollY);
            },
        },

        mounted() {
            this.$nextTick(() => {
                this.$store.commit('app/updateStickyHeader', false)
            })

            window.addEventListener('scroll', this.onWindowScroll)
            this.$bus.$on('viewScroll', this.onViewScroll)
            window.addEventListener('resize', this.onWindowResize)
            this.onWindowResize()
        },

        destroyed() {
            window.removeEventListener('scroll', this.onWindowScroll, true)
            this.$bus.$off('viewScroll')
            window.removeEventListener('resize', this.onWindowResize, true)
        }
    }

</script>
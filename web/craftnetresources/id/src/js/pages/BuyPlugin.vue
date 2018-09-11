<template>
    <div>
        <h1>Buy Plugin</h1>

        <div class="card mb-4">
            <div class="card-body">
                Adding
                <template v-if="plugin">{{plugin.name}}</template>
                <code v-else>{{handle}}</code>
                to your cart…

                <div class="spinner" v-if="loading"></div>
            </div>
        </div>
    </div>
</template>

<script>
    import {mapState, mapGetters, mapActions} from 'vuex'

    export default {

        data() {
            return {
                loading: false,
                plugin: null,
            }
        },

        computed: {

            ...mapState({
                plugins: state => state.pluginStore.plugins,
            }),

            ...mapGetters({
                getPluginByHandle: 'pluginStore/getPluginByHandle',
            }),

            handle() {
                return this.$route.params.handle
            },

            edition() {
                return this.$route.params.edition
            }
        },

        methods: {

            ...mapActions({
                getPluginStoreData: 'pluginStore/getPluginStoreData',
            }),

            addToCart() {
                this.plugin = this.getPluginByHandle(this.handle)

                const pluginEdition = this.plugin.editions.find(edition => edition.handle === this.edition)

                const item = {
                    type: 'plugin-edition',
                    plugin: this.plugin,
                    pluginEditionHandle: this.edition,
                    lineItem: {
                        total: pluginEdition.price
                    }
                }

                this.$store.dispatch('cart/addToCart', {item})
                    .then(response => {
                        this.$router.push({path: '/cart'})
                    })
            }
        },

        mounted() {
            if(this.plugins.length === 0) {
                this.loading = true
                this.getPluginStoreData()
                    .then(response => {
                        this.loading = false
                        this.addToCart();
                    })
                    .catch(response => {
                        this.loading = false
                    })
            } else {
                this.addToCart();
            }
        }

    }
</script>

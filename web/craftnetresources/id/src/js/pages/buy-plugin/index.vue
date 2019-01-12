<template>
    <div>
        <h1>Buy Plugin</h1>

        <div class="card mb-4">
            <div class="card-body">
                Adding
                <template v-if="plugin">{{plugin.name}}</template>
                <code v-else>{{handle}}</code>
                to your cart…

                <spinner v-if="loading"></spinner>
            </div>
        </div>
    </div>
</template>

<script>
    import {mapState, mapGetters, mapActions} from 'vuex'
    import Spinner from '../../components/Spinner'

    export default {

        components: {
            Spinner,
        },

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

                const item = {
                    type: 'plugin-edition',
                    plugin: this.handle,
                    edition: this.edition,
                    autoRenew: false,
                }

                this.$store.dispatch('cart/addToCart', [item])
                    .then(() => {
                        this.$router.push({path: '/cart'})
                    })
            }
        },

        mounted() {
            if(this.plugins.length === 0) {
                this.loading = true
                this.getPluginStoreData()
                    .then(() => {
                        this.loading = false
                        this.addToCart();
                    })
                    .catch(() => {
                        this.loading = false
                    })
            } else {
                this.addToCart();
            }
        }

    }
</script>

<template>
    <div>
        <h1>Buy License</h1>

        <div class="card mb-4">
            <div class="card-body">
                <h2>Select a plugin edition</h2>

                <div class="flex items-center mb-4">
                    <div>
                        <select v-model="selectedPlugin">
                            <option value="">Select a plugin</option>
                            <option v-for="plugin in plugins"
                                    :value="plugin.handle">{{ plugin.name }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <div class="spinner" v-if="loading"></div>
                    </div>
                </div>

                <div class="buttons">
                    <input type="button" class="btn btn-primary"
                           :class="{disabled: !selectedPlugin}"
                           @click="addToCart(selectedPlugin)"
                           :disabled="!selectedPlugin" value="Add to cart"/>
                </div>
            </div>
        </div>

        <cart></cart>
        <payment></payment>
    </div>
</template>

<script>
    import {mapState, mapActions} from 'vuex'
    import Cart from '../components/buy-license/Cart'
    import Payment from '../components/buy-license/Payment'

    export default {

        data() {
            return {
                loading: false,
                selectedPlugin: '',
            }
        },

        components: {
            Cart,
            Payment,
        },

        computed: {

            ...mapState({
                plugins: state => state.pluginstore.plugins,
            }),

            handle() {
                return this.$route.params.handle
            }
        },

        methods: {
            ...mapActions({
                getPluginStoreData: 'getPluginStoreData',
                addToCart: 'addToCart',
            })
        },

        mounted() {
            if (this.plugins.length === 0) {
                this.loading = true

                this.getPluginStoreData()
                    .then(response => {
                        this.loading = false
                    })
                    .catch(response => {
                        this.loading = false
                    })
            }
        }

    }
</script>

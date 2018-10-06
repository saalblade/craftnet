<template>
    <div>
        <h1>Cart</h1>

        <div v-if="loading" class="spinner"></div>

        <template v-else>
            <template v-if="cart">
                <template v-if="cartItems.length">
                    <table class="table">
                        <thead>
                        <tr>
                            <th colspan="2">Item</th>
                            <th>Updates</th>
                            <th>Quantity</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="(item, itemKey) in cartItems">
                            <template v-if="item.lineItem.purchasable.type === 'cms-edition'">
                                <td class="thin">
                                    <div class="plugin-icon">
                                        <img :src="craftLogo" width="42" height="42" />
                                    </div>
                                </td>
                                <td>Craft {{ item.lineItem.purchasable.name }}</td>
                            </template>

                            <template v-else="item.lineItem.purchasable.type === 'plugin-edition'">
                                <td class="thin">
                                    <div v-if="item.plugin" class="plugin-icon">
                                        <img v-if="item.plugin.iconUrl" :src="item.plugin.iconUrl" width="42" height="42" />
                                    </div>
                                </td>
                                <td>
                                    <strong class="text-xl">{{item.lineItem.purchasable.plugin.name}}</strong>

                                    <div class="text-secondary">
                                        {{item.lineItem.purchasable.name}}
                                    </div>
                                </td>
                            </template>

                            <td>
                                <select-field v-model="itemUpdates[itemKey]" :options="itemUpdateOptions(itemKey)" />
                            </td>
                            <td>
                                <select-field value="1" :options="quantityOptions"></select-field>
                            </td>
                            <td class="text-right">
                                <strong class="block text-xl">{{ item.lineItem.total|currency }}</strong>
                                <a @click="removeFromCart(itemKey)">Remove</a>
                            </td>
                        </tr>

                        <tr>
                            <th class="text-right text-xl" colspan="4">Total</th>
                            <td class="text-right text-xl"><strong>{{ cart.totalPrice|currency }}</strong></td>
                        </tr>
                        </tbody>

                        <cart-mock />
                    </table>

                    <div class="mt-4 text-right"><input type="button" class="btn btn-lg btn-primary" @click="checkout()" value="Check Out" /></div>
                </template>

                <div v-else>
                    <div class="empty">
                        <div class="empty-body">
                            <font-awesome-icon icon="shopping-cart" class="text-5xl mb-4 text-grey" />
                            <div class="font-bold">Your cart is empty</div>
                            <div class="mt-4">
                                <router-link class="btn btn-primary" to="/buy">Buy a license</router-link>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </template>
    </div>
</template>

<script>
    import CraftComponents from "@benjamindavid/craftcomponents";
    import {mapState, mapGetters, mapActions} from 'vuex'
    import CartMock from '../components/CartMock'

    export default {

        components: {
            ...CraftComponents,
            CartMock,
        },

        data() {
            return {
                loading: false,
                itemUpdates: {},
            }
        },

        computed: {

            ...mapState({
                cart: state => state.cart.cart,
            }),

            ...mapGetters({
                cartItems: 'cart/cartItems',
            }),

            quantityOptions()
            {
                return [
                    {label: 1, value: 1},
                    {label: 2, value: 2},
                    {label: 3, value: 3},
                    {label: 4, value: 4},
                    {label: 5, value: 5},
                ]
            }
        },

        methods: {

            ...mapActions({
                getCart: 'cart/getCart',
                removeFromCart: 'cart/removeFromCart',
                createCart: 'cart/createCart',
                removeFromCartMock: 'cart/removeFromCartMock',
                getPluginStoreData: 'pluginStore/getPluginStoreData',
            }),

            checkout() {
                this.$router.push({path: '/payment'})
            },

            itemUpdateOptions(itemKey) {
                return [
                    {
                        label: 'Updates Until 9/28/2019 (Free)',
                        value: '',
                    }
                ]
            },

        },

        mounted() {
            this.loading = true

            this.getPluginStoreData()
                .then(() => {
                    this.getCart()
                        .then(() => {
                            this.loading = false
                        })
                        .catch(() => {
                            this.loading = false
                        })
                })
                .catch(() => {
                    this.loading = false
                })
        },
    }
</script>

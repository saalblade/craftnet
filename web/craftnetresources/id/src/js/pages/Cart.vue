<template>
    <div>
        <h1>Cart</h1>

        <div class="card mb-4">
            <div class="card-body">
                <div v-if="loading" class="spinner"></div>

                <template v-if="cart">
                    <template v-if="cartItems.length">
                        <table class="table">
                            <thead>
                            <tr>
                                <th></th>
                                <th>Item</th>
                                <th>Updates</th>
                                <th></th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="(item, itemKey) in cartItems">
                                <template v-if="item.lineItem.purchasable.type === 'cms-edition'">
                                    <td class="thin">
                                        <div class="plugin-icon">
                                            <img :src="craftLogo" width="32" height="32" />
                                        </div>
                                    </td>
                                    <td>Craft {{ item.lineItem.purchasable.name }}</td>
                                </template>

                                <template v-else="item.lineItem.purchasable.type === 'plugin-edition'">
                                    <td class="thin">
                                        <div class="plugin-icon">
                                            <!--<img v-if="item.plugin.iconUrl" :src="item.plugin.iconUrl" height="32" />-->
                                        </div>
                                    </td>
                                    <td>
                                        {{item.lineItem.purchasable.plugin.name}}
                                        <!--{{ item.plugin.name}}-->
                                    </td>
                                </template>

                                <td>
                                    <!--<select-input v-model="itemUpdates[itemKey]" :options="itemUpdateOptions[itemKey]" />-->
                                </td>
                                <td class="rightalign"><strong>{{ item.lineItem.total|currency }}</strong></td>
                                <td class="thin">
                                    <a @click="removeFromCart(itemKey)"><font-awesome-icon icon="times" /></a>
                                </td>
                            </tr>
                            <tr>
                                <th class="rightalign" colspan="3">Total Price</th>
                                <td class="rightalign"><strong>{{ cart.totalPrice|currency }}</strong></td>
                                <td class="thin"></td>
                            </tr>
                            </tbody>
                        </table>

                        <p><input type="button" class="btn btn-primary" @click="payment()" value="Checkout" /></p>
                    </template>

                    <div v-else>
                        <p>{{ "Your cart is empty." }}</p>
                    </div>
                </template>
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
            }
        },

        computed: {

            ...mapState({
                cart: state => state.cart.cart,
            }),

            ...mapGetters({
                cartItems: 'cart/cartItems',
            }),
        },

        methods: {
            ...mapActions({
                getCart: 'cart/getCart',
                removeFromCart: 'cart/removeFromCart',
            }),
        },

        mounted() {
            this.loading = true

            this.getCart()
                .then(() => {
                    this.loading = false
                })
                .catch(() => {
                    this.loading = false
                })
        },
    }
</script>
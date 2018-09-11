<template>
    <div class="card mb-4">
        <div class="card-body">
            <h2>{{cart.items.length}} item(s) in your cart</h2>
            <table v-if="cart.items.length > 0" class="table">
                <thead>
                <tr>
                    <th></th>
                    <th>Item</th>
                    <th>Edition</th>
                    <th>Updates</th>
                    <th></th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="(item, itemKey) in cart.items">
                    <template v-if="item.type === 'renewal'">
                        <td></td>
                        <td colspan="3">Renewal</td>
                    </template>

                    <template v-else>
                        <td>
                            <img :src="item.plugin.iconUrl" width="32" height="32" alt="">
                        </td>
                        <td>
                            {{item.plugin.name}}<br />
                            <small class="text-grey">{{item.type}}</small>
                        </td>
                        <td>{{item.pluginEditionHandle}}</td>
                        <td>
                            <select>
                                <option>Updates Until x/x/xxxx (+$00.00)</option>
                            </select>
                        </td>
                    </template>

                    <td>{{item.lineItem.total|currency}}</td>
                    <td><input type="button" class="btn btn-secondary" @click="removeFromCart(itemKey)" value="Remove" /></td>
                </tr>
                <tr>
                    <th colspan="4" class="text-right">Total</th>
                    <th>{{cartTotal|currency}}</th>
                    <th></th>
                </tr>
                </tbody>
            </table>

            <p v-else>Your cart is empty.</p>

            <div class="flex justify-between">
                <input type="button" class="btn btn-primary" :class="{disabled: cart.items.length === 0}" value="Checkout" @click="checkout()" />
                <input type="button" class="btn btn-secondary" value="Create Cart" @click="createCart" />
            </div>
        </div>
    </div>
</template>

<script>
    import {mapState, mapGetters, mapActions} from 'vuex'

    export default {

        computed: {

            ...mapState({
                cart: state => state.cart.cart,
            }),

            ...mapGetters({
                cartTotal: 'cart/cartTotal',
            }),

        },

        methods: {
            ...mapActions({
                createCart: 'cart/createCart',
                removeFromCart: 'cart/removeFromCart',
            }),

            checkout() {
                this.$router.push({path: '/payment'});
            }
        }
    }
</script>
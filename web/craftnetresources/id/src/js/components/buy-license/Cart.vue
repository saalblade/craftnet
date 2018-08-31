<template>
    <div class="card mb-4">
        <div class="card-body">
            <h2>Cart</h2>

            <table v-if="cart.items.length > 0" class="table">
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
                <tr v-for="item in cart.items">
                    <td>Icon</td>
                    <td>{{item.plugin.name}}</td>
                    <td>Select input with renewal options</td>
                    <td>${{item.lineItem.total}}</td>
                    <td><a href="#" class="btn btn-secondary">Remove</a></td>
                </tr>
                <tr>
                    <th colspan="3" class="text-right">Total</th>
                    <th>${{cartTotal}}</th>
                    <th></th>
                </tr>
                </tbody>
            </table>

            <p v-else>Cart is empty.</p>

            <div class="flex justify-between">
                <input type="button" class="btn btn-primary" :class="{disabled: cart.items.length === 0}" value="Checkout" />
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
            })
        }
    }
</script>
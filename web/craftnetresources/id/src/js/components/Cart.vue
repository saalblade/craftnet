<template>
    <div>
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
            <tr v-for="item in cart.items">
                <td>Icon</td>
                <td>{{item.plugin.name}}</td>
                <td>Select input with renewal options</td>
                <td>{{item.lineItem.total}}</td>
                <td><a href="#" class="btn btn-secondary">Remove</a></td>
            </tr>
            <tr>
                <th colspan="3" class="text-right">Total</th>
                <th>$0.00</th>
                <th></th>
            </tr>
            </tbody>
        </table>

        <div class="buttons">
            <input type="button" class="btn btn-primary" value="Checkout" />
            <input type="button" class="btn btn-secondary" value="Create Cart" @click="createCart" />
        </div>
    </div>
</template>

<script>
    import {mapState, mapActions} from 'vuex'

    export default {

        computed: {

            ...mapState({
                cart: state => state.cart.cart,
            }),

        },

        methods: {
            ...mapActions({
                createCart: 'createCart',
            })
        }
    }
</script>
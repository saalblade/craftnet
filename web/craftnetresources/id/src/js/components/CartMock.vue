<template>
    <tbody v-if="mockCart.items.length > 0">
        <tr>
            <th colspan="5">Renewals</th>
        </tr>

        <tr v-for="(item, itemKey) in mockCart.items">
            <td></td>
            <td colspan="2">Renewal</td>
            <td>{{item.lineItem.total|currency}}</td>
            <td class="text-right"><a @click="removeFromCartMock(itemKey)"><font-awesome-icon icon="times" /></a></td>
        </tr>

        <tr>
            <th colspan="3" class="text-right">Renewal Total</th>
            <th>{{cartTotal|currency}}</th>
            <td class="thin"></td>
        </tr>
    </tbody>
</template>

<script>
    import {mapState, mapGetters} from 'vuex'

    export default {

        data() {
            return {
                loading: false,
            }
        },

        computed: {

            ...mapState({
                mockCart: state => state.cart.mockCart,
            }),

            ...mapGetters({
                cartTotal: 'cart/cartTotal',
            }),
        },

    }
</script>
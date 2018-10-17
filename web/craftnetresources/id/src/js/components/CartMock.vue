<template>
    <tbody v-if="mockCart.items.length > 0">
        <tr>
            <th colspan="5"><div class="text-xl">Renewals</div></th>
        </tr>

        <tr v-for="(item, itemKey) in mockCart.items">
            <td></td>
            <td colspan="3">Renewal</td>
            <td class="text-right">
                <strong class="block text-xl">{{ item.lineItem.total|currency }}</strong>
                <a @click="removeFromCartMock(itemKey)">Remove</a>
            </td>
        </tr>

        <tr>
            <th colspan="4" class="text-right text-xl">Renewal Total</th>
            <th class="text-right text-xl">{{cartTotal|currency}}</th>
        </tr>
    </tbody>
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
                mockCart: state => state.cart.mockCart,
            }),

            ...mapGetters({
                cartTotal: 'cart/cartTotal',
            }),
        },

        methods: {

            ...mapActions({
                removeFromCartMock: 'cart/removeFromCartMock',
            })

        }

    }
</script>
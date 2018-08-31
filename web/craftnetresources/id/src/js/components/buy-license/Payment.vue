<template>
    <div class="card">
        <div class="card-body">
            <div class="md:flex -mx-4">
                <div class="md:w-1/2 px-4">
                    <h2>Payment</h2>

                    <h3>Payment Method</h3>
                    <payment-method></payment-method>

                    <h3 class="mt-4">Coupon Code</h3>
                    <coupon-code></coupon-code>
                </div>
                <div class="md:w-1/2 px-4">
                    <h3>Billing Informations</h3>
                    <billing-infos></billing-infos>
                </div>
            </div>

            <input type="button" class="btn btn-primary" :value="'Pay $'+cartTotal"/>
        </div>
    </div>
</template>

<script>
    import {mapGetters} from 'vuex'
    import CraftComponents from "@benjamindavid/craftcomponents";
    import PaymentMethod from '../payment/PaymentMethod'
    import CouponCode from '../payment/CouponCode'
    import BillingInfos from '../payment/BillingInfos'

    export default {

        components: {
            ...CraftComponents,
            PaymentMethod,
            CouponCode,
            BillingInfos,
        },

        computed: {

            ...mapGetters({
                cartTotal: 'cart/cartTotal',
            }),

        },

    }
</script>

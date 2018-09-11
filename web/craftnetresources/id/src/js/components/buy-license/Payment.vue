<template>
    <div class="card">
        <div class="card-body">
            <div class="md:flex -mx-4">
                <div class="md:w-1/2 px-4">
                    <h2>Payment</h2>

                    <h3>Payment Method</h3>
                    <payment-method
                            :card="card"
                            :cardToken="cardToken"
                            :paymentMode="paymentMode"
                            :replaceCard.sync="replaceCard"></payment-method>

                    <h3 class="mt-4">Coupon Code</h3>
                    <coupon-code></coupon-code>
                </div>
                <div class="md:w-1/2 px-4">
                    <h3>Billing Informations</h3>
                    <billing-infos
                            :billingInfo="billingInfo"
                            :errors="billingInfoErrors"></billing-infos>
                </div>
            </div>

            <input type="button" class="btn btn-primary" :value="'Pay $'+cartTotal" @click="pay"/>
        </div>
    </div>
</template>

<script>
    import {mapState, mapGetters} from 'vuex'
    import CraftComponents from "@benjamindavid/craftcomponents";
    import PaymentMethod from '../payment/PaymentMethod'
    import CouponCode from '../payment/CouponCode'
    import BillingInfos from '../payment/BillingInfos'

    export default {

        data() {
            return {
                billingInfo: {
                    firstName: '',
                    lastName: '',
                    businessName: '',
                    businessTaxId: '',
                    address1: '',
                    address2: '',
                    country: '',
                    state: '',
                    city: '',
                    zipCode: '',
                },
                billingInfoErrors: {},
                cardToken: null,
                paymentMode: 'newCard',
                replaceCard: false,
            }
        },

        components: {
            ...CraftComponents,
            PaymentMethod,
            CouponCode,
            BillingInfos,
        },

        computed: {

            ...mapState({
                cart: state => state.cart.cart,
                card: state => state.account.card,
            }),

            ...mapGetters({
                cartTotal: 'cart/cartTotal',
            }),

        },

        methods: {

            pay() {
                this.savePaymentMethod()
                    .then(() => {
                        this.saveBillingInfos()
                            .then(() => {
                                this.processPayment();
                            })
                    })
            },

            savePaymentMethod() {
                return new Promise((resolve, reject) => {
                    resolve()
                    // reject()
                })
            },

            saveBillingInfos() {
                return new Promise((resolve, reject) => {
                    resolve()
                    // reject()
                })
            },

            processPayment() {
                console.log('process payment');
            }

        },

        mounted() {
            if (this.card) {
                this.paymentMode = 'existingCard'
            }
        }

    }
</script>

<template>
    <div>
        <h1>Payment</h1>

        <div v-if="error">{{error}}</div>
        <spinner v-if="!cart"></spinner>

        <template v-else>
            <form @submit.prevent="pay()">
                <div class="md:flex -mx-8">
                    <div class="md:w-1/2 px-8">
                        <h2>Payment Method</h2>

                        <payment-method
                                ref="paymentMethod"
                                :card="card"
                                :cardToken="cardToken"
                                :paymentMode.sync="paymentMode"
                                :replaceCard.sync="replaceCard"></payment-method>

                        <h2 class="mt-4">Coupon Code</h2>
                        <coupon-code></coupon-code>
                    </div>
                    <div class="md:w-1/2 px-8 border-l">
                        <h2>Billing Informations</h2>
                        <billing-infos
                                :billingInfo.sync="billingInfo"
                                :errors="errors"></billing-infos>
                    </div>
                </div>

                <div class="text-center mt-8">
                    <btn class="primary" type="submit" large>Pay {{cart.totalPrice|currency}}</btn>

                    <spinner v-if="payLoading"></spinner>

                    <div class="mt-4">
                        <img src="~@/images/powered_by_stripe.svg" height="18" />
                    </div>
                </div>
            </form>
        </template>
    </div>
</template>

<script>
    import {mapState, mapGetters, mapActions} from 'vuex'
    import PaymentMethod from '../components/payment/PaymentMethod'
    import CouponCode from '../components/payment/CouponCode'
    import BillingInfos from '../components/payment/BillingInfos'

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
                paymentMode: 'newCard',
                replaceCard: false,
                loading: false,
                payLoading: false,
                error: null,
                errors: {},
                cardToken: null,
            }
        },

        components: {
            PaymentMethod,
            CouponCode,
            BillingInfos,
        },

        computed: {
            ...mapState({
                cart: state => state.cart.cart,
                card: state => state.stripe.card,
                existingCardToken: state => state.stripe.cardToken,
                accountBillingAddress: state => state.account.billingAddress,
            }),

            ...mapGetters({
                cartTotal: 'cart/cartTotal',
            }),
        },

        methods: {
            ...mapActions({
                getCart: 'cart/getCart',
            }),

            pay() {
                this.payLoading = true

                this.savePaymentMethod()
                    .then(() => {
                        this.errors = {}
                        this.saveBillingInfos()
                            .then(() => {
                                this.processPayment()
                                    .then(() => {
                                        this.$store.dispatch('app/displayNotice', 'Payment processed.')
                                        this.payLoading = false
                                        this.$router.push({path: '/thank-you'})
                                    })
                                    .catch(() => {
                                        this.$store.dispatch('app/displayError', 'Couldn’t process payment.')
                                        this.payLoading = false
                                    })
                            })
                            .catch((response) => {
                                let errors = {}

                                if (response.response.data.errors) {
                                    response.response.data.errors.forEach(error => {
                                        errors[error.param] = [error.message]
                                    })
                                }

                                this.errors = errors

                                this.$store.dispatch('app/displayError', 'Couldn’t save billing infos.')
                                this.payLoading = false
                            })
                    })
                    .catch((error) => {
                        this.$store.dispatch('app/displayError', 'Couldn’t save payment method.')
                        this.payLoading = false
                        throw error
                    })
            },

            savePaymentMethod() {
                return new Promise((resolve, reject) => {
                    if (this.cart && this.cart.totalPrice > 0) {
                        if (this.paymentMode === 'newCard') {
                            // Save new card
                            if (!this.cardToken) {
                                this.$refs.paymentMethod.$refs.newCard.save(source => {
                                    this.cardToken = source
                                    resolve()
                                }, () => {
                                    reject()
                                })
                            } else {
                                resolve()
                            }
                        } else {
                            resolve()
                        }
                    } else {
                        resolve()
                    }
                })
            },

            saveBillingInfos() {
                let cartData = {
                    billingAddress: {
                        firstName: this.billingInfo.firstName,
                        lastName: this.billingInfo.lastName,
                        businessName: this.billingInfo.businessName,
                        businessTaxId: this.billingInfo.businessTaxId,
                        address1: this.billingInfo.address1,
                        address2: this.billingInfo.address2,
                        country: this.billingInfo.country,
                        state: this.billingInfo.state,
                        city: this.billingInfo.city,
                        zipCode: this.billingInfo.zipCode,
                    },
                }

                return this.$store.dispatch('cart/saveCart', cartData)
            },

            processPayment() {
                let cardToken = null

                if (this.cart.totalPrice > 0) {
                    switch (this.paymentMode) {
                        case 'newCard':
                            cardToken = this.cardToken.id
                            break
                        default:
                            cardToken = this.existingCardToken
                    }
                }

                let checkoutData = {
                    orderNumber: this.cart.number,
                    token: cardToken,
                    expectedPrice: this.cart.totalPrice,
                    makePrimary: this.replaceCard,
                }

                return this.$store.dispatch('cart/checkout', checkoutData)
                    .then(() => {
                        this.$store.dispatch('craftId/getCraftIdData')
                            .then(() => {
                                this.$store.dispatch('cart/resetCart')
                                    .then(() => {
                                        this.loading = false
                                    })
                            })
                    })
            },
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

            if (this.card) {
                this.paymentMode = 'existingCard'
            }

            this.$nextTick(() => {
                if(this.accountBillingAddress) {
                    this.billingInfo = JSON.parse(JSON.stringify(this.accountBillingAddress))
                }
            })
        }
    }
</script>

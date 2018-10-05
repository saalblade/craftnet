<template>
    <div>
        <h1>Payment</h1>

        <div v-if="error">{{error}}</div>
        <div v-if="!cart" class="spinner"></div>

        <template v-else>
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
                            :billingInfo="billingInfo"
                            :errors="errors"></billing-infos>
                </div>
            </div>

            <div class="text-center mt-8">
                <input type="button" class="btn btn-lg btn-primary" :value="'Pay '+$options.filters.currency(cart.totalPrice)" @click="pay"/>

                <div v-if="payLoading" class="spinner"></div>

                <div class="mt-4">
                    <img src="/craftnetresources/id/dist/images/powered_by_stripe.svg" height="18" />
                </div>
            </div>
        </template>
    </div>
</template>

<script>
    import {mapState, mapGetters, mapActions} from 'vuex'
    import CraftComponents from "@benjamindavid/craftcomponents";
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
            ...CraftComponents,
            PaymentMethod,
            CouponCode,
            BillingInfos,
        },

        computed: {

            ...mapState({
                cart: state => state.cart.cart,
                card: state => state.account.card,
                existingCardToken: state => state.account.cardToken,
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
                        this.errors = {};
                        this.saveBillingInfos()
                            .then(() => {
                                this.processPayment()
                                    .then(() => {
                                        this.$root.displayNotice('Payment processed.')
                                        this.payLoading = false
                                    })
                                    .catch(() => {
                                        this.$root.displayError('Couldn’t process payment.')
                                        this.payLoading = false
                                    })
                            })
                            .catch((response) => {
                                let errors = {};

                                if (response.response.data.errors) {
                                    response.response.data.errors.forEach(error => {
                                        errors[error.param] = [error.message]
                                    })
                                }

                                this.errors = errors;

                                this.$root.displayError('Couldn’t save billing infos.')
                                this.payLoading = false
                            })
                    })
                    .catch((error) => {
                        this.$root.displayError('Couldn’t save payment method.')
                        this.payLoading = false
                        throw error;
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
                                    console.log('failure')
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
                console.log('processPayment');
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
            this.loading = true;

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
                this.billingInfo = JSON.parse(JSON.stringify(this.accountBillingAddress))
            })
        }

    }
</script>

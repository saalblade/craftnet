<template>
    <div>
        <div class="md:flex -mx-4">
            <div class="md:w-1/2 px-4">
                <h3>Payment Method</h3>
                <template v-if="card">
                    <p><label><input type="radio" value="existingCard" v-model="paymentMode" /> Use card <span>{{ card.brand }} •••• •••• •••• {{ card.last4 }} — {{ card.exp_month }}/{{ card.exp_year }}</span></label></p>
                </template>

                <p><label><input type="radio" value="newCard" v-model="paymentMode" /> Use a new credit card</label></p>

                <template v-if="paymentMode === 'newCard'">
                    <card-element v-if="!cardToken" ref="newCard" />
                    <p v-else>{{ cardToken.card.brand }} •••• •••• •••• {{ cardToken.card.last4 }} ({{ cardToken.card.exp_month }}/{{ cardToken.card.exp_year }}) <a class="delete icon" @click="cardToken = null">Delete</a></p>

                    <checkbox-field id="replaceCard" v-model="replaceCard" label="Save as my new credit card" />
                </template>

                <h3 class="mt-4">Coupon Code</h3>
                <text-field placeholder="XXXXXXX" id="coupon-code" size="12" />
            </div>
            <div class="md:w-1/2 px-4">
                <h3>Billing Informations</h3>
                <text-field placeholder="First Name" id="first-name" v-model="billingInfo.firstName" :errors="errors['billingAddress.firstName']" />
                <text-field placeholder="Last Name" id="last-name" v-model="billingInfo.lastName" :errors="errors['billingAddress.lastName']" />
                <text-field placeholder="Business Name" id="business-name" v-model="billingInfo.businessName" :errors="errors['billingAddress.businessName']" />
                <text-field placeholder="Business Tax ID" id="business-tax-id" v-model="billingInfo.businessTaxId" :errors="errors['billingAddress.businessTaxId']" />
                <text-field placeholder="Address 1" id="address-1" v-model="billingInfo.address1" :errors="errors['billingAddress.address1']" />
                <text-field placeholder="Address 2" id="address-2" v-model="billingInfo.address2" :errors="errors['billingAddress.address2']" />

                <div class="md:flex -mx-2">
                    <div class="md:w-1/2 px-2">
                        <text-field placeholder="City" id="city" v-model="billingInfo.city" :errors="errors['billingAddress.city']" />
                    </div>
                    <div class="md:w-1/2 px-2">
                        <text-field placeholder="Zip Code" id="zip-code" v-model="billingInfo.zipCode" :errors="errors['billingAddress.zipCode']" />
                    </div>
                </div>
                <div class="md:flex -mx-2">
                    <div class="md:w-1/2 px-2">
                        <select-field :fullwidth="true" :options="[{label: 'State', value: 0}]" :value="0" id="state" />
                    </div>
                    <div class="md:w-1/2 px-2">
                        <select-field :fullwidth="true" :options="[{label: 'Country', value: 0}]" :value="0" id="country" />
                    </div>
                </div>
            </div>
        </div>

        <button @click="$emit('back')" class="btn btn-secondary">Back</button>
        <button @click="pay" class="btn btn-primary">Pay {{ totalPrice|currency }}</button>
        <div v-if="loading" class="spinner"></div>

        <div class="mt-4">
            <img src="/craftnetresources/id/dist/images/powered_by_stripe.svg" height="18" />
        </div>
    </div>
</template>

<script>
    import {mapState, mapGetters} from 'vuex'
    import CraftComponents from "@benjamindavid/craftcomponents";
    import CardElement from '../../CardElement'

    export default {

        props: ['license', 'renew', 'checkedLicenses'],

        components: {
            ...CraftComponents,
            CardElement,
        },

        data() {
            return {
                loading: false,
                paymentMode: 'newCard',
                replaceCard: false,
                cardToken: null,
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
                errors: {},
            }
        },

        computed: {

            ...mapState({
                card: state => state.account.card,
            }),

            ...mapGetters({
                renewableLicensesTotal: 'renewableLicensesTotal',
            }),

            totalPrice() {
                return this.renewableLicensesTotal(this.license, this.renew, this.checkedLicenses);
            },
        },

        methods: {


            pay() {
                this.loading = true

                this.savePaymentMethod(() => {
                    this.saveBillingInfo(() => {
                        this.loading = false
                        this.$emit('pay');
                    }, () => {
                        this.loading = false
                    })
                }, () => {
                    this.loading = false

                })
            },

            savePaymentMethod(cb, cbError) {
                if (this.totalPrice > 0) {
                    if (this.paymentMode === 'newCard') {
                        if (!this.cardToken) {
                            // Save new card
                            this.$refs.newCard.save((card, source) => {
                                this.cardToken = source
                                cb()
                            }, error => {
                                cbError(error)
                            });
                        } else {
                            cb()
                        }
                    } else {
                        cb()
                    }
                } else {
                    cb()
                }
            },

            saveBillingInfo(cb, cbError) {
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

                cb()
//
//                this.$store.dispatch('saveCart', cartData)
//                    .then(response => {
//                        cb(response)
//                    })
//                    .catch(response => {
//                        cbError(response)
//                    })
            },

        },

        mounted() {
            if (this.card) {
                this.paymentMode = 'existingCard'
            }
        }
    }
</script>
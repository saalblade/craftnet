<template>
    <div>
        <div class="md:flex -mx-4">
            <div class="md:w-1/2 px-4">
                <h6>Payment Method</h6>
                <template v-if="card">
                    <p><label><input type="radio" value="existingCard" v-model="paymentMode" /> Use card <span>{{ card.brand }} •••• •••• •••• {{ card.last4 }} — {{ card.exp_month }}/{{ card.exp_year }}</span></label></p>
                </template>

                <p><label><input type="radio" value="newCard" v-model="paymentMode" /> Use a new credit card</label></p>

                <template v-if="paymentMode === 'newCard'">
                    <card-element v-if="!cardToken" ref="newCard" />
                    <p v-else>{{ cardToken.card.brand }} •••• •••• •••• {{ cardToken.card.last4 }} ({{ cardToken.card.exp_month }}/{{ cardToken.card.exp_year }}) <a class="delete icon" @click="cardToken = null">Delete</a></p>

                    <checkbox-field id="replaceCard" v-model="replaceCard" label="Save as my new credit card" />
                </template>

                <h6 class="mt-4">Coupon Code</h6>
                <text-field placeholder="XXXXXXX" id="coupon-code" size="12" />
            </div>
            <div class="md:w-1/2 px-4">
                <h6>Billing Informations</h6>
                <text-field placeholder="First Name" id="first-name" />
                <text-field placeholder="Last Name" id="last-name" />
                <text-field placeholder="Business Name" id="business-name" />
                <text-field placeholder="Business Tax ID" id="business-tax-id" />
                <text-field placeholder="Address 1" id="address-1" />
                <text-field placeholder="Address 2" id="address-2" />

                <div class="md:flex -mx-2">
                    <div class="md:w-1/2 px-2">
                        <text-field placeholder="City" id="city" />
                    </div>
                    <div class="md:w-1/2 px-2">
                        <text-field placeholder="Zip Code" id="zip-code" />
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
        <button @click="$emit('pay')" class="btn btn-primary">Pay {{ renewableLicensesTotal(license, renew, checkedLicenses)|currency }}</button>
    </div>
</template>

<script>
    import {mapState, mapGetters} from 'vuex'
    import CardElement from '../../CardElement'
    import CheckboxField from '../../fields/CheckboxField'
    import TextField from '../../fields/TextField'
    import SelectField from '../../fields/SelectField'

    export default {

        props: ['license', 'renew', 'checkedLicenses'],

        components: {
            CardElement,
            CheckboxField,
            TextField,
            SelectField,
        },

        data() {
            return {
                paymentMode: 'newCard',
                replaceCard: false,
            }
        },

        computed: {

            ...mapState({
                card: state => state.account.card,
            }),

            ...mapGetters({
                renewableLicensesTotal: 'renewableLicensesTotal',
            }),

        },
    }
</script>
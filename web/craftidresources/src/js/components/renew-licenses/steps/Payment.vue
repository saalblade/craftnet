<template>
    <div>
        <div class="md:flex -mx-4">
            <div class="md:w-1/2 px-4">
                <h6>Payment Method</h6>
                <card-element></card-element>

                <h6>Coupon Code</h6>
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
                <text-field placeholder="Zip Code" id="zip-code" />
                <text-field placeholder="City" id="city" />
                <text-field placeholder="Country" id="country" />
            </div>
        </div>

        <button @click="$emit('back')" class="btn btn-secondary">Back</button>
        <button @click="$emit('pay')" class="btn btn-primary">Pay {{ renewableLicensesTotal(license, renew, checkedLicenses)|currency }}</button>
    </div>
</template>

<script>
    import {mapGetters} from 'vuex'
    import CardElement from '../../CardElement'
    import TextField from '../../fields/TextField'

    export default {

        props: ['license', 'renew', 'checkedLicenses'],

        components: {
            CardElement,
            TextField,
        },

        computed: {

            ...mapGetters({
                renewableLicensesTotal: 'renewableLicensesTotal',
            }),

        },
    }
</script>
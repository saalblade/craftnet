<template>
    <div>
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
                <select-field :fullwidth="true" :options="countryOptions" v-model="billingInfo.country" id="country" :errors="errors['billingAddress.country']" @input="onCountryChange" />
            </div>
            <div class="md:w-1/2 px-2">
                <select-field :fullwidth="true" :options="stateOptions(billingInfo.country)" v-model="billingInfo.state" id="state" :errors="errors['billingAddress.state']" @input="onStateChange" />
            </div>
        </div>
    </div>
</template>

<script>
    import {mapGetters} from 'vuex'

    export default {
        props: ['billingInfo', 'errors'],

        computed: {
            ...mapGetters({
                countryOptions: 'craftId/countryOptions',
                stateOptions: 'craftId/stateOptions',
            }),
        },

        methods: {
            onCountryChange() {
                this.billingInfo.state = null
                const stateOptions = this.stateOptions(this.billingInfo.country)

                if (stateOptions.length) {
                    this.billingInfo.state = stateOptions[0].value
                }
            },

            onStateChange(value) {
                const billingInfo = JSON.parse(JSON.stringify(this.billingInfo))
                billingInfo.state = value

                this.$emit('update:billingInfo', billingInfo)
            }
        }
    }
</script>

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
                <select-field :fullwidth="true" :options="[{label: 'State', value: 0}]" :value="0" id="state" />
            </div>
            <div class="md:w-1/2 px-2">
                <select-field :fullwidth="true" :options="[{label: 'Country', value: 0}]" :value="0" id="country" />
            </div>
        </div>
    </div>
</template>



<script>
    import CraftComponents from "@benjamindavid/craftcomponents";

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
                errors: {},
            }
        },

        components: {
            ...CraftComponents,
        },

    }
</script>
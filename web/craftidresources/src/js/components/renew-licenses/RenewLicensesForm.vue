<template>
    <div v-if="license.expirable && license.expiresOn">
        <h5>Renew Licenses</h5>

        <template v-if="step === 'extend-updates'">
            <extend-updates :license="license" @cancel="$emit('cancel')" @continue="step = 'cart'" :renew.sync="renew"></extend-updates>
        </template>

        <template v-if="step === 'cart'">
            <cart :license="license" @back="step = 'extend-updates'" @checkout="step = 'payment'" :checkedLicenses.sync="checkedLicenses" :renew="renew"></cart>
        </template>

        <template v-if="step === 'payment'">
            <payment :license="license" :renew="renew" :checkedLicenses="checkedLicenses" @back="step = 'cart'" @pay="step = 'thank-you'"></payment>
        </template>

        <template v-if="step === 'thank-you'">
            <thank-you @done="step = 'extend-updates'; $emit('cancel')"></thank-you>
        </template>
    </div>
</template>

<script>
    import Cart from '../renew-licenses/steps/Cart'
    import ExtendUpdates from '../renew-licenses/steps/ExtendUpdates'
    import Payment from '../renew-licenses/steps/Payment'
    import ThankYou from '../renew-licenses/steps/ThankYou'

    export default {

        props: ['license'],

        components: {
            Cart,
            ExtendUpdates,
            Payment,
            ThankYou,
        },

        data() {
            return {
                renew: 1,
                checkedLicenses: [],
                step: 'extend-updates',
            }
        },

    }
</script>
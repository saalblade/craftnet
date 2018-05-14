<template>
    <div v-if="license.expirable && license.expiresOn">
        <h2 class="mb-3">Renew Licenses</h2>

        <template v-if="step === 'extend-updates'">
            <extend-updates :license="license" @cancel="$emit('cancel')" @continue="step = 'plugins'" :renew.sync="renew"></extend-updates>
        </template>

        <template v-if="step === 'plugins'">
            <plugins :license="license" @back="step = 'extend-updates'" @checkout="step = 'payment'" :checkedLicenses.sync="checkedLicenses" :renew="renew"></plugins>
        </template>

        <template v-if="step === 'payment'">
            <payment :license="license" :renew="renew" :checkedLicenses="checkedLicenses" @back="step = renewableLicenses(license, renew).length > 1 ? 'plugins' : 'extend-updates'" @pay="step = 'thank-you'"></payment>
        </template>

        <template v-if="step === 'thank-you'">
            <thank-you @done="step = 'extend-updates'; $emit('cancel')"></thank-you>
        </template>
    </div>
</template>

<script>
    import {mapGetters} from 'vuex'
    import Plugins from './steps/Plugins'
    import ExtendUpdates from '../renew-licenses/steps/ExtendUpdates'
    import Payment from '../renew-licenses/steps/Payment'
    import ThankYou from '../renew-licenses/steps/ThankYou'

    export default {

        props: ['license'],

        components: {
            Plugins,
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

        computed: {

            ...mapGetters({
                renewableLicenses: 'renewableLicenses',
            }),

        },

    }
</script>
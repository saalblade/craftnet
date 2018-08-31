<template>
    <div v-if="license.expirable && license.expiresOn">
        <h2 class="mb-3">Renew Licenses</h2>

        <extend-updates
                v-if="step === 'extend-updates'"
                :license="license"
                :renew.sync="renew"
                @cancel="$emit('cancel')"
                @continue="step = 'plugins'" />

        <plugins
                v-if="step === 'plugins'"
                :checkedLicenses.sync="checkedLicenses"
                :license="license"
                :renew="renew"
                @back="step = 'extend-updates'"
                @checkout="step = 'payment'" />

        <payment
                v-if="step === 'payment'"
                :checkedLicenses="checkedLicenses"
                :license="license"
                :renew="renew"
                @back="step = renewableLicenses(license, renew).length > 1 ? 'plugins' : 'extend-updates'"
                @pay="step = 'thank-you'" />

        <thank-you
                v-if="step === 'thank-you'"
                @done="step = 'extend-updates'; $emit('cancel')" />

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
                renewableLicenses: 'licenses/renewableLicenses',
            }),

        },

    }
</script>
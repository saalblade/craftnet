<template>
    <div v-if="license.expirable && license.expiresOn">
        <h2 class="mb-3">Renew Licenses</h2>

        <extend-updates
                v-if="renewLicensesStep === 'extend-updates'"
                :license="license"
                :renew.sync="renew"
                @cancel="$emit('cancel')"
                @continue="$store.commit('app/updateRenewLicensesStep', 'plugins')" />

        <plugins
                v-if="renewLicensesStep === 'plugins'"
                :checkedLicenses.sync="checkedLicenses"
                :license="license"
                :renew="renew"
                @back="$store.commit('app/updateRenewLicensesStep', 'extend-updates')"
                @addToCart="$emit('cancel')" />

        <renew-plugin-license
                v-if="renewLicensesStep === 'renew-plugin-license'"
                :license="license"
                @cancel="$emit('cancel')"
                @addToCart="$emit('cancel')" />
    </div>
</template>

<script>
    import {mapState} from 'vuex'
    import Plugins from './steps/Plugins'
    import ExtendUpdates from './steps/ExtendUpdates'
    import RenewPluginLicense from './steps/RenewPluginLicense'
    import helpers from '../../../mixins/helpers'

    export default {
        mixins: [helpers],

        props: ['license'],

        components: {
            Plugins,
            ExtendUpdates,
            RenewPluginLicense,
        },

        data() {
            return {
                renew: 0,
                checkedLicenses: [],
            }
        },

        computed: {
            ...mapState({
                renewLicensesStep: state => state.app.renewLicensesStep,
            }),
        },
    }
</script>

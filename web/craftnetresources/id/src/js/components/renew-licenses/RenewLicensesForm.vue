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
                @addToCart="$emit('cancel')" />
    </div>
</template>

<script>
    import {mapGetters} from 'vuex'
    import Plugins from './steps/Plugins'
    import ExtendUpdates from '../renew-licenses/steps/ExtendUpdates'

    export default {

        props: ['license'],

        components: {
            Plugins,
            ExtendUpdates,
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
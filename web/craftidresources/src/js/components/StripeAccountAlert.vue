<template>
    <div v-if="enableRenewalFeatures && !$root.stripeAccountLoading && !stripeAccount" class="alert alert-warning" role="alert">
        <strong>Stripe account missing.</strong>
        Define a Stripe account in the <router-link to="/developer/settings">developer settings</router-link>.
    </div>
</template>

<script>
    import {mapState, mapGetters} from 'vuex'

    export default {

        computed: {

            ...mapState({
                stripeAccount: state => state.account.stripeAccount,
            }),

            ...mapGetters({
                enableRenewalFeatures: 'enableRenewalFeatures',
            }),

        },

    }
</script>

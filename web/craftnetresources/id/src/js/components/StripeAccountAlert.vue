<template>
    <page-alert v-if="!stripeAccountLoading && !stripeAccount" type="warning">
        <strong>Stripe account missing.</strong>
        Define a Stripe account in the <router-link to="/developer/settings">developer settings</router-link>.
    </page-alert>
</template>

<script>
    import {mapState} from 'vuex'
    import PageAlert from './PageAlert'

    export default {
        components: {
            PageAlert,
        },

        computed: {
            ...mapState({
                stripeAccount: state => state.account.stripeAccount,
                stripeAccountLoading: state => state.app.stripeAccountLoading,
            }),
        },
    }
</script>

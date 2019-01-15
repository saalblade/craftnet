<template>
    <div v-if="!stripeAccountLoading && !stripeAccount" class="top-alert alert alert-warning" role="alert">
        <strong>Stripe account missing.</strong>
        Define a Stripe account in the <router-link to="/developer/settings">developer settings</router-link>.
    </div>
</template>

<script>
    import {mapState} from 'vuex'

    export default {

        computed: {

            ...mapState({
                stripeAccount: state => state.account.stripeAccount,
                stripeAccountLoading: state => state.app.stripeAccountLoading,
            }),

        },

    }
</script>

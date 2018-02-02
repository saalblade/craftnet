<template>
	<div v-if="!$root.stripeAccountLoading && !stripeAccount" class="alert alert-warning" role="alert">
		<strong>Stripe account missing.</strong>
		Define a Stripe account in the <router-link to="/developer/settings">developer settings</router-link>.
	</div>
</template>

<script>
    import { mapGetters } from 'vuex'

    export default {

        computed: {

            ...mapGetters({
                stripeAccount: 'stripeAccount',
            }),

        },

    }
</script>

<template>
	<div v-if="!stripeAccount" class="alert alert-warning" role="alert">
		<strong>Bank account missing.</strong>
		Define a bank account in the <router-link to="/developer/settings">developer settings</router-link>.
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

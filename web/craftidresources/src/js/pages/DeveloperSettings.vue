<template>
	<div>
		<h1>Developer Settings</h1>
		<stripe-account></stripe-account>
	</div>
</template>

<script>
    import StripeAccount from '../components/StripeAccount'

    export default {

        components: {
            StripeAccount
        },

    }
</script>

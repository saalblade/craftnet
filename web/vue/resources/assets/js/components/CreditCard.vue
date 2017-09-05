<template>
	<div>
		<form @submit.prevent="submit()">
			<label for="card-element">
				Credit or debit card
			</label>

			<div class="border p-2 mb-3">
				<div ref="cardElement" id="card-element">
					<!-- a Stripe Element will be inserted here. -->
				</div>
			</div>

			<!-- Used to display form errors -->
			<div id="card-errors" role="alert"></div>
		</form>
	</div>
</template>


<script>
	export default {

	    data() {
	    	return {
	    	    stripe: null,
				elements: null,
				card: null
			};
		},

	    mounted() {
            // Create a Stripe client
            this.stripe = Stripe('pk_test_B2opWU3D3nmA2QXyHKlIx6so');

			// Create an instance of Elements
            this.elements = this.stripe.elements();

            this.card = this.elements.create('card');

			// Add an instance of the card Element into the `card-element` <div>
            // card.mount(this.$refs.cardElement);

            // Vue likes to stay in control of $el but Stripe needs a real element
            const el = document.createElement('div')
            this.card.mount(el)

            // this.$children cannot be used because it expects a VNode :(
            this.$refs.cardElement.appendChild(el)
		},

		methods: {
	        submit() {
	            let vm = this;
                vm.$emit('beforeCreateToken');
                this.stripe.createToken(this.card).then(function(result) {
                    if (result.error) {
                        // Inform the user if there was an error
                        var errorElement = document.getElementById('card-errors');
                        errorElement.textContent = result.error.message;
                    } else {
                        // Send the token to your server
                        vm.card.clear();
                        vm.$emit('stripeTokenHandle', result.token);
                    }
                });
			}
		}
	}
</script>
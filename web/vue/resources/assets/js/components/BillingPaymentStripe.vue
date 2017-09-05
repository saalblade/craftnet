<template>
	<div>
		<h4>Payment Method</h4>

		<div v-if="stripeCustomerLoading" class="spinner"></div>

		<div v-if="stripeCustomer && !stripeCustomerLoading">


			<div v-if="!editing">

				<button @click="editing = true" type="button" class="float-right btn btn-secondary btn-sm" data-facebox="#billing-contact-info-modal">
					<i class="fa fa-credit-card"></i>
					Change Card
				</button>

				<div v-if="stripeCard">
					{{ stripeCard.brand }} •••• •••• •••• {{ stripeCard.last4 }} — {{ stripeCard.exp_month }}/{{ stripeCard.exp_year }}
				</div>
			</div>


			<div :class="{'d-none': !editing}">

				<!--<card ref="card" class="border rounded mb-3 p-2" stripe="pk_test_B2opWU3D3nmA2QXyHKlIx6so"></card>-->

				<credit-card @beforeCreateToken="loading = true" @stripeTokenHandle="handleStripeToken"></credit-card>

				<button class="btn btn-primary" @click="save()">Save</button>
				<button class="btn btn-secondary" @click="cancel()">Cancel</button>
				<div class="spinner" v-if="loading"></div>
			</div>
		</div>
	</div>
</template>


<script>
    import { mapGetters } from 'vuex'
	import CreditCard from './CreditCard'

    export default {
        components: {
			CreditCard
        },

		data() {
            return {
                editing: false,
				loading: false,
			}
		},

        computed: {
            ...mapGetters({
                stripeCustomer: 'stripeCustomer',
                stripeCard: 'stripeCard',
            }),

            stripeCustomerLoading() {
                return this.$root.stripeCustomerLoading;
            }
        },

		methods: {
            handleStripeToken(token) {
                this.loading = true;
                this.$store.dispatch('saveCreditCard', token).then(response => {
					this.loading = false;
                    this.editing = false;
                    this.$root.displayNotice('Credit card saved.');
				});
			},
            cancel() {
				this.editing = false;
			},
            save() {
                // this.loading = true;
                this.$children[0].submit();
			}
		}
    }
</script>
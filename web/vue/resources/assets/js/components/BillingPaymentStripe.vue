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

				<card ref="card" class="border rounded mb-3 p-2" stripe="pk_test_B2opWU3D3nmA2QXyHKlIx6so"></card>

				<button class="btn btn-primary" @click="save()">Save</button>
				<button class="btn btn-secondary" @click="cancel()">Cancel</button>
				<div class="spinner" v-if="loading"></div>
			</div>
		</div>
	</div>
</template>


<script>
    import { mapGetters } from 'vuex'
    import { Card, createToken, instance } from 'vue-stripe-elements'

    export default {
        components: {
			Card
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
            cancel() {
				this.editing = false;
                this.$refs.card.$children[0]._element.clear();
			},
            save() {
                let vm = this;
                this.loading = true;
                createToken().then(data => {
                    console.log(data.token)
                    this.$store.dispatch('saveCreditCard', data.token).then(response => {
                        console.log('credit card saved')
                        this.$refs.card.$children[0]._element.clear();
						this.editing = false;
						this.loading = false;
					});
				})
			}
		}
    }
</script>
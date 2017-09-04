<template>

	<div class="card mb-3">
		<div class="card-header"><i class="fa fa-institution"></i> Stripe Account</div>
		<div class="card-body">

			<p v-if="loading">Loading…</p>

			<template v-else>
				<template v-if="connected">

					<div class="row">
						<dl class="col-md-6">
							<dt>Account name</dt>
							<dd><template v-if="stripeAccount.display_name">{{ stripeAccount.display_name }}</template><em v-else class="text-secondary">Not provided</em></dd>
							<dt>Business name</dt>
							<dd><template v-if="stripeAccount.business_name">{{ stripeAccount.business_name }}</template><em v-else class="text-secondary">Not provided</em></dd>
							<dt>Payouts enabled</dt>
							<dd v-if="stripeAccount.payouts_enabled" class="text-success">Yes</dd>
							<dd v-else class="text-success">No</dd>
							<dt>Details Submitted</dt>
							<dd v-if="stripeAccount.details_submitted" class="text-success">Yes</dd>
							<dd v-else class="text-success">No</dd>
						</dl>
						<dl class="col-md-6">
							<dt>Email</dt>
							<dd>{{ stripeAccount.email }}</dd>
							<dt>ID</dt>
							<dd>{{ stripeAccount.id }}</dd>
							<dt>Country</dt>
							<dd>{{ stripeAccount.country }}</dd>
							<dt>Statement descriptor</dt>
							<dd><template v-if="stripeAccount.statement_descriptor">{{ stripeAccount.statement_descriptor }}</template><em v-else class="text-secondary">Not provided</em></dd>
						</dl>
					</div>

					<button type="button" class="btn btn-secondary btn-sm" @click="disconnect()">Disconnect Account</button> <div v-if="disconnectLoading" class="spinner"></div>

				</template>

				<template v-else>
					<a class="btn btn-primary" href="https://id.craftcms.dev/index.php/stripe/connect">Connect your Stripe account</a>
				</template>
			</template>

		</div>
	</div>

</template>

<script>
    import { mapGetters } from 'vuex'
    import TextField from '../components/fields/TextField'

    export default {
        components: {
            TextField
        },

        data() {
            return {
                connected: false,
                loading: true,
				disconnectLoading: false,
            }
        },

		computed: {
            ...mapGetters({
                stripeAccount: 'stripeAccount',
            }),
		},

		methods: {
          	disconnect() {
                this.disconnectLoading = true;

                this.$store.dispatch('disconnectStripeAccount').then(() => {
                    this.connected = false;
					this.disconnectLoading = false;
                });
			}
		},

        mounted() {
            this.connected = false;

            if(window.stripeAccessToken) {
                this.connected = true;

                this.$store.dispatch('getStripeAccount').then(() => {
                    this.loading = false;
                });
            } else {
                this.loading = false;
			}
        }
    }
</script>
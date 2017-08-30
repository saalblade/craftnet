<template>
	<div v-if="payment">
		<div class="card mb-3">
			<div class="card-body">

				<h3>{{ payment.amount|currency }}</h3>
				<p class="text-secondary">#00000{{paymentId}}</p>

				<hr>

				<dl>
					<dt>Amount</dt>
					<dd>{{payment.amount|currency}}</dd>
					<dt>Payment ID</dt>
					<dd>#{{paymentId}}</dd>
					<dt>Customer</dt>
					<dd><router-link :to="'/customers/'+payment.customer.id">{{payment.customer.email}}</router-link></dd>
					<dt>Items</dt>
					<dd>{{payment.items.length}}</dd>
					<dt>Date</dt>
					<dd>{{payment.date}}</dd>
				</dl>
			</div>
		</div>

		<div class="card mb-3">
			<div class="card-header">Items</div>

			<div class="card-body">
				<table class="table">
					<thead>
					<tr>
						<th>Name</th>
					</tr>
					</thead>
					<tbody>
					<tr v-for="plugin in payment.items">
						<td><router-link :to="'/plugins/'+plugin.id">{{ plugin.name }}</router-link></td>
					</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</template>

<script>
    import { mapGetters } from 'vuex'

    export default {

        computed: {
            ...mapGetters({
                payments: 'payments',
            }),

			paymentId() {
                return this.$route.params.id;
			},

			payment() {
				return this.payments[this.paymentId];
			}
        }
    }
</script>
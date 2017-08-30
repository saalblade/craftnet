<template>

	<div v-if="customer">
		<div class="card mb-3">
			<div class="card-body">

				<h3>{{ customer.email }}</h3>
				<p class="text-secondary">#00000{{customerId}}</p>

				<hr>

				<dl>
					<dt>ID</dt>
					<dd>{{customerId}}</dd>
					<dt>Email</dt>
					<dd>{{customer.email}}</dd>
					<dt>Full name</dt>
					<dd>{{customer.fullName}}</dd>
				</dl>

			</div>
		</div>

		<div class="card mb-3">
			<div class="card-header">Payments</div>
			<div class="card-body">

				<table class="table">
					<thead>
					<tr>
						<th>Amount</th>
						<th>Items</th>
						<th>Customer</th>
						<th>Date</th>
					</tr>
					</thead>
					<tbody>
					<tr v-for="(payment, key) in customerPayments">
						<td><router-link :to="'/payments/'+key">{{payment.amount|currency}}</router-link></td>
						<td>{{ payment.items.length }}</td>
						<td><router-link :to="'/customers/'+payment.customer.id">{{payment.customer.email}}</router-link></td>
						<td>{{ payment.date }}</td>
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
                customers: 'customers',
                payments: 'payments',
            }),

            customerId() {
                return this.$route.params.id;
            },

            customer() {
				return this.customers.find(c => c.id == this.customerId);
            },

            customerPayments() {
				return this.payments.filter(p => p.customer.id == this.customerId);
			}
        }
    }
</script>
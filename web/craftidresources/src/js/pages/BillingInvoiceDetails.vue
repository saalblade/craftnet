<template>
	<div>
		<p><router-link class="nav-link" to="/account/billing" exact>← Billing</router-link></p>
		<h1>Invoice {{ invoice.shortNumber }}</h1>

		<div class="card mb-4">
			<div class="card-body">
				<dl>
					<dt>Invoice Number</dt>
					<dd>{{ invoice.number }}</dd>

					<dt>Date Paid</dt>
					<dd>{{ invoice.datePaid }}</dd>
				</dl>

				<billing-address :address="invoice.billingAddress" class="mb-4"></billing-address>

				<table class="table">
					<thead>
					<tr>
						<th>Item</th>
						<th>Price</th>
						<th>Quantity</th>
						<th></th>
					</tr>
					</thead>
					<tbody>
						<tr v-for="lineItem in invoice.lineItems">
							<td>{{ lineItem.description }}</td>
							<td>{{ lineItem.salePrice|currency }}</td>
							<td>{{ lineItem.qty }}</td>
							<td class="text-right">{{ lineItem.subtotal|currency }}</td>
						</tr>
						<tr v-for="adjustment in invoice.adjustments">
							<th colspan="3" class="text-right">{{ adjustment.name }}</th>
							<td class="text-right">{{ adjustment.amount|currency }}</td>
						</tr>
						<tr>
							<th colspan="3" class="text-right">Items Price</th>
							<td class="text-right">{{ invoice.itemTotal|currency }}</td>
						</tr>
						<tr>
							<th colspan="3" class="text-right">Total Price</th>
							<td class="text-right">{{ invoice.totalPrice|currency }}</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>

		<div class="card mb-4">
			<div class="card-body">
				<h3 class="mb-2">Transactions</h3>

				<table class="table">
					<thead>
					<tr>
						<th>Type</th>
						<th>Status</th>
						<th>Amount</th>
						<th>Payment Amount</th>
						<th>Method</th>
						<th>Date</th>
					</tr>
					</thead>
					<tbody>
					<tr v-for="transaction in invoice.transactions">
						<td>{{ transaction.type }}</td>
						<td>{{ transaction.status }}</td>
						<td>{{ transaction.amount|currency }}</td>
						<td>{{ transaction.paymentAmount|currency }}</td>
						<td>Gateway {{ transaction.gatewayId }}</td>
						<td>{{ transaction.dateCreated }}</td>
					</tr>
					</tbody>
				</table>
			</div>
		</div>

	</div>
</template>

<script>
    import { mapGetters } from 'vuex'
    import BillingAddress from '../components/BillingAddress'
    import CardIcon from '../components/CardIcon'

    export default {

        components: {
            BillingAddress,
            CardIcon,
        },

        computed: {

            ...mapGetters({
                getInvoiceByNumber: 'getInvoiceByNumber',
            }),

            invoice() {
				return this.getInvoiceByNumber(this.$route.params.number);
			},

        }

    }
</script>

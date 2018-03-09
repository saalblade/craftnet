<template>
	<div>
		<p><router-link class="nav-link" to="/account/billing" exact>← Billing</router-link></p>
		<h1>Invoice {{ invoice.number }}</h1>

		<div class="card mb-4">
			<div class="card-body">
				<p class="text-secondary">Date: {{ invoice.datePaid }}</p>

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
							<td>{{ lineItem.subtotal|currency }}</td>
						</tr>
						<tr v-for="adjustment in invoice.adjustments">
							<th colspan="3">{{ adjustment.name }}</th>
							<td>{{ adjustment.amount|currency }}</td>
						</tr>
						<tr>
							<th colspan="3">Items Price</th>
							<td>{{ invoice.itemTotal|currency }}</td>
						</tr>
						<tr>
							<th colspan="3">Total Price</th>
							<td>{{ invoice.totalPrice|currency }}</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>

		<div class="card">
			<div class="card-body">
				<h3 class="mb-2">Payment Method</h3>

				<div v-if="invoice.card" class="credit-card">
					<card-icon :brand="invoice.card.brand"></card-icon>
					<ul class="list-reset">
						<li>Number: •••• •••• •••• {{ invoice.card.last4 }}</li>
						<li>Expiry: {{ invoice.card.exp_month }}/{{ invoice.card.exp_year }}</li>
					</ul>
				</div>
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

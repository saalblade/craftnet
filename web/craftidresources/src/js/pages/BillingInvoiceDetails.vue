<template>
	<div>
		<p><router-link class="nav-link" to="/account/billing" exact>← Billing</router-link></p>
		<h1>Invoice {{ invoice.number }}</h1>

		<div class="card">
			<div class="card-body">
				<p class="text-secondary">Date: {{ invoice.datePaid }}</p>

				<div class="lg:flex">
					<div class="lg:w-1/2">
						<billing-address :address="invoice.billingAddress"></billing-address>
					</div>
					<div class="lg:w-1/2 sm:mt-4 lg:mt-0">

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

				<table class="table">
					<thead>
					<tr>
						<th>Line Item</th>
						<th>Purchasable ID</th>
						<th class="text-right">Amount</th>
					</tr>
					</thead>
					<tbody>
						<tr v-for="lineItem in invoice.lineItems">
							<td>{{ lineItem.id }}</td>
							<td>{{ lineItem.purchasableId }}</td>
							<td>{{ lineItem.total|currency }}</td>
						</tr>

						<tr>
							<th colspan="2">Total</th>
							<td>{{ invoice.totalPrice|currency }}</td>
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

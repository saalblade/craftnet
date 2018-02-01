<template>
	<div>
		<p><router-link class="nav-link" to="/account/billing" exact>← Billing</router-link></p>
		<h1>#INV000{{ invoice.id }}</h1>


		<div class="card">
			<div class="card-body">
				<p class="text-secondary">Order Date: {{ invoice.date }}</p>

				<div class="lg:flex">
					<div class="lg:w-1/2">
						<pre>Pixel & Tonic, Inc.
Address
Bend, OR 97701
USA
support@craftcms.com</pre>
					</div>
					<div class="lg:w-1/2 sm:mt-4 lg:mt-0">

						<pre>Payment Method
{{ invoice.paymentMethod.type }} ending with {{ invoice.paymentMethod.last4 }}</pre>

						<pre>{{ currentUser.businessName }}
{{ currentUser.businessVatId }}
{{ currentUser.businessAddressLine1 }}
{{ currentUser.businessAddressLine2 }}
{{ currentUser.businessCity }}
{{ currentUser.businessState }}
{{ currentUser.businessZipCode }}
{{ currentUser.businessCountry }}
</pre>
					</div>
				</div>

				<table class="table">
					<thead>
					<tr>
						<th>Item</th>
						<th>Type</th>
						<th class="text-right">Amount</th>
					</tr>
					</thead>
					<tbody>
						<tr v-for="item in invoice.items">
							<td>{{ item.name }}</td>
							<td class="text-secondary">{{ item.type }}</td>
							<td class="text-right">{{ item.amount|currency }}</td>
						</tr>

						<tr>
							<th colspan="2">Total</th>
							<td class="text-right">{{ invoice.total|currency }}</td>
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

        components: {
        },

        computed: {

            ...mapGetters({
                getInvoiceById: 'getInvoiceById',
                currentUser: 'currentUser',
            }),

            invoice() {
				return this.getInvoiceById(this.$route.params.id);
			},

        }

    }
</script>

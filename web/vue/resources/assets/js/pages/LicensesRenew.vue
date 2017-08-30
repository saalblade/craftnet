<template>
	<div>
		<div class="card">
			<div class="card-body">

				<div class="text-center mt-1 mb-4">
					<h3>Renew Licenses</h3>
					<p class="text-secondary">Renew your licenses for another year of great updates.</p>
				</div>

				<table class="table">
					<thead>
					<tr>
						<th></th>
						<th>License Key</th>
						<th>Item</th>
						<th>Domain</th>
						<th>Next Payment</th>
						<th>Price</th>
					</tr>
					</thead>
					<tbody>
					<template>
						<tr v-for="license in licenses">
							<td>
								<input type="checkbox" :value="license.id" v-model="selectedLicenses" />
							</td>

							<template v-if="license.type == 'pluginLicense'">
								<td><router-link :to="'/licenses/plugins/'+license.id">000000{{ license.id }}</router-link></td>
								<td>{{ license.plugin.title }}</td>
							</template>

							<template v-if="license.type == 'craftLicense'">
								<td><router-link :to="'/licenses/craft/'+license.id">000000{{ license.id }}</router-link></td>
								<td>Craft {{ license.craftEdition.value }}</td>
							</template>

							<td>{{ license.domain }}</td>

							<td>November 16th, 2017</td>

							<td>
								<template v-if="license.plugin">
										{{ license.plugin.updatePrice|currency }} for 1 year
								</template>
							</td>
						</tr>
					</template>
					</tbody>
				</table>

				<hr>

				<div class="text-center mt-4">
					<p>
						<select name="" id="">
							<option value="">Renew for 3 years and save $XX.00</option>
						</select>
					</p>

					<div class="row">
						<div class="col-sm-6 text-right"><strong>Subtotal</strong></div>
						<div class="col-sm-6 text-left">{{ subtotal|currency }}</div>
					</div>
					<div class="row">
						<div class="col-sm-6 text-right"><strong>Pro-Rate discount</strong></div>
						<div class="col-sm-6 text-left">$XX.00</div>
					</div>
					<div class="row">
						<div class="col-sm-6 text-right"><strong>Total</strong></div>
						<div class="col-sm-6 text-left">$XX.00</div>
					</div>

					<div class="mt-3">
						<template v-if="selectedLicenses.length > 0">
							<a class="btn btn-primary btn-lg" href="#">Renew {{ selectedLicenses.length }} licenses</a>
						</template>

						<template v-else>
							<a class="btn btn-primary btn-lg disabled" href="#">Renew {{ selectedLicenses.length }} licenses</a>
						</template>

					</div>
				</div>

			</div>
		</div>
	</div>
</template>

<script>
    import { mapGetters } from 'vuex'
    import LicenseTable from '../components/LicenseTable';

    export default {
        components: {
            LicenseTable
        },

		data() {
          	return {
          	    selectedLicenses: []
			}
		},

        computed: {
            ...mapGetters({
                licenses: 'licenses',
            }),
			subtotal() {
              	return this.licenses.reduce((a, b) => {
              	    if(b.plugin && this.selectedLicenses.find(lId => lId == b.id)) {
              	        return a + parseFloat(b.plugin.updatePrice);
					}

					return a;
				}, 0);
			},
        },

		mounted() {
            this.licenses.forEach(license => {
                this.selectedLicenses.push(license.id)
			})
		}
    }
</script>

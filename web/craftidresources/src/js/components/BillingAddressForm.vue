<template>
	<div>
		<div class="flex">
			<div class="flex-1">
				<h4>Billing Address</h4>

				<template v-if="!showForm && billingAddress">
					<ul v-if="billingAddress.firstName || billingAddress.lastName || billingAddress.address1 || billingAddress.address2 || billingAddress.city || billingAddress.country || billingAddress.businessName || billingAddress.state || billingAddress.zipCode" class="list-reset">
						<li v-if="billingAddress.firstName || billingAddress.lastName">{{ billingAddress.firstName }} {{ billingAddress.lastName }}</li>
						<li v-if="billingAddress.businessName">{{ billingAddress.businessName }}</li>
						<li v-if="billingAddress.address1">{{ billingAddress.address1 }}</li>
						<li v-if="billingAddress.address2">{{ billingAddress.address2 }}</li>
						<li v-if="billingAddress.zipCode || billingAddress.city">
							<template v-if="billingAddress.zipCode">{{ billingAddress.zipCode }}</template>
							<template v-if="billingAddress.city">{{ billingAddress.city }}</template>
						</li>
						<li v-if="billingAddress.countryText">{{ billingAddress.countryText}}</li>
						<li v-if="billingAddress.state">{{ billingAddress.state }}</li>
					</ul>

					<p v-else class="text-secondary">Billing address not defined.</p>
				</template>
			</div>

			<div v-if="!showForm">
				<button @click="edit()" type="button"
						class="btn btn-secondary btn-sm"
						data-facebox="#billing-contact-info-modal">
					<i class="fas fa-pencil-alt"></i>
					Edit
				</button>
			</div>
		</div>

		<form v-if="showForm" @submit.prevent="save()">
			<text-field id="firstName" label="First Name" v-model="invoiceDetailsDraft.firstName" :errors="errors.firstName" />
			<text-field id="lastName" label="Last Name" v-model="invoiceDetailsDraft.lastName" :errors="errors.lastName" />
			<text-field id="businessName" label="Business Name" v-model="invoiceDetailsDraft.businessName" :errors="errors.businessName" />
			<text-field id="address1" label="Address Line 1" v-model="invoiceDetailsDraft.address1" :errors="errors.address1" />
			<text-field id="address2" label="Address Line 2" v-model="invoiceDetailsDraft.address2" :errors="errors.address2" />
			<text-field id="city" label="City" v-model="invoiceDetailsDraft.city" :errors="errors.city" />
			<select-field id="country" label="Country" v-model="invoiceDetailsDraft.country" :options="countryOptions" @input="onCountryChange" />
			<select-field id="state" label="State" v-model="invoiceDetailsDraft.state" :options="stateOptions" />
			<text-field id="zipCode" label="Zip Code" v-model="invoiceDetailsDraft.zipCode" :errors="errors.zipCode" />

			<input type="submit" class="btn btn-primary" value="Save" />
			<input type="button" class="btn btn-secondary" value="Cancel" @click="cancel()" />
		</form>

	</div>
</template>

<script>
    import {mapGetters} from 'vuex'
    import TextField from './fields/TextField'
    import SelectField from './fields/SelectField'

    export default {

        components: {
            TextField,
            SelectField,
        },

        data() {
            return {
                errors: {},
                showForm: false,
                invoiceDetailsDraft: {},
            }
        },

        computed: {

            ...mapGetters({
                currentUser: 'currentUser',
                billingAddress: 'billingAddress',
                countries: 'countries',
            }),

            countryOptions() {
                let options = [];

                for (let iso in this.countries) {
                    if (this.countries.hasOwnProperty(iso)) {
                        options.push({
                            label: this.countries[iso].name,
                            value: iso,
                        })
                    }
                }

                return options;
            },

            stateOptions() {
                let options = [];

                const iso = this.invoiceDetailsDraft.country

				if (!this.countries[iso] || (this.countries[iso] && !this.countries[iso].states)) {
				    return [];
				}

				const states = this.countries[iso].states

                for (let stateIso in states) {
                    if (states.hasOwnProperty(stateIso)) {
                        options.push({
                            label: states[stateIso],
                            value: stateIso,
                        })
                    }
                }

                return options
			}
        },

        methods: {

            /**
             * Edit billing address.
             */
            edit() {
                this.showForm = true;

                if(this.billingAddress) {
                    this.invoiceDetailsDraft = JSON.parse(JSON.stringify(this.billingAddress));
                }
            },

            /**
             * Save the billing address.
             */
            save() {
                let data = {
                    firstName: this.invoiceDetailsDraft.firstName,
                    lastName: this.invoiceDetailsDraft.lastName,
                    businessName: this.invoiceDetailsDraft.businessName,
                    address1: this.invoiceDetailsDraft.address1,
                    address2: this.invoiceDetailsDraft.address2,
                    city: this.invoiceDetailsDraft.city,
                    state: this.invoiceDetailsDraft.state,
                    zipCode: this.invoiceDetailsDraft.zipCode,
                    country: this.invoiceDetailsDraft.country,
                }

                if(this.billingAddress) {
                    data = Object.assign({}, data, {
                        id: this.billingAddress.id,
                        businessTaxId: this.billingAddress.businessTaxId,
                    });
                }

                this.$store.dispatch('saveBillingInfo', data).then(response => {
                    this.$root.displayNotice('Billing address saved.');
                    this.showForm = false;
                    this.errors = {};
                }).catch(response => {
                    const errorMessage = response.data && response.data.error ? response.data.error : 'Couldn’t save billing address.';
                    this.$root.displayError(errorMessage);
                    this.errors = response.data && response.data.errors ? response.data.errors : {};
                });
            },

            /**
             * Cancel changes.
             */
            cancel() {
                this.showForm = false;
                this.errors = {};
            },

			onCountryChange() {
                this.invoiceDetailsDraft.state = null

				if(this.stateOptions.length) {
                    this.invoiceDetailsDraft.state = this.stateOptions[0].value
                }
			}
        }

    }
</script>

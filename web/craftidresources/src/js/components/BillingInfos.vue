<template>
	<div>
		<h4>Invoice details</h4>

		<div v-if="!showForm" class="row">
			<div class="col-sm-8">

				<div class="flex-auto">
					<p>Here’s what currently appears on your receipts:</p>

					<pre>{{ companyInfos.businessName }}
{{ companyInfos.businessVatId }}
{{ companyInfos.businessAddressLine1 }}
{{ companyInfos.businessAddressLine2 }}
{{ companyInfos.businessCity }}
{{ companyInfos.businessState }}
{{ companyInfos.businessZipCode }}
{{ companyInfos.businessCountry }}</pre>

				</div>
			</div>
			<div class="col-sm-4 text-right">
				<button @click="editInvoiceDetails()" type="button" class="btn btn-secondary btn-sm" data-facebox="#billing-contact-info-modal">
					<i class="fa fa-pencil"></i>
					Change information
				</button>
			</div>
		</div>

		<form v-if="showForm" @submit.prevent="save()">
			<text-field id="businessName" label="Name" v-model="invoiceDetailsDraft.businessName" :errors="errors.businessName" />
			<text-field id="businessVatId" label="Vat ID" v-model="invoiceDetailsDraft.businessVatId" :errors="errors.businessVatId" />
			<text-field id="businessAddressLine1" label="Address Line 1" v-model="invoiceDetailsDraft.businessAddressLine1" :errors="errors.businessAddressLine1" />
			<text-field id="businessAddressLine2" label="Address Line 2" v-model="invoiceDetailsDraft.businessAddressLine2" :errors="errors.businessAddressLine2" />
			<text-field id="businessCity" label="City" v-model="invoiceDetailsDraft.businessCity" :errors="errors.businessCity" />
			<text-field id="businessState" label="State" v-model="invoiceDetailsDraft.businessState" :errors="errors.businessState" />
			<text-field id="businessZipCode" label="Zip Code" v-model="invoiceDetailsDraft.businessZipCode" :errors="errors.businessZipCode" />
			<text-field id="businessCountry" label="Country" v-model="invoiceDetailsDraft.businessCountry" :errors="errors.businessCountry" />

			<input type="submit" class="btn btn-primary" value="Save" />
			<input type="button" class="btn btn-secondary" value="Cancel" @click="cancel()" />
		</form>

	</div>
</template>

<script>
    import { mapGetters } from 'vuex'
    import TextField from './fields/TextField'

    export default {

        components: {
            TextField,
        },

        data() {
            return {
                errors: {},
                userId: 1,
                showForm: false,
                invoiceDetailsDraft: {},
            }
        },

		computed: {

            ...mapGetters({
                currentUser: 'currentUser',
            }),

            companyInfos() {
				return {
					businessName: this.currentUser.businessName,
					businessVatId: this.currentUser.businessVatId,
					businessAddressLine1: this.currentUser.businessAddressLine1,
					businessAddressLine2: this.currentUser.businessAddressLine2,
					businessCity: this.currentUser.businessCity,
					businessState: this.currentUser.businessState,
					businessZipCode: this.currentUser.businessZipCode,
					businessCountry: this.currentUser.businessCountry,
				};
			},

		},

        methods: {

            editInvoiceDetails() {
                this.showForm = true;
                this.invoiceDetailsDraft = JSON.parse(JSON.stringify(this.companyInfos));
            },

            save() {
                this.$store.dispatch('saveUser', {
                    id: this.currentUser.id,
					businessName: this.invoiceDetailsDraft.businessName,
					businessVatId: this.invoiceDetailsDraft.businessVatId,
					businessAddressLine1: this.invoiceDetailsDraft.businessAddressLine1,
					businessAddressLine2: this.invoiceDetailsDraft.businessAddressLine2,
					businessCity: this.invoiceDetailsDraft.businessCity,
					businessState: this.invoiceDetailsDraft.businessState,
					businessZipCode: this.invoiceDetailsDraft.businessZipCode,
					businessCountry: this.invoiceDetailsDraft.businessCountry,
                }).then((data) => {
                    this.$root.displayNotice('Company infos saved.');
                    this.showForm = false;
                    this.errors = {};
                }).catch((data) => {
                    this.$root.displayError('Couldn’t save company infos.');
                    this.errors = data.errors;
                });
            },

            cancel() {
                this.showForm = false;
                this.errors = {};
            }

        }

    }
</script>

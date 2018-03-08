<template>
	<div>
		<div v-if="!showForm" class="flex">
			<div class="flex-1">
				<h4>Invoice details</h4>

				<pre>{{ companyInfos.businessVatId }}</pre>
			</div>

			<div>
				<button @click="editInvoiceDetails()" type="button" class="btn btn-secondary btn-sm" data-facebox="#billing-contact-info-modal">
					<i class="fas fa-pencil-alt"></i>
					Edit
				</button>
			</div>
		</div>

		<form v-if="showForm" @submit.prevent="save()">
			<text-field id="businessVatId" label="Vat ID" v-model="invoiceDetailsDraft.businessVatId" :errors="errors.businessVatId" />

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

            /**
             * Edit invoice details.
             */
            editInvoiceDetails() {
                this.showForm = true;
                this.invoiceDetailsDraft = JSON.parse(JSON.stringify(this.companyInfos));
            },

            /**
			 * Saves the user’s invoice details.
             */
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
                }).then(response => {
                    this.$root.displayNotice('Company infos saved.');
                    this.showForm = false;
                    this.errors = {};
                }).catch(response => {
                    const errorMessage = response.data && response.data.error ? response.data.error : 'Couldn’t save company infos.';
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
            }

        }

    }
</script>

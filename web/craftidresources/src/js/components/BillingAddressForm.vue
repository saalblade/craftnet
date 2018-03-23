<template>
	<div>
		<div class="flex">
			<div class="flex-1">
				<h4>Billing Address</h4>

				<template v-if="!showForm">
					<ul v-if="companyInfos.businessAddressLine1 || companyInfos.businessAddressLine2 || companyInfos.businessCity || companyInfos.businessCountry || companyInfos.businessName || companyInfos.businessState || companyInfos.businessZipCode" class="list-reset">
						<li v-if="companyInfos.businessName">
							<strong>
								<template v-if="companyInfos.businessName">{{ companyInfos.businessName }}</template>
							</strong>
						</li>
						<li v-if="companyInfos.businessAddressLine1">{{ companyInfos.businessAddressLine1 }}</li>
						<li v-if="companyInfos.businessAddressLine2">{{ companyInfos.businessAddressLine2 }}</li>
						<li v-if="companyInfos.businessZipCode || companyInfos.businessCity">
							<template v-if="companyInfos.businessZipCode">{{ companyInfos.businessZipCode }}</template>
							<template v-if="companyInfos.businessCity">{{ companyInfos.businessCity }}</template>
						</li>
						<li v-if="companyInfos.businessCountry">{{ companyInfos.businessCountry}}</li>
						<li v-if="companyInfos.businessState">{{ companyInfos.businessState }}</li>
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
			<text-field id="businessName" label="Name" v-model="invoiceDetailsDraft.businessName" :errors="errors.businessName" />
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
    import {mapGetters} from 'vuex'
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
             * Edit billing address.
             */
            edit() {
                this.showForm = true;
                this.invoiceDetailsDraft = JSON.parse(JSON.stringify(this.companyInfos));
            },

            /**
             * Save the billing address.
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
            },
        }

    }
</script>

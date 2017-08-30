<template>
	<div>
		<h4>Business informations</h4>

		<div v-if="!showForm" class="row">
			<div class="col-sm-8">

				<div class="flex-auto">
					<div class="billing-heres-what-appears">Here’s what currently appears on your receipts:</div>

					<div class="billing-businessName">
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
			</div>
			<div class="col-sm-4 text-right">
				<button @click="editInfos()" type="button" class="btn btn-secondary btn-sm" data-facebox="#billing-contact-info-modal">
					<i class="fa fa-pencil"></i>
					Change information
				</button>
			</div>
		</div>

		<form v-if="showForm" @submit.prevent="save()">
			<text-field id="businessName" label="Name" v-model="companyInfosDraft.businessName" :errors="errors.businessName" />
			<text-field id="businessVatId" label="Vat ID" v-model="companyInfosDraft.businessVatId" :errors="errors.businessVatId" />
			<text-field id="businessAddressLine1" label="Address Line 1" v-model="companyInfosDraft.businessAddressLine1" :errors="errors.businessAddressLine1" />
			<text-field id="businessAddressLine2" label="Address Line 2" v-model="companyInfosDraft.businessAddressLine2" :errors="errors.businessAddressLine2" />
			<text-field id="businessCity" label="City" v-model="companyInfosDraft.businessCity" :errors="errors.businessCity" />
			<text-field id="businessState" label="State" v-model="companyInfosDraft.businessState" :errors="errors.businessState" />
			<text-field id="businessZipCode" label="Zip Code" v-model="companyInfosDraft.businessZipCode" :errors="errors.businessZipCode" />
			<text-field id="businessCountry" label="Country" v-model="companyInfosDraft.businessCountry" :errors="errors.businessCountry" />

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
                companyInfosDraft: {},
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
            editInfos() {
                this.showForm = true;
                this.companyInfosDraft = JSON.parse(JSON.stringify(this.companyInfos));
            },

            save() {
                this.$store.dispatch('saveUser', {
                    id: this.currentUser.id,
					businessName: this.companyInfosDraft.businessName,
					businessVatId: this.companyInfosDraft.businessVatId,
					businessAddressLine1: this.companyInfosDraft.businessAddressLine1,
					businessAddressLine2: this.companyInfosDraft.businessAddressLine2,
					businessCity: this.companyInfosDraft.businessCity,
					businessState: this.companyInfosDraft.businessState,
					businessZipCode: this.companyInfosDraft.businessZipCode,
					businessCountry: this.companyInfosDraft.businessCountry,
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

<template>
    <div>
        <div class="flex">
            <div class="flex-1">
                <h4>Invoice details</h4>

                <dl v-if="!showForm">
                    <dt>VAT ID</dt>
                    <dd>
                        <template v-if="billingAddress && billingAddress.businessTaxId">
                            {{ billingAddress.businessTaxId }}
                        </template>
                        <template v-else>
                            <span class="text-secondary">VAT ID not defined.</span>
                        </template>
                    </dd>
                </dl>
            </div>

            <div v-if="!showForm">
                <button @click="editInvoiceDetails()" type="button"
                        class="btn btn-secondary btn-sm"
                        data-facebox="#billing-contact-info-modal">
                    <font-awesome-icon icon="pencil-alt" />
                    Edit
                </button>
            </div>
        </div>

        <form v-if="showForm" @submit.prevent="save()">
            <text-field id="businessTaxId" label="Tax ID" v-model="invoiceDetailsDraft.businessTaxId" :errors="errors.businessTaxId" />
            <input type="submit" class="btn btn-primary" value="Save" />
            <input type="button" class="btn btn-secondary" value="Cancel" @click="cancel()" />
        </form>
    </div>
</template>

<script>
    import {mapState} from 'vuex'
    import CraftComponents from "../../../../../../../../repos/craftcomponents/src/components/index";

    export default {

        components: {
            ...CraftComponents,
        },

        data() {
            return {
                errors: {},
                showForm: false,
                invoiceDetailsDraft: {},
            }
        },

        computed: {

            ...mapState({
                currentUser: state => state.account.currentUser,
                billingAddress: state => state.account.billingAddress,
            }),

        },

        methods: {

            /**
             * Edit invoice details.
             */
            editInvoiceDetails() {
                this.showForm = true;

                if (this.billingAddress) {
                    this.invoiceDetailsDraft = JSON.parse(JSON.stringify(this.billingAddress));
                }
            },

            /**
             * Saves the user’s invoice details.
             */
            save() {
                let data = {
                    businessTaxId: this.invoiceDetailsDraft.businessTaxId,
                }

                if(this.billingAddress) {
                    data = Object.assign({}, data, {
                        id: this.billingAddress.id,
                        firstName: this.billingAddress.firstName,
                        lastName: this.billingAddress.lastName,
                        businessName: this.billingAddress.businessName,
                        address1: this.billingAddress.address1,
                        address2: this.billingAddress.address2,
                        city: this.billingAddress.city,
                        state: this.billingAddress.state,
                        zipCode: this.billingAddress.zipCode,
                        country: this.billingAddress.country,
                    });
                }

                this.$store.dispatch('account/saveBillingInfo', data)
                    .then(response => {
                        if (response.data.error) {
                            const errorMessage = response.data.error
                            this.$store.dispatch('app/displayError', errorMessage)
                        } else {
                            this.$store.dispatch('app/displayNotice', 'Invoice details saved.');
                            this.showForm = false;
                            this.errors = {};
                        }
                    }).catch(response => {
                        const errorMessage = response.data && response.data.error ? response.data.error : 'Couldn’t save invoice details.';
                        this.$store.dispatch('app/displayError', errorMessage);
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

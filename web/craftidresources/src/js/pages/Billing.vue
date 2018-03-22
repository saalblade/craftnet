<template>
    <div>
        <h1>Billing</h1>

        <div class="card mb-3">
            <div class="card-body">
                <billing-payment></billing-payment>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <billing-address-form></billing-address-form>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <billing-invoice-details></billing-invoice-details>
            </div>
        </div>

        <div v-if="enableCommercialFeatures" class="card mb-3">
            <div class="card-body">
                <h4>Upcoming Invoice</h4>

                <invoices-table :invoices="[upcomingInvoice]" :upcoming="true"></invoices-table>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <h4>Invoices</h4>

                <div v-if="$root.invoicesLoading" class="spinner"></div>

                <template v-else>
                    <invoices-table v-if="invoices && invoices.length > 0" :invoices="invoices"></invoices-table>
                    <p v-else class="text-secondary">No invoices.</p>
                </template>
            </div>
        </div>
    </div>
</template>

<script>
    import {mapGetters} from 'vuex'
    import BillingPayment from '../components/BillingPayment'
    import BillingInvoiceDetails from '../components/BillingInvoiceDetails'
    import BillingAddressForm from '../components/BillingAddressForm'
    import InvoicesTable from '../components/InvoicesTable'


    export default {

        components: {
            BillingPayment,
            BillingInvoiceDetails,
            BillingAddressForm,
            InvoicesTable,
        },

        computed: {

            ...mapGetters({
                enableCommercialFeatures: 'enableCommercialFeatures',
                invoices: 'invoices',
                upcomingInvoice: 'upcomingInvoice',
            }),

        },

    }
</script>

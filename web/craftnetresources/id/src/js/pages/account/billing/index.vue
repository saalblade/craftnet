<template>
    <div>
        <h1>Billing</h1>

        <div class="card mb-6">
            <div class="card-body">
                <billing-payment></billing-payment>
            </div>
        </div>

        <div class="card mb-6">
            <div class="card-body">
                <billing-address-form></billing-address-form>
            </div>
        </div>

        <div class="card mb-6">
            <div class="card-body">
                <billing-invoice-details></billing-invoice-details>
            </div>
        </div>

        <div class="card mb-6">
            <div class="card-body">
                <h4>Upcoming Invoice</h4>

                <invoices-table :invoices="[]" :upcoming="true"></invoices-table>
            </div>
        </div>

        <div class="card mb-6">
            <div class="card-body">
                <h4>Invoices</h4>

                <spinner v-if="invoicesLoading"></spinner>

                <template v-else>
                    <invoices-table v-if="invoices && invoices.length > 0" :invoices="invoices"></invoices-table>
                    <p v-else class="text-secondary">No invoices.</p>
                </template>
            </div>
        </div>
    </div>
</template>

<script>
    import {mapState} from 'vuex'
    import BillingPayment from '../../../components/billing/BillingPayment'
    import BillingInvoiceDetails from '../../../components/billing/BillingInvoiceDetails'
    import BillingAddressForm from '../../../components/billing/BillingAddressForm'
    import InvoicesTable from '../../../components/billing/InvoicesTable'
    import Spinner from '../../../components/Spinner'


    export default {

        components: {
            BillingPayment,
            BillingInvoiceDetails,
            BillingAddressForm,
            InvoicesTable,
            Spinner,
        },

        computed: {

            ...mapState({
                invoices: state => state.account.invoices,
                upcomingInvoice: state => state.account.upcomingInvoice,
                invoicesLoading: state => state.account.invoicesLoading,
            }),

        },

    }
</script>

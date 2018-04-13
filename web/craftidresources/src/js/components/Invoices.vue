<template>
    <div>
        <div v-if="enableRenewalFeatures" class="card mb-3">
            <div class="card-header">Upcoming Invoice</div>
            <div class="card-body">
                <invoices-table :invoices="[upcomingInvoice]" :upcoming="true"></invoices-table>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">Invoices</div>
            <div class="card-body">
                <invoices-table :invoices="invoices"></invoices-table>
            </div>
        </div>

    </div>
</template>

<script>
    import {mapState, mapGetters} from 'vuex'
    import InvoicesTable from './InvoicesTable';

    export default {

        components: {
            InvoicesTable
        },

        computed: {

            ...mapState({
                invoices: state => state.account.invoices,
                upcomingInvoice: state => state.account.upcomingInvoice,
            }),

            ...mapGetters({
                enableRenewalFeatures: 'enableRenewalFeatures',
            }),

        },

    }
</script>

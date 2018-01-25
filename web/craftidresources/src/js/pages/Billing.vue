<template>
    <div>
        <div class="card mb-3">
            <div class="card-body">
                <billing-payment></billing-payment>
            </div>
        </div>
        <div class="card mb-3">
            <div class="card-body">
                <billing-infos></billing-infos>
            </div>
        </div>
        <div class="card mb-3">
            <div class="card-body">
                <billing-coupon></billing-coupon>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <h4>Upcoming Invoice</h4>

                <invoices-table :invoices="[upcomingInvoice]" :upcoming="true"></invoices-table>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <h4>Invoices</h4>

                <invoices-table :invoices="invoices"></invoices-table>
            </div>
        </div>
    </div>
</template>

<script>
    import { mapGetters } from 'vuex'
    import BillingPayment from '../components/BillingPayment'
    import BillingCoupon from '../components/BillingCoupon'
    import BillingInfos from '../components/BillingInfos'
    import InvoicesTable from '../components/InvoicesTable'


    export default {

        components: {
            BillingPayment,
            BillingCoupon,
            BillingInfos,
            InvoicesTable,
        },

        computed: {

            ...mapGetters({
                upcomingInvoice: 'upcomingInvoice',
                invoices: 'invoices',
            }),

        },

    }
</script>

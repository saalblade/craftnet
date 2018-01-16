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
                <h4>Invoices</h4>
                <table class="table">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Date</th>
                        <th>Payment Method</th>
                        <th>Amount</th>
                        <th>Receipt</th>
                    </tr>
                    </thead>
                    <tbody>
                        <tr v-for="invoice in invoices">
                            <td>
                                <router-link :to="'/account/billing/invoices/'+invoice.id">INV000{{ invoice.id }}</router-link>
                            </td>
                            <td>{{ invoice.date }}</td>
                            <td>{{ invoice.paymentMethod.type }} ending with {{ invoice.paymentMethod.last4 }}</td>
                            <td>{{ invoice.total|currency }}</td>
                            <td><a href="#">Receipt</a></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script>
    import { mapGetters } from 'vuex'
    import BillingPayment from '../components/BillingPayment'
    import BillingCoupon from '../components/BillingCoupon'
    import BillingInfos from '../components/BillingInfos'

    export default {

        components: {
            BillingPayment,
            BillingCoupon,
            BillingInfos,
        },

        computed: {

            ...mapGetters({
                invoices: 'invoices',
            }),

        },

    }
</script>

<template>
    <div>
        <spinner v-if="invoicesLoading"></spinner>

        <template v-else>
            <p><router-link class="nav-link" to="/account/billing" exact>← Billing</router-link></p>
            <h1>Invoice {{ invoice.shortNumber }}</h1>

            <div class="card mb-4">
                <div class="card-body">
                    <dl>
                        <dt>Invoice Number</dt>
                        <dd>{{ invoice.number }}</dd>

                        <dt>Date Paid</dt>
                        <dd>{{ invoice.datePaid.date|moment("LLL") }}</dd>
                    </dl>

                    <billing-address :address="invoice.billingAddress" class="mb-4"></billing-address>

                    <table class="table">
                        <thead>
                        <tr>
                            <th>Item</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                            <tr v-for="lineItem in invoice.lineItems">
                                <td>{{ lineItem.description }}</td>
                                <td>{{ lineItem.salePrice|currency }}</td>
                                <td>{{ lineItem.qty }}</td>
                                <td class="text-right">{{ lineItem.subtotal|currency }}</td>
                            </tr>
                            <tr v-for="adjustment in invoice.adjustments">
                                <th colspan="3" class="text-right">{{ adjustment.name }}</th>
                                <td class="text-right">{{ adjustment.amount|currency }}</td>
                            </tr>
                            <tr>
                                <th colspan="3" class="text-right">Items Price</th>
                                <td class="text-right">{{ invoice.itemTotal|currency }}</td>
                            </tr>
                            <tr>
                                <th colspan="3" class="text-right">Total Price</th>
                                <td class="text-right">{{ invoice.totalPrice|currency }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-body">
                    <h3 class="mb-2">Transactions</h3>

                    <table class="table">
                        <thead>
                        <tr>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Amount</th>
                            <th>Payment Amount</th>
                            <th>Method</th>
                            <th>Date</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="transaction in invoice.transactions">
                            <td>{{ transaction.type }}</td>
                            <td>{{ transaction.status }}</td>
                            <td>{{ transaction.amount|currency }}</td>
                            <td>{{ transaction.paymentAmount|currency }}</td>
                            <td>{{ transaction.gatewayName }}</td>
                            <td>{{ transaction.dateCreated.date|moment("LLL") }}</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div v-if="invoice.cmsLicenses.length" class="card mb-4">
                <div class="card-body">
                    <h3 class="mb-2">CMS Licenses</h3>
                    <cms-licenses-table :licenses="invoice.cmsLicenses"></cms-licenses-table>
                </div>
            </div>

            <div v-if="invoice.pluginLicenses.length" class="card mb-4">
                <div class="card-body">
                    <h3 class="mb-2">Plugin Licenses</h3>
                    <plugin-licenses-table :licenses="invoice.pluginLicenses"></plugin-licenses-table>
                </div>
            </div>
        </template>
    </div>
</template>

<script>
    import {mapState, mapGetters} from 'vuex'
    import BillingAddress from '../../../../components/billing/BillingAddress'
    import CardIcon from '../../../../components/card/CardIcon'
    import CmsLicensesTable from '../../../../components/licenses/CmsLicensesTable'
    import PluginLicensesTable from '../../../../components/licenses/PluginLicensesTable'
    import Spinner from '../../../../components/Spinner'

    export default {

        components: {
            BillingAddress,
            CardIcon,
            CmsLicensesTable,
            PluginLicensesTable,
            Spinner,
        },

        computed: {

            ...mapState({
                invoicesLoading: state => state.app.invoicesLoading,
            }),

            ...mapGetters({
                getInvoiceByNumber: 'account/getInvoiceByNumber',
            }),

            invoice() {
                const invoice = this.getInvoiceByNumber(this.$route.params.number)

                if(!invoice) {
                    this.$store.dispatch('app/displayError', "Couldn’t find invoice.")
                    this.$router.push({path: '/account/billing'})
                }

                return invoice;
            },

        }

    }
</script>

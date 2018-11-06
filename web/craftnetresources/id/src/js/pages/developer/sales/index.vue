<template>
    <div>
        <stripe-account-alert></stripe-account-alert>

        <h1>Sales</h1>

        <template v-if="salesToRender.length > 0">
            <div class="field mb-6">
                <text-input class="form-control" id="searchQuery" name="searchQuery" type="text" placeholder="Search sales" v-model="searchQuery" />
            </div>

            <div class="card card-table responsive-content">
                <table class="table">
                    <thead>
                    <tr>
                        <th>Item</th>
                        <th>Customer</th>
                        <th>Type</th>
                        <th>Gross Amount</th>
                        <th>Net Amount</th>
                        <th>Date</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="sale in salesToRender">
                        <td>{{ sale.plugin.name }}</td>
                        <td><a :href="'mailto:'+sale.customer.email">{{ sale.customer.email }}</a></td>
                        <td class="text-secondary">License Purchase</td>
                        <td>{{ sale.grossAmount|currency }}</td>
                        <td>{{ sale.netAmount|currency }}</td>
                        <td class="date-col">{{ sale.saleTime|moment("LLL") }}</td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </template>
        <template v-else>
            <div class="empty">
                <div class="empty-body">
                    <font-awesome-icon icon="dollar-sign" class="text-5xl mb-4 text-grey-light" />
                    <div class="font-bold">No sales</div>
                    <div>You don’t have any sales yet.</div>
                </div>
            </div>
        </template>
    </div>
</template>

<script>
    import {mapState} from 'vuex'
    import CraftComponents from "@benjamindavid/craftcomponents";
    import StripeAccountAlert from '../../../components/developer/StripeAccountAlert'

    export default {

        components: {
            ...CraftComponents,
            StripeAccountAlert
        },

        data() {
            return {
                searchQuery: '',
            }
        },

        computed: {

            ...mapState({
                sales: state => state.developers.sales,
            }),

            salesToRender() {
                let searchQuery = this.searchQuery;
                return this.sales.filter(function(sale) {
                    if (sale) {
                        let searchQueryRegExp = new RegExp(searchQuery, 'gi');

                        if (sale.customer.name.match(searchQueryRegExp)) {
                            return true;
                        }

                        if (sale.customer.email.match(searchQueryRegExp)) {
                            return true;
                        }
                    }
                });
            },
        },

    }
</script>

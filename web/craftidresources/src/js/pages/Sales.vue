<template>
    <div>
        <h1>Sales</h1>

        <stripe-account-alert></stripe-account-alert>

        <div class="card">
            <div class="card-body">
                <template v-if="salesToRender.length > 0">
                    <div class="form-group">
                        <input class="form-control" id="searchQuery" name="searchQuery" type="text" placeholder="Search sales" v-model="searchQuery">
                    </div>

                    <div class="responsive-content">
                        <table class="table">
                            <thead>
                            <tr>
                                <th>Item</th>
                                <th>Type</th>
                                <th>Customer</th>
                                <th>Gross Amount</th>
                                <th>Net Amount</th>
                                <th>Date</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="sale in salesToRender">
                                <td>{{ sale.plugin.name }}</td>
                                <td class="text-secondary">{{ sale.type }}</td>
                                <td><a :href="'mailto:'+sale.customer.email">{{ sale.customer.email }}</a></td>
                                <td>{{ sale.grossAmount|currency }}</td>
                                <td>{{ sale.netAmount|currency }}</td>
                                <td>{{ sale.saleTime|moment("LLL") }}</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </template>
                <template v-else>
                    <p>No sales yet.</p>
                </template>
            </div>
        </div>
    </div>
</template>

<script>
    import {mapState} from 'vuex'
    import StripeAccountAlert from '../components/StripeAccountAlert'

    export default {

        components: {
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

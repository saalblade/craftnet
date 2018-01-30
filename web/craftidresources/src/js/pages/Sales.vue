<template>
    <div>
        <h1>Sales</h1>

        <bank-account-alert></bank-account-alert>

        <div class="card">
            <div class="card-body">
                <div class="form-group">
                    <input class="form-control" id="searchQuery" name="searchQuery" type="text" placeholder="Search sales" v-model="searchQuery">
                </div>

                <div class="responsive-content">
                    <table class="table">
                        <thead>
                        <tr>
                            <th>ID</th>
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
                            <td><router-link :to="'/developer/sales/'+sale.id">SAL000{{ sale.id }}</router-link></td>
                            <td>{{ sale.plugin.name }}</td>
                            <td class="text-secondary">{{ sale.type }}</td>
                            <td><a :href="'mailto:'+sale.customer.email">{{ sale.customer.email }}</a></td>
                            <td>{{ sale.grossAmount|currency }}</td>
                            <td>{{ sale.netAmount|currency }}</td>
                            <td>{{ sale.date }}</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import { mapGetters } from 'vuex'
    import BankAccountAlert from '../components/BankAccountAlert'

    export default {

        components: {
            BankAccountAlert
        },

        data () {
            return {
                searchQuery: '',
            }
        },

        computed: {

            ...mapGetters({
                sales: 'sales',
            }),

            salesToRender() {
                let searchQuery = this.searchQuery;
                return this.sales.filter(function(sale) {
                    if(sale) {
                        let searchQueryRegExp = new RegExp(searchQuery, 'gi');

                        if(sale.customer.name.match(searchQueryRegExp)) {
                            return true;
                        }

                        if(sale.customer.email.match(searchQueryRegExp)) {
                            return true;
                        }
                    }
                });
            },
        },

    }
</script>

<template>
    <div v-if="customer">
        <p><router-link class="nav-link" to="/developer/customers" exact>← Back to customers</router-link></p>

        <div class="card mb-3">
            <div class="card-body">

                <h3>{{ customer.email }}</h3>
                <p class="text-secondary">#CUS000{{customerId}}</p>

                <hr>

                <dl>
                    <dt>ID</dt>
                    <dd>{{customerId}}</dd>
                    <dt>Email</dt>
                    <dd>{{customer.email}}</dd>
                    <dt>Full name</dt>
                    <dd>{{customer.fullName}}</dd>
                </dl>

            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">Sales</div>
            <div class="card-body">

                <table class="table">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Item</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Date</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="sale in customerSales">
                        <td><router-link :to="'/developer/sales/'+sale.id">SAL000{{ sale.id }}</router-link></td>
                        <td>{{ sale.plugin.name }}</td>
                        <td class="text-secondary">{{ sale.type }}</td>
                        <td>{{sale.amount|currency}}</td>
                        <td>{{ sale.date }}</td>
                    </tr>
                    </tbody>
                </table>

            </div>
        </div>
    </div>
</template>

<script>
    import { mapGetters } from 'vuex'

    export default {

        computed: {

            ...mapGetters({
                customers: 'customers',
                sales: 'sales',
            }),

            customerId() {
                return this.$route.params.id;
            },

            customer() {
                return this.customers.find(c => c.id == this.customerId);
            },

            customerSales() {
                if(this.sales) {
                    return this.sales.filter(p => p.customer.id == this.customerId);
                }
            }

        }
    }
</script>

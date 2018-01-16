<template>
    <div>
        <table class="table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Item</th>
                <th>Type</th>
                <th>Customer</th>
                <th>Amount</th>
                <th>Date</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="sale in sales">
                <td><router-link :to="'/developer/sales/'+sale.id">SAL000{{ sale.id }}</router-link></td>
                <td>{{ sale.plugin.name }}</td>
                <td class="text-secondary">{{ sale.type }}</td>
                <td><router-link :to="'/developer/customers/'+sale.customer.id">{{sale.customer.email}}</router-link></td>
                <td>{{sale.amount|currency}}</td>
                <td>{{ sale.date }}</td>
            </tr>
            </tbody>
        </table>
    </div>
</template>

<script>
    import { mapGetters } from 'vuex'

    export default {

        computed: {

            ...mapGetters({
                sales: 'sales',
            }),

        },

    }
</script>

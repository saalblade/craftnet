<template>
    <div>
        <table class="table">
            <thead>
            <tr>
                <th>Amount</th>
                <th>Items</th>
                <th>Customer</th>
                <th>Date</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="(payment, key) in payments">
                <td><router-link :to="'/payments/'+key">{{payment.amount|currency}}</router-link></td>
                <td>{{ payment.items.length }}</td>
                <td><router-link :to="'/customers/'+payment.customer.id">{{payment.customer.email}}</router-link></td>
                <td>{{ payment.date }}</td>
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
                payments: 'payments',
            }),
        },
    }
</script>

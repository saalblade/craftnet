<template>
    <table class="table">
        <thead>
        <tr>
            <th>Invoice</th>
            <th>Amount</th>
            <th>Date</th>
            <th v-if="!upcoming">Receipt</th>
        </tr>
        </thead>
        <tbody>
        <tr v-for="invoice in invoices">
            <td v-if="!upcoming"><router-link :to="'/account/billing/invoices/'+invoice.id">INV000{{ invoice.id }}</router-link></td>
            <td v-else>INV000{{ invoice.id }}</td>
            <td>{{ invoice.total|currency }}</td>
            <td>{{ invoice.date }}</td>
            <td v-if="!upcoming"><a href="#">Receipt</a></td>
        </tr>
        </tbody>
    </table>
</template>

<script>
    export default {

        props: ['upcoming', 'invoices'],

    }
</script>

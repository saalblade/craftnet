<template>
    <div class="responsive-content">
        <table class="table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Price</th>
                <th>Date</th>
                <th v-if="!upcoming">Receipt</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="invoice in invoices">
                <td v-if="!upcoming"><router-link :to="'/account/billing/invoices/' + invoice.number">{{ invoice.shortNumber }}</router-link></td>
                <td v-else>—</td>
                <td>{{ invoice.totalPrice|currency }}</td>
                <td><template v-if="invoice.datePaid">{{ invoice.datePaid.date|moment("L") }}</template></td>
                <td v-if="!upcoming"><a href="#">Receipt</a></td>
            </tr>
            </tbody>
        </table>
    </div>
</template>

<script>
    export default {

        props: ['upcoming', 'invoices'],

    }
</script>

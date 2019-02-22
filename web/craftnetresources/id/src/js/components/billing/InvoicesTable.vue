<template>
    <div class="responsive-content">
        <table class="table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Price</th>
                <th>Date</th>
                <th>Receipt</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="(invoice, key) in invoices" :key="key">
                <td><router-link :to="'/account/billing/invoices/' + invoice.number">{{ invoice.shortNumber }}</router-link></td>
                <td>{{ invoice.totalPrice|currency }}</td>
                <td><template v-if="invoice.datePaid">{{ invoice.datePaid.date|moment("L") }}</template></td>
                <td><a :href="invoice.pdfUrl">Download Receipt</a></td>
            </tr>
            </tbody>
        </table>
    </div>
</template>

<script>
    export default {
        props: ['invoices'],
    }
</script>

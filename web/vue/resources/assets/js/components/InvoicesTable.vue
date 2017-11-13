<template>
    <table class="table">
        <thead>
        <tr>
            <th>Invoice</th>
            <th>Amount</th>
            <th>Date</th>
        </tr>
        </thead>
        <tbody>
            <tr v-for="invoice in invoices">
                <td>#00000{{ invoice.id }}</td>
                <td>{{ invoice.amount|currency }}</td>
                <td>{{ invoice.date }}</td>
            </tr>
        </tbody>
    </table>
</template>

<script>
    export default {

        props: ['upcoming'],

        computed: {

            invoices() {
                if(this.upcoming) {
                    return [
                        {id: 2, email: 'ben@pixelandtonic.com', amount: 299.00, date: '2018/05/11 11:25'}
                    ]
                }

                return [
                    {id: 1, email: 'ben@pixelandtonic.com', amount: 299.00, date: '2017/05/11 11:25'}
                ]
            }

        }

    }
</script>

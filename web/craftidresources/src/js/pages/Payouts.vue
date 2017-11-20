<template>
    <div>
        <payouts-bank-account></payouts-bank-account>

        <div class="card mb-3">
            <div class="card-header">Scheduled deposits</div>
            <div class="card-body">
                <table class="table">
                    <thead>
                    <tr>
                        <th>Amount</th>
                        <th>Date</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="payout in payoutsScheduled">
                        <td>{{ payout.amount|currency }}</td>
                        <td>{{ payout.date }}</td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card mb-3">
            <div class="card-header">Past deposits</div>
            <div class="card-body">
                <table class="table">
                    <thead>
                    <tr>
                        <th>Amount</th>
                        <th>Date</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="payout in payouts">
                        <td><router-link :to="'/developer/payouts/'+payout.id">{{ payout.amount|currency }}</router-link></td>
                        <td>{{ payout.date }}</td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script>
    import { mapGetters } from 'vuex'
    import TextField from '../components/fields/TextField'
    import PayoutsBankAccount from '../components/PayoutsBankAccount'

    export default {

        components: {
            TextField,
            PayoutsBankAccount
        },

        computed: {

            ...mapGetters({
                payouts: 'payouts',
                payoutsScheduled: 'payoutsScheduled',
            }),

        },

    }
</script>

<template>
    <div v-if="payout">
        <div class="card mb-3">
            <div class="card-body">

                <h3>{{ payout.amount|currency }}</h3>
                <p class="text-secondary">#00000{{payoutId}}</p>

                <hr>

                <dl>
                    <dt>Date Paid</dt>
                    <dd>{{payout.date}}</dd>

                    <dt>Amount</dt>
                    <dd>{{payout.amount|currency}}</dd>

                    <dt>ID</dt>
                    <dd>#00000{{payoutId}}</dd>

                </dl>
            </div>
        </div>
        <div class="card mb-3">
            <div class="card-header"><i class="fa fa-institution"></i> Bank account details</div>
            <div class="card-body">
                <dl>
                    <dt>Bank Name</dt>
                    <dd>{{ payout.bank.name }}</dd>
                    <dt>Account Number</dt>
                    <dd>{{ payout.bank.accountNumber }}</dd>
                </dl>
            </div>
        </div>
    </div>
</template>

<script>
    import { mapGetters } from 'vuex'

    export default {

        computed: {

            ...mapGetters({
                payouts: 'payouts',
            }),

            payoutId() {
                return this.$route.params.id;
            },

            payout() {
                return this.payouts[this.payoutId];
            }

        }

    }
</script>

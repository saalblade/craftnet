<template>
    <div v-if="payout">
        <p><router-link class="nav-link" to="/developer/payouts" exact>← Back to payouts</router-link></p>

        <div class="card mb-3">
            <div class="card-body">
                <h3>{{ payout.total|currency }}</h3>
                <p class="text-secondary">#PAY000{{payoutId}}</p>

                <hr>

                <div class="row">
                    <div class="col-6">
                        <dl>
                            <dt>Sales</dt>
                            <dd>{{ payout.sales|currency }}</dd>
                            <dt>Fees</dt>
                            <dd>{{ payout.fees|currency }}</dd>
                            <dt>Total</dt>
                            <dd>{{ payout.total|currency }}</dd>
                        </dl>
                    </div>
                    <div class="col-6">
                        <dl>
                            <dt>Date</dt>
                            <dd>{{ payout.date }}</dd>
                            <dt>ID</dt>
                            <dd>#PAY000{{ payout.id }}</dd>
                        </dl>
                    </div>
                </div>
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

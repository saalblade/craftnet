<template>
    <div>
        <h4>Payment Method</h4>

        <div v-if="stripeCustomerLoading" class="spinner"></div>

        <div v-if="!stripeCustomerLoading">


            <div v-if="!editing">

                <button @click="editing = true" type="button" class="float-right btn btn-secondary btn-sm" data-facebox="#billing-contact-info-modal">
                    <i class="fa fa-credit-card"></i>
                    Change Card
                </button>

                <div v-if="stripeCard">
                    {{ stripeCard.brand }} •••• •••• •••• {{ stripeCard.last4 }} — {{ stripeCard.exp_month }}/{{ stripeCard.exp_year }}
                </div>
            </div>


            <div :class="{'d-none': !editing}">

                <credit-card-form :loading="loading" @error="error" @beforeSave="beforeSave" @save="save" @cancel="cancel"></credit-card-form>

            </div>


            <div class="mt-3">
                <img src="/vue/dist/images/powered_by_stripe.svg" height="18" />
            </div>
        </div>
    </div>
</template>


<script>
    import { mapGetters } from 'vuex'
    import CreditCardForm from './CreditCardForm'

    export default {
        components: {
            CreditCardForm
        },

        data() {
            return {
                editing: false,
                loading: false,

                stripe: null,
                elements: null,
                card: null,
            }
        },

        computed: {
            ...mapGetters({
                stripeCustomer: 'stripeCustomer',
                stripeCard: 'stripeCard',
            }),

            stripeCustomerLoading() {
                return this.$root.stripeCustomerLoading;
            }
        },

        methods: {
            error() {
                this.loading = false;
            },

            beforeSave() {
                this.loading = true;
            },

            save(card, token) {
                this.$store.dispatch('saveCreditCard', token).then(response => {
                    card.clear();
                    this.loading = false;
                    this.editing = false;
                    this.$root.displayNotice('Credit card saved.');
                });
            },

            cancel() {
                this.editing = false;
            },
        },
    }
</script>
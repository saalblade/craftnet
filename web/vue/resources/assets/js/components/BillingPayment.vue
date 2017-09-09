<template>
    <div>
        <h4>Payment</h4>

        <div v-if="stripeCustomerLoading" class="spinner"></div>

        <div v-if="!stripeCustomerLoading">


            <div v-if="!editing">

                <div class="float-right">
                    <p>
                        <button @click="editing = true" type="button" class="btn btn-secondary btn-sm" data-facebox="#billing-contact-info-modal">
                            <i class="fa fa-plus"></i>
                            New Card
                        </button>
                    </p>

                    <p v-if="stripeCard">
                        <button @click="removeCard()" class="btn btn-sm btn-outline-danger">
                            <i class="fa fa-remove"></i>
                            Remove
                        </button>

                        <div v-if="removeCardLoading" class="spinner"></div>
                    </p>
                </div>


                <p v-if="stripeCard">
                    {{ stripeCard.brand }} •••• •••• •••• {{ stripeCard.last4 }} — {{ stripeCard.exp_month }}/{{ stripeCard.exp_year }}
                </p>

                <p v-else>No credit card.</p>

            </div>


            <div :class="{'d-none': !editing}">

                <credit-card-form :loading="cardFormloading" @error="error" @beforeSave="beforeSave" @save="save" @cancel="cancel"></credit-card-form>

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
                cardFormloading: false,
                removeCardLoading: false,

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
                this.cardFormloading = false;
            },

            beforeSave() {
                this.cardFormloading = true;
            },

            save(card, token) {
                this.$store.dispatch('saveCreditCard', token).then(response => {
                    card.clear();
                    this.cardFormloading = false;
                    this.editing = false;
                    this.$root.displayNotice('Credit card saved.');
                });
            },

            cancel() {
                this.editing = false;
            },

            removeCard() {
                this.removeCardLoading = true;
                this.$store.dispatch('removeCreditCard').then(response => {
                    this.removeCardLoading = false;
                    this.$root.displayNotice('Credit card removed.')
                })
            }
        },
    }
</script>
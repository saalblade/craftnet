<template>
    <div>
        <div class="flex">
            <div class="flex-1">
                <h4>Payment</h4>

                <div v-if="stripeCustomerLoading" class="spinner"></div>

                <div v-if="!stripeCustomerLoading">
                    <div v-if="!editing">
                        <div v-if="stripeCard" class="credit-card">
                            <card-icon :brand="stripeCard.brand"></card-icon>
                            <ul class="list-reset">
                                <li>Number: •••• •••• •••• {{ stripeCard.last4 }}</li>
                                <li>Expiry: {{ stripeCard.exp_month }}/{{ stripeCard.exp_year }}</li>
                            </ul>
                        </div>

                        <p v-else>No credit card.</p>
                    </div>

                    <div :class="{'hidden': !editing}">
                        <card-form :loading="cardFormloading" @error="error" @beforeSave="beforeSave" @save="saveCard" @cancel="cancel"></card-form>
                    </div>

                    <div class="mt-3">
                        <img src="/craftidresources/dist/images/powered_by_stripe.svg" height="18" />
                    </div>
                </div>
            </div>

            <div v-if="!editing" class="pl-4">
                <p>
                    <button @click="editing = true" type="button" class="btn btn-secondary btn-sm" data-facebox="#billing-contact-info-modal">
                        <i class="fa fa-plus"></i>
                        New Card
                    </button>
                </p>

                <p v-if="stripeCard">
                    <button @click="removeCard()" class="btn btn-sm btn-danger">
                        <i class="fas fa-times"></i>
                        Remove
                    </button>

                    <div v-if="removeCardLoading" class="spinner"></div>
                </p>
            </div>
        </div>
    </div>
</template>

<script>
    import { mapGetters } from 'vuex'
    import CardForm from './CardForm'
    import CardIcon from './CardIcon'

    export default {

        components: {
            CardForm,
            CardIcon,
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

            /**
             * Saves a credit card.
             *
             * @param card
             * @param source
             */
            saveCard(card, source) {
                this.$store.dispatch('saveCard', source).then(response => {
                    card.clear();
                    this.cardFormloading = false;
                    this.editing = false;
                    this.$root.displayNotice('Card saved.');
                });
            },

            /**
             * Removes a credit card.
             */
            removeCard() {
                this.removeCardLoading = true;
                this.$store.dispatch('removeCard').then(response => {
                    this.removeCardLoading = false;
                    this.$root.displayNotice('Card removed.')
                })
            },

            /**
             * Before save.
             */
            beforeSave() {
                this.cardFormloading = true;
            },

            /**
             * Cancel changes.
             */
            cancel() {
                this.editing = false;
            },

            /**
             * Error.
             */
            error() {
                this.cardFormloading = false;
            },

        },

    }
</script>
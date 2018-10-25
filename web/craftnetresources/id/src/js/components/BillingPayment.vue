<template>
    <div>
        <div class="flex">
            <div class="flex-1">
                <h4>Payment</h4>

                <div v-if="!editing">
                    <div v-if="card" class="credit-card">
                        <card-icon :brand="card.brand"></card-icon>
                        <ul class="list-reset">
                            <li>Number: •••• •••• •••• {{ card.last4 }}</li>
                            <li>Expiry: {{ card.exp_month }}/{{ card.exp_year }}</li>
                        </ul>
                    </div>

                    <p v-else class="text-secondary">Credit card not defined.</p>
                </div>

                <div :class="{'hidden': !editing}">
                    <card-form :loading="cardFormloading" @error="error" @beforeSave="beforeSave" @save="saveCard" @cancel="cancel"></card-form>
                </div>

                <div class="mt-3">
                    <img src="/craftnetresources/id/dist/images/powered_by_stripe.svg" height="18" />
                </div>
            </div>

            <div v-if="!editing" class="pl-4">
                <p>
                    <button @click="editing = true" type="button" class="btn btn-secondary btn-sm" data-facebox="#billing-contact-info-modal">
                        <font-awesome-icon icon="plus" />
                        New Card
                    </button>
                </p>

                <p v-if="card">
                    <button @click="removeCard()" class="btn btn-sm btn-danger">
                        <font-awesome-icon icon="times" />
                        Remove
                    </button>

                    <div v-if="removeCardLoading" class="spinner"></div>
                </p>
            </div>
        </div>
    </div>
</template>

<script>
    import {mapState} from 'vuex'
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
            }
        },

        computed: {

            ...mapState({
                card: state => state.account.card,
            }),

        },

        methods: {

            /**
             * Saves a credit card.
             *
             * @param card
             * @param source
             */
            saveCard(card, source) {
                this.$store.dispatch('account/saveCard', source).then(response => {
                    card.clear();
                    this.cardFormloading = false;
                    this.editing = false;
                    this.$store.dispatch('app/displayNotice', 'Card saved.');
                });
            },

            /**
             * Removes a credit card.
             */
            removeCard() {
                this.removeCardLoading = true;
                this.$store.dispatch('account/removeCard').then(response => {
                    this.removeCardLoading = false;
                    this.$store.dispatch('app/displayNotice', 'Card removed.')
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
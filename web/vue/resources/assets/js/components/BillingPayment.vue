<template>
	<div>
		<h4>Payment Method</h4>

		<div v-if="!showForm" class="row">
			<div class="col-sm-8">
				<p>
					<i class="fa fa-credit-card"></i>
					<strong>Visa</strong>
					<strong>{{ creditCard.cardNumber }}</strong>
					Expiration: <strong>{{ creditCard.cardExpiry }}</strong>
					<br>

					Next payment due: <strong>2017-05-24</strong>
					<br>
					Total Amount: <strong>$7.00</strong>
				</p>
			</div>
			<div class="col-sm-4 text-right">
				<button @click="editInfos()" type="button" class="btn btn-secondary btn-sm" data-facebox="#billing-contact-info-modal">
					<i class="fa fa-credit-card"></i>
					Update payment method
				</button>
			</div>
		</div>


		<form v-if="showForm" @submit.prevent="save()">
			<text-field id="cardNumber" label="Card Number" v-model="creditCardDraft.cardNumber" :errors="errors.cardNumber" />
			<text-field id="cardExpiry" label="Card Expiry" v-model="creditCardDraft.cardExpiry" :errors="errors.cardExpiry" />
			<text-field id="cardCvc" label="Card CVC" v-model="creditCardDraft.cardCvc" :errors="errors.cardCvc" />

			<input type="submit" class="btn btn-primary" value="Save" />
			<input type="button" class="btn btn-secondary" value="Cancel" @click="cancel()" />
		</form>

	</div>
</template>

<script>
    import { mapGetters } from 'vuex'
    import TextField from './fields/TextField'

    export default {
        components: {
            TextField,
		},

        data () {
            return {
                errors: {},
                userId: 1,
                showForm: false,
                creditCardDraft: {},
            }
        },

        computed: {
            ...mapGetters({
                currentUser: 'currentUser',
            }),

			creditCard() {
				return {
					cardNumber: this.currentUser.cardNumber,
					cardExpiry: this.currentUser.cardExpiry,
					cardCvc: this.currentUser.cardCvc,
				};
			}
        },

        methods: {
            editInfos: function() {
                this.showForm = true;
                this.creditCardDraft = JSON.parse(JSON.stringify(this.creditCard));
            },
            save: function() {
                this.$store.dispatch('saveUser', {
                    id: this.currentUser.id,
					cardNumber: this.creditCardDraft.cardNumber,
					cardExpiry: this.creditCardDraft.cardExpiry,
					cardCvc: this.creditCardDraft.cardCvc,
                }).then((data) => {
                    this.$root.displayNotice('Payment method saved.');
                    this.showForm = false;
                    this.errors = {};
                }).catch((data) => {
                    this.$root.displayError('Couldn’t save payment method.');
                    this.errors = data.errors;
                });
            },
			cancel() {
                this.showForm = false;
                this.errors = {};
			}
        },
    }
</script>

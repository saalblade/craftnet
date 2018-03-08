<template>
    <form @submit.prevent="save()">
        <div ref="cardElement" id="card-element" class="card-element form-control mb-3"></div>
        <p id="card-errors" class="text-red" role="alert"></p>

        <input type="submit" class="btn btn-primary" value="Save"></input>
        <button type="button" class="btn btn-secondary" @click="cancel()">Cancel</button>

        <div class="spinner" v-if="loading"></div>
    </form>
</template>


<script>
    export default {

        props: ['loading'],

        methods: {

            /**
             * Save the credit card.
             */
            save() {
                this.$emit('beforeSave');

                let vm = this;
                this.stripe.createSource(this.card).then(function(result) {
                    if (result.error) {
                        let errorElement = document.getElementById('card-errors');
                        errorElement.textContent = result.error.message;
                        vm.$emit('error', result.error);
                    } else {
                        vm.$emit('save', vm.card, result.source);
                    }
                });
            },

            /**
             * Cancel.
             */
            cancel() {
                this.card.clear();

                let errorElement = document.getElementById('card-errors');
                errorElement.textContent = '';

                this.$emit('cancel');
            }

        },

        mounted() {
            this.stripe = Stripe(window.stripePublishableKey);
            this.elements = this.stripe.elements();
            this.card = this.elements.create('card');

            // Vue likes to stay in control of $el but Stripe needs a real element
            const el = document.createElement('div')
            this.card.mount(el)

            // this.$children cannot be used because it expects a VNode :(
            this.$refs.cardElement.appendChild(el)
        },

    }
</script>

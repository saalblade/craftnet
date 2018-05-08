<template>
    <div ref="cardElement" id="card-element" class="card-element form-control mb-3"></div>
</template>

<script>
    export default {

        mounted() {
            this.stripe = Stripe(window.stripePublicKey);
            this.elements = this.stripe.elements();
            this.card = this.elements.create('card', { hidePostalCode: true });

            // Vue likes to stay in control of $el but Stripe needs a real element
            const el = document.createElement('div')
            this.card.mount(el)

            // this.$children cannot be used because it expects a VNode :(
            this.$refs.cardElement.appendChild(el)
        },

    }
</script>
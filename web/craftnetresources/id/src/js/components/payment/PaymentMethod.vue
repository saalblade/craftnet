<template>
    <div>
        <template v-if="card">
            <p><label><input type="radio" value="existingCard" v-model="paymentMode" /> Use card <span>{{ card.brand }} •••• •••• •••• {{ card.last4 }} — {{ card.exp_month }}/{{ card.exp_year }}</span></label></p>
        </template>

        <p><label><input type="radio" value="newCard" v-model="paymentMode" /> Use a new credit card</label></p>

        <template v-if="paymentMode === 'newCard'">
            <card-element v-if="!cardToken" ref="newCard" />
            <p v-else>{{ cardToken.card.brand }} •••• •••• •••• {{ cardToken.card.last4 }} ({{ cardToken.card.exp_month }}/{{ cardToken.card.exp_year }}) <a class="delete icon" @click="cardToken = null">Delete</a></p>

            <checkbox-field id="replaceCard" v-model="replaceCard" label="Save as my new credit card" />
        </template>
    </div>
</template>

<script>
    import CraftComponents from "@benjamindavid/craftcomponents";
    import CardElement from '../CardElement'

    export default {

        data() {
            return {
                card: null,
                cardToken: null,
                paymentMode: 'newCard',
                replaceCard: false,
            }
        },

        components: {
            ...CraftComponents,
            CardElement,
        },

    }
</script>
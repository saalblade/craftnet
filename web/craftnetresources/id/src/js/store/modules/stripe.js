import Vue from 'vue'
import Vuex from 'vuex'
import stripeApi from '../../api/stripe'

Vue.use(Vuex)

/**
 * State
 */
const state = {
    card: null,
    cardToken: null,
    stripeAccount: null,
}

/**
 * Getters
 */
const getters = {}

/**
 * Actions
 */
const actions = {
    disconnectStripeAccount({commit}) {
        return new Promise((resolve, reject) => {
            stripeApi.disconnect()
                .then((response) => {
                    commit('disconnectStripeAccount')
                    resolve(response)
                })
                .catch((response) => {
                    reject(response)
                })
        })
    },

    getStripeAccount({commit}) {
        return new Promise((resolve, reject) => {
            stripeApi.getAccount()
                .then((response) => {
                    commit('updateStripeAccount', {response})
                    resolve(response)
                })
                .catch((response) => {
                    reject(response)
                })
        })
    },

    removeCard({commit}) {
        return new Promise((resolve, reject) => {
            stripeApi.removeCard()
                .then((response) => {
                    commit('removeStripeCard')
                    resolve(response)
                })
                .catch((response) => {
                    reject(response)
                })
        })
    },

    saveCard({commit}, source) {
        return new Promise((resolve, reject) => {
            stripeApi.saveCard(source)
                .then((response) => {
                    commit('updateStripeCard', {card: response.data.card.card})
                    resolve(response)
                })
                .catch((response) => {
                    reject(response)
                })
        })
    },
}

/**
 * Mutations
 */
const mutations = {
    disconnectStripeAccount(state) {
        state.stripeAccount = null
    },

    removeStripeCard(state) {
        state.card = null
    },

    updateCard(state, {card}) {
        state.card = card
    },

    updateCardToken(state, {cardToken}) {
        state.cardToken = cardToken
    },

    updateStripeAccount(state, {response}) {
        state.stripeAccount = response.data
    },

    updateStripeCard(state, {card}) {
        state.card = card
    },
}

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
}

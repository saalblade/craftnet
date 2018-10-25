import Vue from 'vue'
import Vuex from 'vuex'

Vue.use(Vuex)

/**
 * State
 */
const state = {
    notification: null,
    stripeAccountLoading: true,
}

/**
 * Getters
 */
const getters = {

}

/**
 * Actions
 */
const actions = {

    setStripeAccountLoading({commit}, loading) {
        commit('updateStripeAccountLoading', loading)
    },

    /**
     *  Displays an error.
     *
     * @param {string} message
     */
    displayNotice({dispatch}, message) {
        dispatch('displayNotification', {type: 'notice', message})
    },

    /**
     *  Displays an error.
     *
     * @param {string} message
     */
    displayError({dispatch}, message) {
        dispatch('displayNotification', {type:'error', message})
    },

    /**
     *  Displays a notification.
     *
     * @param {string} type
     * @param {string} message
     */
    displayNotification({commit}, {type, message}) {
        commit('updateNotification', {
            type: type,
            message: message
        })

        setTimeout(function() {
            this.notification = null;
            commit('updateNotification', null)
        }.bind(this), 2000);
    },

}

/**
 * Mutations
 */
const mutations = {

    updateStripeAccountLoading(state, loading) {
        state.stripeAccountLoading = loading
    },

    updateNotification(state, notification) {
        state.notification = notification;
    },

}

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
}

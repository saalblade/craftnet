import Vue from 'vue'
import Vuex from 'vuex'

Vue.use(Vuex)

/**
 * State
 */
const state = {
    loading: true,
    notification: null,
    stripeAccountLoading: true,
    invoicesLoading: true,
    renewLicensesStep: null,
    showRenewLicensesModal: false,
    renewLicense: null,
}

/**
 * Getters
 */
const getters = {}

/**
 * Actions
 */
const actions = {

    /**
     *  Show the renew licenses modal at a given step.
     *
     * @param {string} step
     */
    showRenewLicensesModal({commit}, step) {
        commit('updateRenewLicensesStep', step)
        commit('updateShowRenewLicensesModal', true)
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

    updateInvoicesLoading(state, loading) {
        state.invoicesLoading = loading
    },

    updateRenewLicensesStep(state, step) {
        state.renewLicensesStep = step
    },

    updateShowRenewLicensesModal(state, show) {
        state.showRenewLicensesModal = show
    },

    updateLoading(state, loading) {
        state.loading = loading
    },

    updateRenewLicense(state, license) {
        state.renewLicense = license
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

import Vue from 'vue'
import Vuex from 'vuex'
import invoicesApi from '../../api/invoices'

Vue.use(Vuex)

/**
 * State
 */
const state = {
    invoices: [],
    invoicesLoading: false,
}

/**
 * Getters
 */
const getters = {

    getInvoiceByNumber(state) {
        return number => {
            if (state.invoices) {
                return state.invoices.find(inv => inv.number == number)
            }
        }
    },

}

/**
 * Actions
 */
const actions = {

    getInvoices({commit}) {
        return new Promise((resolve, reject) => {
            invoicesApi.getInvoices(response => {
                if (response.data && !response.data.error) {
                    commit('updateInvoices', {response});
                    resolve(response);
                } else {
                    reject(response);
                }
            }, response => {
                reject(response);
            })
        })
    },

}

/**
 * Mutations
 */
const mutations = {

    updateInvoices(state, {response}) {
        state.invoices = response.data;
    },

    updateInvoicesLoading(state, loading) {
        state.invoicesLoading = loading
    },

}

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
}

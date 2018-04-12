import * as types from '../mutation-types'
import Vue from 'vue'
import Vuex from 'vuex'
import accountApi from '../../api/account'

Vue.use(Vuex)

/**
 * State
 */
const state = {
    invoices: [],
    upcomingInvoice: null,
}

/**
 * Getters
 */
const getters = {

    invoices(state) {
        return state.invoices;
    },

    getInvoiceById(state) {
        return id => {
            if (state.craftId.invoices) {
                return state.craftId.invoices.find(inv => inv.id == id)
            }
        }
    },

    getInvoiceByNumber(state) {
        return number => {
            if (state.invoices) {
                return state.invoices.find(inv => inv.number == number)
            }
        }
    },

    upcomingInvoice(state) {
        return state.upcomingInvoice;
    }
}

/**
 * Actions
 */
const actions = {

    getInvoices({commit}) {
        return new Promise((resolve, reject) => {
            accountApi.getInvoices(response => {
                if (response.data && !response.data.error) {
                    commit(types.RECEIVE_INVOICES, {response});
                    resolve(response);
                } else {
                    reject(response);
                }
            }, response => {
                reject(response);
            })
        })
    }

}

/**
 * Mutations
 */
const mutations = {

    [types.RECEIVE_INVOICES](state, {response}) {
        state.invoices = response.data;
    },

    [types.RECEIVE_UPCOMING_INVOICE](state, {upcomingInvoice}) {
        state.upcomingInvoice = upcomingInvoice;
    }

}

export default {
    state,
    getters,
    actions,
    mutations
}

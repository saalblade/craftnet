import api from '../api'
import Vue from 'vue'
import Vuex from 'vuex'
import * as actions from './actions'
import * as mutations from './mutations'

Vue.use(Vuex);

export default new Vuex.Store({
    strict: true,

    state: {
        craftId: null,
        stripeAccount: null,
        stripeCustomer: null,
        stripeCard: null,
    },

    getters: {
        craftId: state => {
            return state.craftId;
        },

        stripeAccount: state => {
            return state.stripeAccount;
        },

        stripeCard: state => {
            return state.stripeCard;
        },

        stripeCustomer: state => {
            return state.stripeCustomer;
        },

        craftLicenses: state => {
            if(state.craftId) {
                return state.craftId.craftLicenses;
            }
        },

        currentUser: state => {
            if(state.craftId) {
                return state.craftId.currentUser;
            }
        },

        customers: state => {
            if(state.craftId) {
                return state.craftId.customers;
            }
        },

        licenses: state => {
            if(state.craftId) {
                return state.craftId.pluginLicenses.concat(state.craftId.craftLicenses);
            }
        },

        payments: state => {
            if(state.craftId) {
                return state.craftId.payments;
            }
        },

        payouts: state => {
            if(state.craftId) {
                return state.craftId.payouts;
            }
        },

        payoutsScheduled: state => {
            if(state.craftId) {
                return state.craftId.payoutsScheduled;
            }
        },

        pluginLicenses: state => {
            if(state.craftId) {
                return state.craftId.pluginLicenses;
            }
        },

        plugins: state => {
            if(state.craftId) {
                return state.craftId.plugins;
            }
        },
    },

    actions,
    mutations,
})

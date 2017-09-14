import api from '../api'
import Vue from 'vue'
import Vuex from 'vuex'
import * as types from './mutation-types'

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

    actions: {
        getCraftIdData ({ commit }) {
            return new Promise((resolve, reject) => {
                let userId = window.currentUserId;

                api.getCraftIdData(userId, data => {
                    commit(types.RECEIVE_CRAFT_ID_DATA, { data })
                    resolve(data);
                }, response => {
                    reject(response);
                })
            })
        },

        saveUser({ commit, state }, user) {
            return new Promise((resolve, reject) => {
                api.saveUser(user, data => {
                    if(!data.errors) {
                        commit(types.SAVE_USER, { user, data });
                        resolve(data);
                    } else {
                        reject(data);
                    }
                }, response => {
                    reject(response);
                })
            })
        },

        getStripeAccount({commit}) {
            return new Promise((resolve, reject) => {
                api.getStripeAccount(data => {
                    commit(types.RECEIVE_STRIPE_ACCOUNT, { data })
                    resolve(data);
                }, response => {
                    reject(response);
                })
            })
        },

        getStripeCustomer({commit}) {
            return new Promise((resolve, reject) => {
                api.getStripeCustomer(data => {
                    commit(types.RECEIVE_STRIPE_CUSTOMER, { data })
                    commit(types.RECEIVE_STRIPE_CARD, { data })
                    resolve(data);
                }, response => {
                    reject(response);
                })
            })
        },

        disconnectStripeAccount({commit}) {
            return new Promise((resolve, reject) => {
                api.disconnectStripeAccount(data => {
                    commit(types.DISCONNECT_STRIPE_ACCOUNT, { data })
                    resolve(data);
                }, response => {
                    reject(response);
                })
            })
        },

        saveCard({commit}, token) {
            return new Promise((resolve, reject) => {
                api.saveCard(token, data => {
                    commit(types.SAVE_CARD, { data })
                    resolve(data);
                }, response => {
                    reject(response);
                })
            })
        },

        removeCard({commit}) {
            return new Promise((resolve, reject) => {
                api.removeCard(data => {
                    commit(types.REMOVE_CARD, { data })
                    resolve(data);
                }, response => {
                    reject(response);
                })
            })
        },

        saveLicense({ commit, state }, license) {
            return new Promise((resolve, reject) => {
                api.saveLicense(license, data => {
                    if(!data.errors) {
                        commit(types.SAVE_LICENSE, { license, data });
                        resolve(data);
                    } else {
                        reject(data);
                    }
                }, response => {
                    reject(response);
                })
            })
        },

        savePlugin({ commit, state }, plugin) {
            return new Promise((resolve, reject) => {
                api.savePlugin(license, data => {
                    if(!data.errors) {
                        commit(types.SAVE_PLUGIN, { plugin, response });
                        resolve(data);
                    } else {
                        reject(data);
                    }
                }, response => {
                    reject(response);
                })
            })
        },
    },

    mutations: {
        [types.SAVE_CARD] (state, { data }) {
            state.stripeCard = data.card
        },
        [types.REMOVE_CARD] (state, { data }) {
            state.stripeCard = null
        },

        [types.RECEIVE_STRIPE_CUSTOMER] (state, { data }) {
            state.stripeCustomer = data.customer
        },

        [types.RECEIVE_STRIPE_CARD] (state, { data }) {
            state.stripeCard = data.card
        },

        [types.RECEIVE_STRIPE_ACCOUNT] (state, { data }) {
            state.stripeAccount = data
        },


        [types.DISCONNECT_STRIPE_ACCOUNT] (state, { data }) {
            state.stripeAccount = null
        },


        [types.RECEIVE_CRAFT_ID_DATA] (state, { data }) {
            state.craftId = data
        },

        [types.SAVE_USER] (state, {user, response}) {
            for (let attribute in user) {
                if(attribute == 'id') {
                    continue;
                }

                for (let attribute in user) {
                    state.craftId.currentUser[attribute] = user[attribute];
                }
            }
        },

        [types.SAVE_LICENSE] (state, {license, response}) {
            let stateLicense = null;
            if(license.type === 'craftLicense') {
                stateLicense = state.craftId.craftLicenses.find(l => l.id == license.id);
            } else if(license.type === 'pluginLicense') {
                stateLicense = state.craftId.pluginLicenses.find(l => l.id == license.id);
            }

            for (let attribute in license) {
                switch(attribute) {
                    case 'id':
                    case 'type':
                        // ignore
                        break;
                    case 'autoRenew':
                        stateLicense[attribute] = (license[attribute] ? 1 : 0);
                        break;
                    default:
                        stateLicense[attribute] = license[attribute];
                }
            }
        },

        [types.SAVE_PLUGIN] (state, {plugin, response}) {
            let newPlugin = false;
            let statePlugin = state.craftId.plugins.find(p => p.id == plugin.id);

            if(!statePlugin) {
                statePlugin = {
                    id: response.body.id,
                };
                newPlugin = true;
            }

            for (let attribute in plugin) {
                statePlugin[attribute] = plugin[attribute];
            }

            if(newPlugin) {
                state.craftId.plugins.push(statePlugin);
            }
        },

        [types.SAVE_CRAFT_ID_DATA] (state) {
        },
    },
})

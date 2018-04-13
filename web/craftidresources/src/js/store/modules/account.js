import * as types from '../mutation-types'
import Vue from 'vue'
import Vuex from 'vuex'
import accountApi from '../../api/account'

Vue.use(Vuex)

/**
 * State
 */
const state = {
    apps: {},
    currentUser: null,
    billingAddress: null,
    invoices: [],
    stripeAccount: null,
    stripeCard: null,
    stripeCustomer: null,
    upcomingInvoice: null,
}

/**
 * Getters
 */
const getters = {

    userIsInGroup(state) {
        return handle => {
            return state.currentUser.groups.find(g => g.handle === handle)
        }
    },

    getInvoiceById(state) {
        return id => {
            return state.invoices.find(inv => inv.id == id)
        }
    },

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

    /**
     * Apps
     */

    connectAppCallback({commit}, apps) {
        commit(types.CONNECT_APP_CALLBACK, {apps})
    },

    disconnectApp({commit}, appHandle) {
        return new Promise((resolve, reject) => {
            accountApi.disconnectApp(appHandle, response => {
                    commit(types.DISCONNECT_APP, {appHandle});
                    resolve(response);
                },
                response => {
                    reject(response);
                })
        })
    },

    /**
     * User
     */

    deleteUserPhoto({commit}) {
        return new Promise((resolve, reject) => {
            accountApi.deleteUserPhoto(response => {
                commit(types.DELETE_USER_PHOTO, {response});
                resolve(response);
            }, response => {
                reject(response);
            })
        })
    },

    saveUser({commit}, user) {
        return new Promise((resolve, reject) => {
            accountApi.saveUser(user, response => {
                    if (!response.data.errors) {
                        commit(types.SAVE_USER, {user, response});
                        resolve(response);
                    } else {
                        reject(response);
                    }
                },
                response => {
                    reject(response);
                })
        })
    },

    uploadUserPhoto({commit}, data) {
        return new Promise((resolve, reject) => {
            accountApi.uploadUserPhoto(data, response => {
                commit(types.UPLOAD_USER_PHOTO, {response});
                resolve(response);
            }, response => {
                reject(response);
            })
        })
    },

    saveBillingInfo({commit}, data) {
        return new Promise((resolve, reject) => {
            accountApi.saveBillingInfo(data, response => {
                    if (!response.data.errors) {
                        commit(types.SAVE_BILLING_INFO, {response});
                        resolve(response);
                    } else {
                        reject(response);
                    }
                },
                response => {
                    reject(response);
                })
        })
    },


    /**
     * Credit cards
     */

    removeCard({commit}) {
        return new Promise((resolve, reject) => {
            accountApi.removeCard(response => {
                commit(types.REMOVE_CARD);
                resolve(response);
            }, response => {
                reject(response);
            })
        })
    },

    saveCard({commit}, source) {
        return new Promise((resolve, reject) => {
            accountApi.saveCard(source, response => {
                commit(types.SAVE_CARD, {response});
                resolve(response);
            }, response => {
                reject(response);
            })
        })
    },

    getStripeCustomer({commit}) {
        return new Promise((resolve, reject) => {
            accountApi.getStripeCustomer(response => {
                commit(types.RECEIVE_STRIPE_CUSTOMER, {customer: response.data.customer});
                commit(types.RECEIVE_STRIPE_CARD, {card: response.data.card});
                resolve(response);
            }, response => {
                reject(response);
            })
        })
    },


    /**
     * Stripe account
     */

    disconnectStripeAccount({commit}) {
        return new Promise((resolve, reject) => {
            accountApi.disconnectStripeAccount(response => {
                commit(types.DISCONNECT_STRIPE_ACCOUNT);
                resolve(response);
            }, response => {
                reject(response);
            })
        })
    },

    getStripeAccount({commit}) {
        return new Promise((resolve, reject) => {
            accountApi.getStripeAccount(response => {
                commit(types.RECEIVE_STRIPE_ACCOUNT, {response});
                resolve(response);
            }, response => {
                reject(response);
            })
        })
    },


    /**
     * Invoices
     */

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
    /**
     * Apps
     */

    [types.RECEIVE_APPS](state, {apps}) {
        state.apps = apps;
    },

    [types.CONNECT_APP_CALLBACK](state, {apps}) {
        state.apps = apps;
    },

    [types.DISCONNECT_APP](state, {appHandle}) {
        Vue.delete(state.apps, appHandle);
    },


    /**
     * User
     */

    [types.UPLOAD_USER_PHOTO](state, {response}) {
        state.currentUser.photoId = response.data.photoId;
        state.currentUser.photoUrl = response.data.photoUrl;
    },

    [types.DELETE_USER_PHOTO](state, {response}) {
        state.currentUser.photoId = response.data.photoId;
        state.currentUser.photoUrl = response.data.photoUrl;
    },

    [types.SAVE_USER](state, {user, response}) {
        for (let attribute in user) {
            if (attribute === 'id' || attribute === 'email') {
                continue;
            }

            state.currentUser[attribute] = user[attribute];

            if (user.enablePluginDeveloperFeatures) {
                let groupExists = state.currentUser.groups.find(g => g.handle === 'developers');

                if (!groupExists) {
                    state.currentUser.groups.push({
                        id: 1,
                        name: 'Developers',
                        handle: 'developers',
                    })
                }
            }
        }
    },

    [types.SAVE_BILLING_INFO](state, {response}) {
        state.billingAddress = response.data.address
    },

    [types.RECEIVE_BILLING_ADDRESS](state, {billingAddress}) {
        state.billingAddress = billingAddress
    },

    [types.RECEIVE_CURRENT_USER](state, {currentUser}) {
        state.currentUser = currentUser
    },


    /**
     * Credit cards
     */

    [types.REMOVE_CARD](state) {
        state.stripeCard = null
    },

    [types.SAVE_CARD](state, {response}) {
        state.stripeCard = response.data.card.card
    },

    [types.RECEIVE_STRIPE_CARD](state, {card}) {
        state.stripeCard = card
    },

    [types.RECEIVE_STRIPE_CUSTOMER](state, {customer}) {
        state.stripeCustomer = customer
    },


    /**
     * Stripe Account
     */

    [types.DISCONNECT_STRIPE_ACCOUNT](state) {
        state.stripeAccount = null
    },

    [types.RECEIVE_STRIPE_ACCOUNT](state, {response}) {
        state.stripeAccount = response.data
    },


    /**
     * Invoices
     */

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

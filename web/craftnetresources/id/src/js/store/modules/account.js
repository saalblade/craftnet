import Vue from 'vue'
import Vuex from 'vuex'
import accountApi from '../../api/account'
import appsApi from '../../api/apps'
import usersApi from '../../api/users'
import stripeApi from '../../api/stripe'

Vue.use(Vuex)

/**
 * State
 */
const state = {
    billingAddress: null,
    card: null,
    cardToken: null,
    currentUser: null,
    currentUserLoaded: false,
    stripeAccount: null,
    stripeCustomer: null,
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
}

/**
 * Actions
 */
const actions = {
    /**
     * Apps
     */

    connectAppCallback({commit}, apps) {
        commit('updateApps', {apps})
    },

    disconnectApp({commit}, appHandle) {
        return new Promise((resolve, reject) => {

            appsApi.disconnect(appHandle, response => {
                    commit('disconnectApp', {appHandle});
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
                commit('deleteUserPhoto', {response});
                resolve(response);
            }, response => {
                reject(response);
            })
        })
    },

    saveUser({commit}, user) {
        return new Promise((resolve, reject) => {
            usersApi.saveUser(user, response => {
                    if (!response.data.errors) {
                        commit('saveUser', {user, response});
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
                commit('uploadUserPhoto', {response});
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
                        commit('updateBillingAddress', {billingAddress: response.data.address});
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
            stripeApi.removeCard(response => {
                commit('removeStripeCard');
                resolve(response);
            }, response => {
                reject(response);
            })
        })
    },

    saveCard({commit}, source) {
        return new Promise((resolve, reject) => {
            stripeApi.saveCard(source, response => {
                commit('updateStripeCard', {card: response.data.card.card});
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
            stripeApi.disconnect(response => {
                commit('disconnectStripeAccount');
                resolve(response);
            }, response => {
                reject(response);
            })
        })
    },

    getStripeAccount({commit}) {
        return new Promise((resolve, reject) => {
            stripeApi.getAccount(response => {
                commit('updateStripeAccount', {response});
                resolve(response);
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
    /**
     * Apps
     */

    disconnectApp(state, {appHandle}) {
        Vue.delete(state.apps, appHandle);
    },


    /**
     * User
     */

    uploadUserPhoto(state, {response}) {
        state.currentUser.photoId = response.data.photoId;
        state.currentUser.photoUrl = response.data.photoUrl;
    },

    deleteUserPhoto(state, {response}) {
        state.currentUser.photoId = response.data.photoId;
        state.currentUser.photoUrl = response.data.photoUrl;
    },

    saveUser(state, {user}) {
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

    updateBillingAddress(state, {billingAddress}) {
        state.billingAddress = billingAddress
    },

    updateCard(state, {card}) {
        state.card = card
    },

    updateCardToken(state, {cardToken}) {
        state.cardToken = cardToken
    },

    updateCurrentUser(state, {currentUser}) {
        state.currentUser = currentUser
    },

    updateCurrentUserLoaded(state, loaded) {
        state.currentUserLoaded = loaded
    },


    /**
     * Credit cards
     */

    removeStripeCard(state) {
        state.card = null
    },

    updateStripeCard(state, {card}) {
        state.card = card
    },


    /**
     * Stripe Account
     */

    disconnectStripeAccount(state) {
        state.stripeAccount = null
    },

    updateStripeAccount(state, {response}) {
        state.stripeAccount = response.data
    },
}

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
}

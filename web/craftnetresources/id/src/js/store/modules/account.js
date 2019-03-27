import Vue from 'vue'
import Vuex from 'vuex'
import accountApi from '../../api/account'
import usersApi from '../../api/users';

Vue.use(Vuex)

/**
 * State
 */
const state = {
    billingAddress: null,
    currentUser: null,
    accountLoading: false,
    currentUserLoaded: false,
    craftSessionLoaded: false,
    hasApiToken: false,
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
    deleteUserPhoto({commit}) {
        return new Promise((resolve, reject) => {
            accountApi.deleteUserPhoto()
                .then((response) => {
                    commit('deleteUserPhoto', {response})
                    resolve(response)
                })
                .catch((response) => {
                    reject(response)
                })
        })
    },

    generateApiToken({commit}) {
        return new Promise((resolve, reject) => {
            accountApi.generateApiToken()
                .then((response) => {
                    if (response.data && !response.data.error) {
                        commit('updateHasApiToken', {hasApiToken: !!response.data.apiToken})
                        resolve(response)
                    } else {
                        reject(response)
                    }
                })
                .catch((error) => {
                    reject(error.response)
                })
        })
    },

    saveBillingInfo({commit}, data) {
        return new Promise((resolve, reject) => {
            accountApi.saveBillingInfo(data)
                .then((response) => {
                    if (!response.data.errors) {
                        commit('updateBillingAddress', {billingAddress: response.data.address})
                        resolve(response)
                    } else {
                        reject(response)
                    }
                })
                .catch((error) => {
                    reject(error.response)
                })
        })
    },

    saveUser({commit}, user) {
        return new Promise((resolve, reject) => {
            usersApi.saveUser(user)
                .then((response) => {
                    if (!response.data.errors) {
                        commit('saveUser', {user, response})
                        resolve(response)
                    } else {
                        reject(response)
                    }
                })
                .catch((error) => {
                    reject(error.response)
                })
        })
    },

    uploadUserPhoto({commit}, data) {
        return new Promise((resolve, reject) => {
            accountApi.uploadUserPhoto(data)
                .then((response) => {
                    commit('uploadUserPhoto', {response})
                    resolve(response)
                })
                .catch((response) => {
                    reject(response)
                })
        })
    },

    getAccount({commit, state}) {
        return new Promise((resolve, reject) => {
            accountApi.getAccount()
                .then((response) => {
                    commit('updateBillingAddress', {billingAddress: response.data.billingAddress})
                    commit('updateHasApiToken', {hasApiToken: response.data.currentUser.hasApiToken})
                    commit('updateCurrentUser', {currentUser: response.data.currentUser})
                    commit('stripe/updateCard', {card: response.data.card}, {root: true})
                    commit('stripe/updateCardToken', {cardToken: response.data.cardToken}, {root: true})
                    commit('updateCurrentUserLoaded', true)

                    resolve(response)
                })
                .catch((response) => {
                    commit('updateCurrentUserLoaded', true)
                    reject(response)
                })
        })
    },

    loadAccount({commit, state, dispatch}) {
        return new Promise((resolve, reject) => {
            if (!state.accountLoading) {
                commit('updateAccountLoading', true)

                let loadAccount = false

                if (!state.craftSessionLoaded) {
                    commit('updateCraftSessionLoaded', true)

                    if (window.loggedIn) {
                        loadAccount = true
                    } else {
                        commit('updateCurrentUserLoaded', true)
                    }
                } else {
                    if (!state.currentUserLoaded) {
                        loadAccount = true
                    }
                }

                if (loadAccount) {
                    dispatch('getAccount')
                        .then(() => {
                            commit('updateAccountLoading', false)
                            resolve(true)
                        })
                        .catch(() => {
                            commit('updateAccountLoading', false)
                            resolve(false)
                        })
                } else {
                    commit('updateAccountLoading', false)
                    resolve(!!state.currentUser)
                }
            } else {
                resolve(!!state.currentUser)
            }
        })
    },
}

/**
 * Mutations
 */
const mutations = {
    deleteUserPhoto(state, {response}) {
        state.currentUser.photoId = response.data.photoId
        state.currentUser.photoUrl = response.data.photoUrl
    },

    updateBillingAddress(state, {billingAddress}) {
        state.billingAddress = billingAddress
    },

    updateCraftSessionLoaded(state, loaded) {
        state.craftSessionLoaded = loaded
    },

    updateCurrentUser(state, {currentUser}) {
        state.currentUser = currentUser
    },

    updateCurrentUserLoaded(state, loaded) {
        state.currentUserLoaded = loaded
    },

    updateAccountLoading(state, loading) {
        state.accountLoading = loading
    },

    updateHasApiToken(state, {hasApiToken}){
        state.hasApiToken = hasApiToken
    },

    uploadUserPhoto(state, {response}) {
        state.currentUser.photoId = response.data.photoId
        state.currentUser.photoUrl = response.data.photoUrl
    },

    saveUser(state, {user}) {
        for (let attribute in user) {
            if (attribute === 'id' || attribute === 'email') {
                continue
            }

            state.currentUser[attribute] = user[attribute]

            if (user.enablePluginDeveloperFeatures) {
                let groupExists = state.currentUser.groups.find(g => g.handle === 'developers')

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
}

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
}

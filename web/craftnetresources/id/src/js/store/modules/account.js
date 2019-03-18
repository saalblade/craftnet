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
    currentUserLoaded: false,
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

    getAccount({commit}) {
        return new Promise((resolve, reject) => {
            accountApi.getAccount()
                .then((response) => {
                    commit('account/updateBillingAddress', {billingAddress: response.data.billingAddress}, {root: true})
                    commit('account/updateHasApiToken', {hasApiToken: response.data.currentUser.hasApiToken}, {root: true})
                    commit('stripe/updateCard', {card: response.data.card}, {root: true})
                    commit('stripe/updateCardToken', {cardToken: response.data.cardToken}, {root: true})
                    commit('account/updateCurrentUser', {currentUser: response.data.currentUser}, {root: true})
                    commit('account/updateCurrentUserLoaded', true, {root: true})

                    resolve(response)
                })
                .catch((response) => {
                    commit('account/updateCurrentUserLoaded', true, {root: true})

                    reject(response)
                })
        })
    }
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

    updateCurrentUser(state, {currentUser}) {
        state.currentUser = currentUser
    },

    updateCurrentUserLoaded(state, loaded) {
        state.currentUserLoaded = loaded
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

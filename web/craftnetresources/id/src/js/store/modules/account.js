import Vue from 'vue'
import Vuex from 'vuex'
import accountApi from '../../api/account'

Vue.use(Vuex)

/**
 * State
 */
const state = {
    billingAddress: null,
    hasApiToken: false,
}

/**
 * Getters
 */
const getters = {}

/**
 * Actions
 */
const actions = {
    deleteUserPhoto({commit}) {
        return new Promise((resolve, reject) => {
            accountApi.deleteUserPhoto()
                .then((response) => {
                    commit('deleteUserPhoto', {response});
                    resolve(response);
                })
                .catch((response) => {
                    reject(response);
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
                .then((response) => {
                    reject(response)
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
                .catch((response) => {
                    reject(response)
                })
        })
    },
}

/**
 * Mutations
 */
const mutations = {
    deleteUserPhoto(rootState, {response}) {
        rootState.users.currentUser.photoId = response.data.photoId;
        rootState.users.currentUser.photoUrl = response.data.photoUrl;
    },

    updateBillingAddress(state, {billingAddress}) {
        state.billingAddress = billingAddress
    },

    updateHasApiToken(state, {hasApiToken}){
        state.hasApiToken = hasApiToken;
    },

    uploadUserPhoto(rootState, {response}) {
        rootState.users.currentUser.photoId = response.data.photoId;
        rootState.users.currentUser.photoUrl = response.data.photoUrl;
    },
}

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
}

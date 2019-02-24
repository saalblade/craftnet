import Vue from 'vue'
import Vuex from 'vuex'
import accountApi from '../../api/account'
import usersApi from '../../api/users'

Vue.use(Vuex)

/**
 * State
 */
const state = {
    billingAddress: null,
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
}

/**
 * Mutations
 */
const mutations = {
    uploadUserPhoto(rootState, {response}) {
        rootState.users.currentUser.photoId = response.data.photoId;
        rootState.users.currentUser.photoUrl = response.data.photoUrl;
    },

    deleteUserPhoto(rootState, {response}) {
        rootState.users.currentUser.photoId = response.data.photoId;
        rootState.users.currentUser.photoUrl = response.data.photoUrl;
    },

    updateBillingAddress(state, {billingAddress}) {
        state.billingAddress = billingAddress
    },
}

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
}

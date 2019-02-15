import Vue from 'vue'
import Vuex from 'vuex'
import appsApi from '../../api/apps';

Vue.use(Vuex)

/**
 * State
 */
const state = {
    apps: {},
    appsLoading: false,
}

/**
 * Getters
 */
const getters = {}

/**
 * Actions
 */
const actions = {

    getApps({commit, state}) {
        if (state.appsLoading) {
            return false
        }

        if (Object.keys(state.apps).length > 0) {
            return false
        }

        commit('updateAppsLoading', true)

        return new Promise((resolve, reject) => {
            appsApi.getApps()
                .then(response => {
                    commit('updateAppsLoading', false)
                    commit('updateApps', {apps: response.data})
                    resolve(response)
                })
                .catch(response => {
                    commit('updateAppsLoading', false)
                    reject(response)
                })
        })
    }

}

/**
 * Mutations
 */
const mutations = {

    updateApps(state, {apps}) {
        state.apps = apps;
    },

    updateAppsLoading(state, loading) {
        state.appsLoading = loading
    },

}

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
}

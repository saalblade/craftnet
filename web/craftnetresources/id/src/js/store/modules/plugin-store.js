import Vue from 'vue'
import Vuex from 'vuex'
import pluginStoreApi from '../../api/plugin-store'

Vue.use(Vuex)

/**
 * State
 */
const state = {
    categories: [],
    featuredPlugins: [],
    plugins: [],
    pluginStoreDataLoaded: false,
    expiryDateOptions: [],
}

/**
 * Getters
 */
const getters = {
    getPluginByHandle(state) {
        return handle => {
            return state.plugins.find(plugin => plugin.handle === handle)
        }
    },
}

/**
 * Actions
 */
const actions = {
    getPluginStoreData({commit, state}) {
        return new Promise((resolve, reject) => {
            if (!state.pluginStoreDataLoaded) {
                pluginStoreApi.getData()
                    .then((response) => {
                        commit('updatePluginStoreData', {response})
                        resolve()
                    })
                    .catch((response) => {
                        reject(response)
                    })
            } else {
                resolve()
            }
        })
    },
}

/**
 * Mutations
 */
const mutations = {
    updatePluginStoreData(state, {response}) {
        state.categories = response.data.categories
        state.featuredPlugins = response.data.featuredPlugins
        state.plugins = response.data.plugins
        state.expiryDateOptions = response.data.expiryDateOptions,
        state.pluginStoreDataLoaded = true
    },
}

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
}

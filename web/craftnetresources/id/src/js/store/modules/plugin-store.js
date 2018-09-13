import Vue from 'vue'
import Vuex from 'vuex'
import axios from 'axios'

Vue.use(Vuex)

/**
 * State
 */
const state = {
    categories: [],
    featuredPlugins: [],
    plugins: [],
    pluginStoreDataLoaded: false,
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
                axios.get('https://api.craftcms.test/v1/plugin-store')
                    .then(response => {
                        commit('updatePluginStoreData', {response})
                        resolve()
                    })
                    .catch(reject)
            } else {
                resolve();
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

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

    getPluginStoreData({commit}) {
        return axios.get(Craft.actionUrl + '/craftnet/id/craft-id/plugin-store-data', '', {
                headers: {
                    'X-CSRF-Token': Craft.csrfTokenValue,
                }
            })
            .then(response => {
                commit('updatePluginStoreData', {response})
            })
    },

}

/**
 * Mutations
 */
const mutations = {

    updatePluginStoreData(state, {response}) {
        console.log('receive plugin store data mutation');
        state.categories = response.data.categories
        state.featuredPlugins = response.data.featuredPlugins
        state.plugins = response.data.plugins
    },

}

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
}

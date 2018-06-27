import axios from 'axios'

function beforeAxiosRequest() {
    if(process.env.NODE_ENV === 'development') {
        process.env.NODE_TLS_REJECT_UNAUTHORIZED = 0
    }
}

/**
 * State
 */
export const state = () => ({
    categories: [],
    featuredPlugins: [],
    plugins: [],
    actionUrl: null,
    plugin: null,
})

/**
 * Getters
 */
export const getters = {

    getPluginsByIds(state) {
        return ids => {
            let plugins = [];

            ids.forEach(function(id) {
                const plugin = state.plugins.find(p => p.id === id)
                plugins.push(plugin)
            })

            return plugins;
        }
    },

    getFeaturedPlugin(state) {
        return id => {
            return state.featuredPlugins.find(g => g.id == id)
        }
    },

    getCategoryById(state) {
        return id => {
            return state.categories.find(c => c.id == id)
        }
    },

    getPluginsByCategory(state) {
        return categoryId => {
            return state.plugins.filter(p => {
                return p.categoryIds.find(c => c == categoryId)
            })
        }
    },

    getPluginById(state) {
        return id => {
            return state.plugins.find(p => p.id == id)
        }
    },

    getPluginsByDeveloperId(state) {
        return developerId => {
            return state.plugins.filter(p => p.developerId == developerId)
        }
    },

}

/**
 * Actions
 */
export const actions = {

    getPluginStoreData({state, commit}) {
        beforeAxiosRequest()

        return axios.get(process.env.actionUrl + '/craftnet/plugins/plugin-store/plugin-store-data')
            .then(response => {
                commit('receivePluginStoreData', {response})
            })
            .catch(response => {
                console.log('error', response);
            })
    },

    getPluginDetails({state, commit}, pluginId) {
        beforeAxiosRequest()

        return axios.get(process.env.actionUrl + '/craftnet/plugins/plugin-store/plugin-details', {
                params: {
                    pluginId: pluginId,
                },
            })
            .then(response => {
                commit('updatePluginDetails', response.data)
            })
            .catch(response => {
                console.log('error', response)
            })
    },

    getDeveloper({state, commit}, developerId) {
        beforeAxiosRequest()

        return axios.get(process.env.actionUrl + '/craftnet/plugins/plugin-store/developer', {
                params: {
                    developerId: developerId,
                }
            })
            .then(response => {
                commit('receiveDeveloper', {developer: response.data})
            })
            .catch(response => {
                console.log('error', response)
            })
    },

}

/**
 * Mutations
 */
export const mutations = {

    receivePluginStoreData(state, {response}) {
        state.categories = response.data.categories
        state.featuredPlugins = response.data.featuredPlugins
        state.plugins = response.data.plugins
    },

    updatePluginDetails(state, pluginDetails) {
        state.plugin = pluginDetails
    },

    receiveDeveloper(state, {developer}) {
        state.developer = developer
    },

}

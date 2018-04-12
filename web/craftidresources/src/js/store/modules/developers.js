import * as types from '../mutation-types'
import Vue from 'vue'
import Vuex from 'vuex'
import developerApi from '../../api/developer';

Vue.use(Vuex)

/**
 * State
 */
const state = {
    plugins: []
}

/**
 * Getters
 */
const getters = {

    plugins(state) {
        return state.plugins;
    },

    repositoryIsInUse(state) {
        return repositoryUrl => {
            return state.plugins.find(plugin => plugin.repository === repositoryUrl)
        }
    }

}

/**
 * Actions
 */
const actions = {

    savePlugin({commit}, {plugin}) {
        return new Promise((resolve, reject) => {
            developerApi.savePlugin({plugin}, response => {
                if (response.data.success) {
                    commit(types.SAVE_PLUGIN, {plugin, response});
                    resolve(response);
                } else {
                    reject(response);
                }
            }, response => {
                reject(response);
            })
        })
    },

    submitPlugin({commit}, pluginId) {
        return new Promise((resolve, reject) => {
            developerApi.submitPlugin(pluginId, response => {
                if (response.data.success) {
                    commit(types.SUBMIT_PLUGIN, {pluginId});
                    resolve(response);
                } else {
                    reject(response);
                }
            }, response => {
                reject(response);
            })
        })
    }
}

/**
 * Mutations
 */
const mutations = {

    [types.RECEIVE_PLUGINS](state, {plugins}) {
        state.plugins = plugins
    },

    [types.SAVE_PLUGIN](state, {plugin, response}) {
        let newPlugin = false;
        let statePlugin = state.plugins.find(p => p.id == plugin.pluginId);

        if (!statePlugin) {
            statePlugin = {
                id: response.data.id,
            };
            newPlugin = true;
        }

        statePlugin.siteId = plugin.siteId;
        statePlugin.pluginId = response.data.id;
        statePlugin.icon = plugin.icon;
        statePlugin.iconUrl = response.data.iconUrl + '?' + Math.floor(Math.random() * 1000000);
        statePlugin.iconId = response.data.iconId;
        statePlugin.developerId = plugin.developerId;
        statePlugin.developerName = plugin.developerName;
        statePlugin.handle = plugin.handle;
        statePlugin.packageName = plugin.packageName;
        statePlugin.name = plugin.name;
        statePlugin.shortDescription = plugin.shortDescription;
        statePlugin.longDescription = plugin.longDescription;
        statePlugin.documentationUrl = plugin.documentationUrl;
        statePlugin.changelogPath = plugin.changelogPath;
        statePlugin.repository = plugin.repository;
        statePlugin.license = plugin.license;
        statePlugin.keywords = plugin.keywords;

        let price = parseFloat(plugin.price);
        statePlugin.price = (price ? price : null);

        let renewalPrice = parseFloat(plugin.renewalPrice);
        statePlugin.renewalPrice = (renewalPrice ? renewalPrice : null);

        statePlugin.categoryIds = plugin.categoryIds;

        let screenshotIds = [];
        let screenshotUrls = [];

        if (response.data.screenshots.length > 0) {
            for (let i = 0; i < response.data.screenshots.length; i++) {
                screenshotIds.push(response.data.screenshots[i].id);
                screenshotUrls.push(response.data.screenshots[i].url);
            }
        }

        statePlugin.screenshotIds = screenshotIds;
        statePlugin.screenshotUrls = screenshotUrls;

        if (newPlugin) {
            state.plugins.push(statePlugin);
        }
    },

    [types.SUBMIT_PLUGIN](state, {pluginId}) {
        let statePlugin = state.plugins.find(p => p.id == pluginId);
        statePlugin.pendingApproval = true;
    }
}

export default {
    state,
    getters,
    actions,
    mutations
}

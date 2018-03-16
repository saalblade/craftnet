import * as types from './mutation-types'
import Vue from 'vue';


/**
 * User
 */

export const CONNECT_APP_CALLBACK = (state, {apps}) => {
    state.craftId.apps = apps;
};

export const DELETE_USER_PHOTO = (state, {data, response}) => {
    state.craftId.currentUser.photoId = response.data.photoId;
    state.craftId.currentUser.photoUrl = response.data.photoUrl;
};

export const DISCONNECT_APP = (state, {appHandle}) => {
    Vue.delete(state.craftId.apps, appHandle);
};

export const RECEIVE_CRAFT_ID_DATA = (state, {response}) => {
    state.craftId = response.data
};

export const SAVE_USER = (state, {user, response}) => {
    for (let attribute in user) {
        if (attribute === 'id' || attribute === 'email') {
            continue;
        }

        state.craftId.currentUser[attribute] = user[attribute];

        if (user.enablePluginDeveloperFeatures) {
            let groupExists = state.craftId.currentUser.groups.find(g => g.handle === 'developers');

            if (!groupExists) {
                state.craftId.currentUser.groups.push({
                    id: 1,
                    name: 'Developers',
                    handle: 'developers',
                })
            }
        }
    }
};

export const UPLOAD_USER_PHOTO = (state, {data, response}) => {
    state.craftId.currentUser.photoId = response.data.photoId;
    state.craftId.currentUser.photoUrl = response.data.photoUrl;
};


/**
 * Licenses
 */

export const CLAIM_CMS_LICENSE = (state, {licenseKey}) => {
};

export const CLAIM_PLUGIN_LICENSE = (state, {licenseKey}) => {
};

export const GET_CMS_LICENSES = (state, {response}) => {
    state.craftId.cmsLicenses = response.data;
};


export const GET_PLUGIN_LICENSES = (state, {response}) => {
    state.craftId.pluginLicenses = response.data;
};

export const RELEASE_CMS_LICENSE = (state, {licenseKey}) => {
    state.craftId.cmsLicenses.find((l, index, array) => {
        if (l.key === licenseKey) {
            array.splice(index, 1);
            return true;
        }

        return false;
    });
};

export const RELEASE_PLUGIN_LICENSE = (state, {licenseKey}) => {
    state.craftId.pluginLicenses.find((l, index, array) => {
        if (l.key === licenseKey) {
            array.splice(index, 1);
            return true;
        }

        return false;
    });
};

export const SAVE_CMS_LICENSE = (state, {license}) => {
    let stateLicense = state.craftId.cmsLicenses.find(l => l.key == license.key);
    for (let attribute in license) {
        stateLicense[attribute] = license[attribute];
    }
};

export const SAVE_LICENSE = (state, {license}) => {
    let stateLicense = null;
    if (license.type === 'cmsLicense') {
        stateLicense = state.craftId.cmsLicenses.find(l => l.id == license.id);
    } else if (license.type === 'pluginLicense') {
        stateLicense = state.craftId.pluginLicenses.find(l => l.id == license.id);
    }

    for (let attribute in license) {
        switch (attribute) {
            case 'id':
            case 'type':
                // ignore
                break;
            case 'autoRenew':
                stateLicense[attribute] = (license[attribute] ? 1 : 0);
                break;
            default:
                stateLicense[attribute] = license[attribute];
        }
    }
};


/**
 * Plugins
 */

export const SAVE_PLUGIN = (state, {plugin, response}) => {
    let newPlugin = false;
    let statePlugin = state.craftId.plugins.find(p => p.id == plugin.pluginId);

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
        state.craftId.plugins.push(statePlugin);
    }
};

export const SUBMIT_PLUGIN = (state, {pluginId}) => {
    let statePlugin = state.craftId.plugins.find(p => p.id == pluginId);
    statePlugin.pendingApproval = true;
};


/**
 * Cards
 */

export const REMOVE_CARD = (state) => {
    state.stripeCard = null
};

export const SAVE_CARD = (state, {response}) => {
    state.stripeCard = response.data.card.card
};


/**
 * Stripe
 */

export const DISCONNECT_STRIPE_ACCOUNT = (state) => {
    state.stripeAccount = null
};

export const RECEIVE_STRIPE_ACCOUNT = (state, {response}) => {
    state.stripeAccount = response.data
};

export const RECEIVE_STRIPE_CARD = (state, {response}) => {
    state.stripeCard = response.data.card
};

export const RECEIVE_STRIPE_CUSTOMER = (state, {response}) => {
    state.stripeCustomer = response.data.customer
};


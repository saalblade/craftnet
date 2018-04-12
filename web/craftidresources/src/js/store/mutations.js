import * as types from './mutation-types'
import Vue from 'vue';


/**
 * User
 */

export const CONNECT_APP_CALLBACK = (state, {apps}) => {
    state.craftId.apps = apps;
};

export const DELETE_USER_PHOTO = (state, {response}) => {
    state.craftId.currentUser.photoId = response.data.photoId;
    state.craftId.currentUser.photoUrl = response.data.photoUrl;
};

export const DISCONNECT_APP = (state, {appHandle}) => {
    Vue.delete(state.craftId.apps, appHandle);
};

export const RECEIVE_CRAFT_ID_DATA = (state, {response}) => {
    state.craftId = {
        apps: response.data.apps,
        billingAddress: response.data.billingAddress,
        categories: response.data.categories,
        cmsLicenses: response.data.cmsLicenses,
        countries: response.data.countries,
        currentUser: response.data.currentUser,
        customers: response.data.customers,
        enableRenewalFeatures: response.data.enableRenewalFeatures,
        pluginLicenses: response.data.pluginLicenses,
        sales: response.data.sales,
    }
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

export const UPLOAD_USER_PHOTO = (state, {response}) => {
    state.craftId.currentUser.photoId = response.data.photoId;
    state.craftId.currentUser.photoUrl = response.data.photoUrl;
};


export const SAVE_BILLING_INFO = (state, {response}) => {
    state.craftId.billingAddress = response.data.address
};


/**
 * Licenses
 */

export const CLAIM_CMS_LICENSE = (state, {licenseKey}) => {
};

export const CLAIM_CMS_LICENSE_FILE = (state, {licenseFile}) => {
};

export const CLAIM_PLUGIN_LICENSE = (state, {licenseKey}) => {
};

export const GENERATE_API_TOKEN = (state, {response}) => {
    state.craftId.currentUser.hasApiToken = response.data.apiToken;
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

export const SAVE_PLUGIN_LICENSE = (state, {license}) => {
    let stateLicense = state.craftId.pluginLicenses.find(l => l.key == license.key);
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


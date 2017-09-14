import * as types from './mutation-types'

export const SAVE_CARD = (state, { data }) => {
    state.stripeCard = data.card
};

export const REMOVE_CARD = (state, { data }) => {
    state.stripeCard = null
};

export const RECEIVE_STRIPE_CUSTOMER = (state, { data }) => {
    state.stripeCustomer = data.customer
};

export const RECEIVE_STRIPE_CARD = (state, { data }) => {
    state.stripeCard = data.card
};

export const RECEIVE_STRIPE_ACCOUNT = (state, { data }) => {
    state.stripeAccount = data
};

export const DISCONNECT_STRIPE_ACCOUNT = (state, { data }) => {
    state.stripeAccount = null
};

export const RECEIVE_CRAFT_ID_DATA = (state, { data }) => {
    state.craftId = data
};

export const SAVE_USER = (state, {user, response}) => {
    for (let attribute in user) {
        if(attribute == 'id') {
            continue;
        }

        for (let attribute in user) {
            state.craftId.currentUser[attribute] = user[attribute];
        }
    }
};

export const SAVE_LICENSE = (state, {license, response}) => {
    let stateLicense = null;
    if(license.type === 'craftLicense') {
        stateLicense = state.craftId.craftLicenses.find(l => l.id == license.id);
    } else if(license.type === 'pluginLicense') {
        stateLicense = state.craftId.pluginLicenses.find(l => l.id == license.id);
    }

    for (let attribute in license) {
        switch(attribute) {
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

export const SAVE_PLUGIN = (state, {plugin, response}) => {
    let newPlugin = false;
    let statePlugin = state.craftId.plugins.find(p => p.id == plugin.id);

    if(!statePlugin) {
        statePlugin = {
            id: response.body.id,
        };
        newPlugin = true;
    }

    for (let attribute in plugin) {
        statePlugin[attribute] = plugin[attribute];
    }

    if(newPlugin) {
        state.craftId.plugins.push(statePlugin);
    }
};

export const SAVE_CRAFT_ID_DATA = (state) => {
};

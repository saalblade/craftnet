import * as types from './mutation-types'

export const SAVE_CARD = (state, {data}) => {
    state.stripeCard = data.card
};

export const REMOVE_CARD = (state, {data}) => {
    state.stripeCard = null
};

export const RECEIVE_STRIPE_CUSTOMER = (state, {data}) => {
    state.stripeCustomer = data.customer
};

export const RECEIVE_STRIPE_CARD = (state, {data}) => {
    state.stripeCard = data.card
};

export const RECEIVE_STRIPE_ACCOUNT = (state, {data}) => {
    state.stripeAccount = data
};

export const DISCONNECT_STRIPE_ACCOUNT = (state, {data}) => {
    state.stripeAccount = null
};

export const RECEIVE_CRAFT_ID_DATA = (state, {data}) => {
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

        if(user.enablePluginDeveloperFeatures) {
            let groupExists = state.craftId.currentUser.groups.find(g => g.handle === 'developers');

            if(!groupExists) {
                state.craftId.currentUser.groups.push({
                    id: 1,
                    name: 'Developers',
                    handle: 'developers',
                })
            }
        }
    }
};

export const UPLOAD_USER_PHOTO = (state, {formData, data}) => {

    state.craftId.currentUser.photoId = data.photoId;
    state.craftId.currentUser.photoUrl = data.photoUrl;
};

export const DELETE_USER_PHOTO = (state, {formData, data}) => {
    state.craftId.currentUser.photoId = data.photoId;
    state.craftId.currentUser.photoUrl = data.photoUrl;
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

export const SAVE_PLUGIN = (state, {formData, data}) => {
    let newPlugin = false;
    let statePlugin = state.craftId.plugins.find(p => p.id == formData.get('pluginId'));

    if(!statePlugin) {
        statePlugin = {
            id: data.id,
        };
        newPlugin = true;
    }

    statePlugin.siteId = formData.get('siteId');
    statePlugin.enabled = formData.get('enabled');
    statePlugin.pluginId = data.id;
    statePlugin.icon = formData.get('icon');
    statePlugin.iconUrl = data.iconUrl+'?'+ Math.floor(Math.random() * 1000000);
    statePlugin.iconId = data.iconId;
    statePlugin.developerId = formData.get('developerId');
    statePlugin.developerName = formData.get('developerName');
    statePlugin.handle = formData.get('handle');
    statePlugin.packageName = formData.get('packageName');
    statePlugin.name = formData.get('name');
    statePlugin.shortDescription = formData.get('shortDescription');
    statePlugin.longDescription = formData.get('longDescription');
    statePlugin.documentationUrl = formData.get('documentationUrl');
    statePlugin.changelogUrl = formData.get('changelogUrl');
    // statePlugin.repository = formData.get('repository');
    statePlugin.license = formData.get('license');
    statePlugin.price = (formData.get('price') ? formData.get('price') : '');
    statePlugin.renewalPrice = (formData.get('renewalPrice') ? formData.get('renewalPrice') : '');
    statePlugin.categoryIds = formData.getAll('categoryIds[]');

    let screenshotIds = [];
    let screenshotUrls = [];

    if(data.screenshots.length > 0) {
        for(let i = 0; i < data.screenshots.length; i++) {
            screenshotIds.push(data.screenshots[i].id);
            screenshotUrls.push(data.screenshots[i].url);
        }
    }

    statePlugin.screenshotIds = screenshotIds;
    statePlugin.screenshotUrls = screenshotUrls;

    /*statePlugin.screenshotIds = formData.getAll('screenshotIds[]');
    statePlugin.screenshotUrls = formData.getAll('screenshotUrls[]');*/

    if(newPlugin) {
        state.craftId.plugins.push(statePlugin);
    }
};

export const SAVE_CRAFT_ID_DATA = (state) => {
};

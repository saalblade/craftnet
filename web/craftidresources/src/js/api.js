import axios from 'axios';
import qs from 'qs';

export default {

    getCraftIdData(userId, cb, cbError) {
        let params = qs.stringify({
            userId: userId
        });

        axios.post(Craft.actionUrl+'/craftcom/id/craft-id', params)
            .then(response => cb(response))
            .catch(error => cbError(error.response));
    },

    disconnectApp(appHandle, cb, cbError) {
        let data = {};
        data['appTypeHandle'] = appHandle;
        data[Craft.csrfTokenName] = Craft.csrfTokenValue;

        let params = qs.stringify(data);

        axios.post(Craft.actionUrl+'/craftcom/id/apps/disconnect', params)
            .then(response => cb(response))
            .catch(error => cbError(error.response));
    },

    saveUser(user, cb, cbError) {
        let formData = new FormData();

        for (let attribute in user) {
            switch (attribute) {
                case 'id':
                    formData.append('userId', user[attribute]);
                    break;
                case 'email':
                case 'username':
                case 'firstName':
                case 'lastName':
                case 'password':
                case 'newPassword':
                case 'photo':
                    formData.append(attribute, user[attribute]);
                    break;
                default:
                    formData.append('fields['+attribute+']', user[attribute]);
            }
        }

        formData.append(Craft.csrfTokenName, Craft.csrfTokenValue);

        axios.post(Craft.actionUrl+'/users/save-user', formData)
            .then(response => cb(response))
            .catch(error => cbError(error.response));
    },

    uploadUserPhoto(data, cb, cbError) {
        let formData = new FormData();

        for(let dataKey in data) {
            formData.append(dataKey, data[dataKey]);
        }

        formData.append(Craft.csrfTokenName, Craft.csrfTokenValue);

        axios.post(Craft.actionUrl+'/craftcom/id/account/upload-user-photo', formData)
            .then(response => cb(response))
            .catch(error => cbError(error.response));
    },

    deleteUserPhoto(data, cb, cbError) {
        data['action'] = 'craftcom/id/account/delete-user-photo';
        data[Craft.csrfTokenName] = Craft.csrfTokenValue;

        let params = qs.stringify(data);

        axios.post(Craft.actionUrl+'/craftcom/id/account/delete-user-photo', params)
            .then(response => cb(response))
            .catch(error => cbError(error.response));
    },

    getStripeAccount(cb, cbError) {
        axios.get(window.craftIdUrl+'/stripe/account')
            .then(response => cb(response))
            .catch(error => cbError(error.response));
    },

    getStripeCustomer(cb, cbError) {
        axios.get(window.craftIdUrl+'/stripe/customer')
            .then(response => cb(response))
            .catch(error => cbError(error.response));
    },

    disconnectStripeAccount(cb, cbError) {
        axios.post(window.craftIdUrl+'/stripe/disconnect')
            .then(response => cb(response))
            .catch(error => cbError(error.response));
    },

    saveCard(token, cb, cbError) {
        let data = {
            token: token.id
        };

        let params = qs.stringify(data);

        axios.post(window.craftIdUrl+'/stripe/save-card', params)
            .then(response => cb(response))
            .catch(error => cbError(error.response));
    },

    removeCard(cb, cbError) {
        axios.post(window.craftIdUrl+'/stripe/remove-card')
            .then(response => cb(response))
            .catch(error => cbError(error.response));
    },

    saveLicense(license, cb, cbError) {
        let body = {
            entryId: license.id,
            siteId: 1,
            sectionId: 2,
            enabled: 1,
            fields: {}
        };

        for (let attribute in license) {
            switch (attribute) {
                case 'entryId':
                    // ignore
                    break;
                case 'title':
                    body[attribute] = license[attribute];
                    break;
                default:
                    body['fields'][attribute] = license[attribute];
            }
        }

        body['action'] = 'entries/save-entry';
        body[Craft.csrfTokenName] = Craft.csrfTokenValue;

        let params = qs.stringify(body);
        axios.post(Craft.actionUrl+'/entries/save-entry', params)
            .then(response => cb(response))
            .catch(error => cbError(error.response));
    },

    saveCmsLicense(license, cb, cbError) {
        let data = {
            [Craft.csrfTokenName]: Craft.csrfTokenValue
        };

        for(let attribute in license) {
            data[attribute] = license[attribute];
        }

        let qsData = qs.stringify(data);

        axios.post(Craft.actionUrl+'/craftcom/id/cms-licenses/save', qsData)
            .then(response => cb(response))
            .catch(error => cbError(error.response));
    },

    savePlugin({plugin}, cb, cbError) {
        let formData = new FormData();

        for(let pluginKey in plugin) {
            if(plugin[pluginKey] !== null && plugin[pluginKey] !== undefined) {
                switch(pluginKey) {
                    case 'iconId':
                    case 'categoryIds':
                    case 'screenshots':
                    case 'screenshotUrls':
                    case 'screenshotIds':
                        for(let i = 0; i < plugin[pluginKey].length; i++) {
                            formData.append(pluginKey+'[]', plugin[pluginKey][i]);
                        }
                        break;

                    default:
                            formData.append(pluginKey, plugin[pluginKey]);
                }
            }
        }

        formData.append(Craft.csrfTokenName, Craft.csrfTokenValue);

        axios.post(Craft.actionUrl+'/craftcom/plugins/save', formData)
            .then(response => cb(response))
            .catch(error => cbError(error.response));
    },

    submitPlugin(pluginId, cb, cbError) {
        let data = {
            pluginId: pluginId,
        };

        data[Craft.csrfTokenName] = Craft.csrfTokenValue;

        let params = qs.stringify(data);

        axios.post(Craft.actionUrl+'/craftcom/plugins/submit', params)
            .then(response => cb(response))
            .catch(error => cbError(error.response));
    }

}
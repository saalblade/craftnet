import axios from 'axios';
import qs from 'qs';

export default {

    getCraftIdData(userId, cb, cbError) {
        let params = qs.stringify({
            userId: userId
        });

        axios.post(window.craftActionUrl+'/craftcom/id/craft-id', params)
            .then(response => cb(response.data))
            .catch(response => cbError(response));
    },

    disconnectApp(appHandle, cb, cbError) {
        let formData = new FormData();
        formData.append('appTypeHandle', appHandle);
        formData.append(csrfTokenName, csrfTokenValue);

        axios.post(window.craftActionUrl+'/craftcom/id/apps/disconnect', formData)
            .then(response => cb(response.data))
            .catch(response => cbError(response));
    },

    saveUser(user, cb, cbError) {
        let formData = new FormData();

        for (let attribute in user) {
            switch (attribute) {
                case 'id':
                    formData.append('userId', user[attribute]);
                    break;
                case 'email':
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

        formData.append('action', 'users/save-user');
        formData.append(csrfTokenName, csrfTokenValue);

        axios.post(window.craftActionUrl+'/users/save-user', formData)
            .then(response => cb(response.data))
            .catch(response => cbError(response.data));
    },

    uploadUserPhoto(formData, cb, cbError) {
        formData.append('action', 'craftcom/id/account/upload-user-photo');
        formData.append(csrfTokenName, csrfTokenValue);

        axios.post(window.craftActionUrl+'/craftcom/id/account/upload-user-photo', formData)
            .then(response => cb(response.data))
            .catch(response => cbError(response));
    },

    deleteUserPhoto(formData, cb, cbError) {
        formData.append('action', 'craftcom/id/account/delete-user-photo');
        formData.append(csrfTokenName, csrfTokenValue);

        axios.post(window.craftActionUrl+'/craftcom/id/account/delete-user-photo', formData)
            .then(response => cb(response.data))
            .catch(response => cbError(response));
    },

    getStripeAccount(cb, cbError) {
        axios.get(window.craftIdUrl+'/stripe/account')
            .then(response => cb(response.data))
            .catch(response => cbError(response));
    },

    getStripeCustomer(cb, cbError) {
        axios.get(window.craftIdUrl+'/stripe/customer')
            .then(response => cb(response.data))
            .catch(response => cbError(response));
    },

    disconnectStripeAccount(cb, cbError) {
        axios.post(window.craftIdUrl+'/stripe/disconnect')
            .then(response => cb(response.data))
            .catch(response => cbError(response));
    },

    saveCard(token, cb, cbError) {
        let formData = new FormData();
        formData.append('token', token.id);

        axios.post(window.craftIdUrl+'/stripe/save-card', formData)
            .then(response => cb(response.data))
            .catch(response => cbError(response));
    },

    removeCard(cb, cbError) {
        axios.post(window.craftIdUrl+'/stripe/remove-card')
            .then(response => cb(response.data))
            .catch(response => cbError(response));
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
        body[csrfTokenName] = csrfTokenValue;

        let params = qs.stringify(body);
        axios.post(window.craftActionUrl+'/entries/save-entry', params)
            .then(response => cb(response.data))
            .catch(response => cbError(response));
    },

    savePlugin(formData, cb, cbError) {
        formData.append('action', 'craftcom/plugins/save');
        formData.append(csrfTokenName, csrfTokenValue);

        axios.post(window.craftActionUrl+'/craftcom/plugins/save', formData)
            .then(response => cb(response.data))
            .catch(response => cbError(response));
    },

    submitPlugin(pluginId, cb, cbError) {
        let formData = new FormData();
        formData.append('pluginId', pluginId);
        formData.append(csrfTokenName, csrfTokenValue);

        axios.post(window.craftActionUrl+'/craftcom/plugins/submit', formData)
            .then(response => cb(response.data))
            .catch(response => cbError(response));
    }

}
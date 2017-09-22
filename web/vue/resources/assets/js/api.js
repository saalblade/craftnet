import Vue from 'vue'
import Vuex from 'vuex'

Vue.use(Vuex);

export default {
    getCraftIdData(userId, cb, cbError) {
        let body = { userId: userId };
        let options = { emulateJSON: true };

        Vue.http.post(window.craftApiUrl+'/craft-id', body, options)
            .then(response => cb(response.body))
            .catch(response => cbError(response));
    },

    saveUser(user, cb, cbError) {
        let body = {
            userId: user.id,
            fields: {},
        };

        for (let attribute in user) {
            switch (attribute) {
                case 'userId':
                    // ignore
                    break;
                case 'firstName':
                case 'lastName':
                    body[attribute] = user[attribute];
                    break;
                default:
                    body['fields'][attribute] = user[attribute];
            }
        }

        body['action'] = 'users/save-user';
        body[csrfTokenName] = csrfTokenValue;

        let options = { emulateJSON: true };

        Vue.http.post(window.craftActionUrl+'/users/save-user', body, options)
            .then(response => cb(response.body))
            .catch(response => cbError(response));
    },

    getStripeAccount(cb, cbError) {
        Vue.http.get(window.craftIdUrl+'/stripe/account')
            .then(response => cb(response.body))
            .catch(response => cbError(response));
    },

    getStripeCustomer(cb, cbError) {
        Vue.http.get(window.craftIdUrl+'/stripe/customer')
            .then(response => cb(response.body))
            .catch(response => cbError(response));
    },

    disconnectStripeAccount(cb, cbError) {
        let options = { emulateJSON: true };

        Vue.http.post(window.craftIdUrl+'/stripe/disconnect', options)
            .then(response => cb(response.body))
            .catch(response => cbError(response));
    },

    saveCard(token, cb, cbError) {
        let body = { token: token.id };
        let options = { emulateJSON: true };

        Vue.http.post(window.craftIdUrl+'/stripe/save-card', body, options)
            .then(response => cb(response.body))
            .catch(response => cbError(response));
    },

    removeCard(cb, cbError) {
        let body = {};
        let options = { emulateJSON: true };

        Vue.http.post(window.craftIdUrl+'/stripe/remove-card', body, options)
            .then(response => cb(response.body))
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

        let options = { emulateJSON: true };

        Vue.http.post(window.craftActionUrl+'/entries/save-entry', body, options)
            .then(response => cb(response.body))
            .catch(response => cbError(response));
    },

    savePlugin(plugin, cb, cbError) {
        let body = {
            pluginId: null,
            siteId: 1,
            enabled: 1,
            fields: {}
        };

        for (let attribute in plugin) {
            switch (attribute) {
                case 'id':
                    body['pluginId'] = plugin[attribute];
                    break;
                default:
                    body[attribute] = plugin[attribute];
            }
        }

        body['action'] = 'craftcom/plugins/save';
        body[csrfTokenName] = csrfTokenValue;

        let options = { emulateJSON: true };

        Vue.http.post(window.craftActionUrl+'/craftcom/plugins/save', body, options)
            .then(responsex => cb(responsex.body))
            .catch(responsey => cbError(responsey));
    }
}
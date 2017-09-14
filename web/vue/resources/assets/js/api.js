import Vue from 'vue'
import Vuex from 'vuex'

Vue.use(Vuex);

export default {
    getCraftIdData(userId, cb, cbError) {
        let body = { userId: userId };
        let options = { emulateJSON: true };

        Vue.http.post(window.craftApiUrl+'/craft-id', body, options)
            .then(response => {
                let data = response.body;

                data['payouts'] = [
                    {
                        id: 1,
                        amount: 99.00,
                        date: '1 year ago',
                        bank: {
                            name: 'BNP Parisbas',
                            accountNumber: '2345678923456783456',
                        }
                    },
                    {
                        id: 2,
                        amount: 99.00,
                        date: '1 year ago',
                        bank: {
                            name: 'BNP Parisbas',
                            accountNumber: '2345678923456783456',
                        }
                    },
                    {
                        id: 3,
                        amount: 298.00,
                        date: '1 year ago',
                        bank: {
                            name: 'BNP Parisbas',
                            accountNumber: '2345678923456783456',
                        }
                    },
                ];
                data['payoutsScheduled'] = [
                    {
                        id: 8,
                        amount: 116.00,
                        date: 'Tomorrow',
                    },
                ];

                data['payments'] = [
                    {
                        items: [{id: 6, name: 'Analytics'}],
                        amount: 99.00,
                        customer: {
                            id: 1,
                            name: 'Benjamin David',
                            email: 'ben@pixelandtonic.com',
                        },
                        date: '3 days ago',
                    },
                    {
                        items: [{id: 6, name: 'Analytics'}],
                        amount: 99.00,
                        customer: {
                            id: 15,
                            name: 'Andrew Welsh',
                            email: 'andrew@nystudio107.com',
                        },
                        date: '1 year ago',
                    },
                    {
                        items: [{id: 7, name: 'Videos'}],
                        amount: 99.00,
                        customer: {
                            id: 15,
                            name: 'Andrew Welsh',
                            email: 'andrew@nystudio107.com',
                        },
                        date: '1 year ago',
                    },
                    {
                        items: [{id: 6, name: 'Analytics'}, {id: 7, name: 'Videos'}],
                        amount: 298.00,
                        customer: {
                            id: 15,
                            name: 'Andrew Welsh',
                            email: 'andrew@nystudio107.com',
                        },
                        date: '1 year ago',
                    },
                ];

                return cb(data);
            })
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
            entryId: null,
            siteId: 1,
            sectionId: 1,
            enabled: 1,
            fields: {}
        };

        for (let attribute in plugin) {
            switch (attribute) {
                case 'id':
                    body['entryId'] = plugin[attribute];
                    break;
                case 'title':
                    body[attribute] = plugin[attribute];
                    break;
                default:
                    body['fields'][attribute] = plugin[attribute];
            }
        }

        body['action'] = 'entries/save-entry';
        body[csrfTokenName] = csrfTokenValue;

        let options = { emulateJSON: true };

        Vue.http.post(window.craftActionUrl+'/entries/save-entry', body, options)
            .then(response => cb(response.body))
            .catch(response => cbError(response));
    }
}
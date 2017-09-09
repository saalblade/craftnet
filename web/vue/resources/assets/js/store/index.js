import Vue from 'vue'
import Vuex from 'vuex'

Vue.use(Vuex);

export default new Vuex.Store({
    strict: true,
    state: {
        craftId: null,
        stripeAccount: null,
        stripeCustomer: null,
        stripeCard: null,
    },
    getters: {
        craftId: state => {
            return state.craftId;
        },
        stripeAccount: state => {
            return state.stripeAccount;
        },
        stripeCard: state => {
            return state.stripeCard;
        },
        stripeCustomer: state => {
            return state.stripeCustomer;
        },
        craftLicenses: state => {
            if(state.craftId) {
                return state.craftId.craftLicenses;
            }
        },
        currentUser: state => {
            if(state.craftId) {
                return state.craftId.currentUser;
            }
        },
        customers: state => {
            if(state.craftId) {
                return state.craftId.customers;
            }
        },
        licenses: state => {
            if(state.craftId) {
                return state.craftId.pluginLicenses.concat(state.craftId.craftLicenses);
            }
        },
        payments: state => {
            if(state.craftId) {
                return state.craftId.payments;
            }
        },
        payouts: state => {
            if(state.craftId) {
                return state.craftId.payouts;
            }
        },
        payoutsScheduled: state => {
            if(state.craftId) {
                return state.craftId.payoutsScheduled;
            }
        },
        pluginLicenses: state => {
            if(state.craftId) {
                return state.craftId.pluginLicenses;
            }
        },
        plugins: state => {
            if(state.craftId) {
                return state.craftId.plugins;
            }
        },
    },
    actions: {
        getCraftIdData ({ commit }) {
            return new Promise((resolve, reject) => {
                let params = {
                    userId: window.currentUserId
                };

                Vue.http.post(window.craftApiUrl+'/craft-id', params, {emulateJSON: true}).then(function(response) {

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

                    commit('RECEIVE_CRAFT_ID_DATA', { data })
                    resolve(data);
                });
            })
        },

        saveUser({ commit, state }, user) {
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

            return new Promise((resolve, reject) => {
                Vue.http.post(window.craftActionUrl+'/users/save-user', body, { emulateJSON: true })
                    .then(response => {
                        let data = response.body;

                        if(!data.errors) {
                            console.log('response', response.body);
                            commit('SAVE_USER', { user, response });
                            resolve(response);
                        } else {
                            console.log('error');
                            reject(data);
                        }
                    })
                    .catch(response => reject(response));
            })
        },

        getStripeAccount({commit}) {
            return new Promise((resolve, reject) => {
                Vue.http.get(window.craftIdUrl+'/stripe/account').then(function(response) {
                    let data = response.body;

                    commit('RECEIVE_STRIPE_ACCOUNT', { data })
                    resolve(data);
                }, error => {
                    reject(error);
                });
            })
        },

        getStripeCustomer({commit}) {
            return new Promise((resolve, reject) => {
                Vue.http.get(window.craftIdUrl+'/stripe/customer').then(function(response) {
                    let data = response.body;

                    commit('RECEIVE_STRIPE_CUSTOMER', { data })
                    commit('RECEIVE_STRIPE_CARD', { data })
                    resolve(data);
                }, error => {
                    reject(error);
                });
            })
        },

        disconnectStripeAccount({commit}) {
            return new Promise((resolve, reject) => {
                Vue.http.post(window.craftIdUrl+'/stripe/disconnect', { emulateJSON: true }).then(function(response) {
                    let data = response.body;

                    commit('DISCONNECT_STRIPE_ACCOUNT', { data })
                    resolve(data);
                });
            })
        },

        saveCreditCard({commit}, token) {
            return new Promise((resolve, reject) => {
                let body = {
                    token: token.id
                };

                Vue.http.post(window.craftIdUrl+'/stripe/save-credit-card', body, { emulateJSON: true })
                    .then(response => {
                        let data = response.body;
                        commit('SAVE_CARD', { data })
                        resolve(data);
                    })
                    .catch(response => {
                        reject(response)
                    });
            })
        },

        removeCreditCard({commit}) {
            return new Promise((resolve, reject) => {
                let body = {};

                Vue.http.post(window.craftIdUrl+'/stripe/remove-credit-card', body, { emulateJSON: true })
                    .then(response => {
                        let data = response.body;
                        commit('REMOVE_CARD', { data })
                        resolve(data);
                    })
                    .catch(response => {
                        reject(response)
                    });
            })
        },

        saveLicense({ commit, state }, license) {
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

            return new Promise((resolve, reject) => {
                Vue.http.post(window.craftActionUrl+'/entries/save-entry', body, { emulateJSON: true })
                    .then(response => {
                        let data = response.body;

                        if(!data.errors) {
                            commit('SAVE_LICENSE', { license, response });
                            resolve(response);
                        } else {
                            console.log('error', data);
                            reject(data);
                        }
                    })
                    .catch(response => reject(response));
            })
        },

        savePlugin({ commit, state }, plugin) {
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

            console.log('body', body);
            body['action'] = 'entries/save-entry';
            body[csrfTokenName] = csrfTokenValue;

            return new Promise((resolve, reject) => {
                Vue.http.post(window.craftActionUrl+'/entries/save-entry', body, { emulateJSON: true })
                    .then(response => {
                        let data = response.body;

                        if(!data.errors) {
                            commit('SAVE_PLUGIN', { plugin, response });
                            resolve(response);
                        } else {
                            console.log('error');
                            reject(data);
                        }
                    })
                    .catch(response => reject(response));
            })
        },
    },

    mutations: {

        ['SAVE_CARD'] (state, { data }) {
            state.stripeCard = data.card
        },
        ['REMOVE_CARD'] (state, { data }) {
            state.stripeCard = null
        },

        ['RECEIVE_STRIPE_CUSTOMER'] (state, { data }) {
            state.stripeCustomer = data.customer
        },

        ['RECEIVE_STRIPE_CARD'] (state, { data }) {
            state.stripeCard = data.card
        },

        ['RECEIVE_STRIPE_ACCOUNT'] (state, { data }) {
            state.stripeAccount = data
        },


        ['DISCONNECT_STRIPE_ACCOUNT'] (state, { data }) {
            state.stripeAccount = null
        },


        ['RECEIVE_CRAFT_ID_DATA'] (state, { data }) {
            state.craftId = data
        },

        ['SAVE_USER'] (state, {user, response}) {
            for (let attribute in user) {
                if(attribute == 'id') {
                    continue;
                }

                for (let attribute in user) {
                    state.craftId.currentUser[attribute] = user[attribute];
                }
            }
        },

        ['SAVE_LICENSE'] (state, {license, response}) {
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
        },

        ['SAVE_PLUGIN'] (state, {plugin, response}) {
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
        },

        ['SAVE_CRAFT_ID_DATA'] (state) {
        },
    },
})

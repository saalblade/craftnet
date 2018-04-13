import axios from 'axios';
import qs from 'qs';

export default {

    getCraftIdData(cb, cbError) {
        axios.post(Craft.actionUrl + '/craftnet/id/craft-id')
            .then(response => cb(response))
            .catch(error => cbError(error.response));
    },

    disconnectApp(appHandle, cb, cbError) {
        const data = {
            appTypeHandle: appHandle
        }

        axios.post(Craft.actionUrl + '/craftnet/id/apps/disconnect', qs.stringify(data))
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
                    formData.append('fields[' + attribute + ']', user[attribute]);
            }
        }

        axios.post(Craft.actionUrl + '/users/save-user', formData, {
                headers: {
                    'X-CSRF-Token':  Craft.csrfTokenValue,
                }
            })
            .then(response => cb(response))
            .catch(error => cbError(error.response));
    },

    saveBillingInfo(data, cb, cbError) {
        axios.post(Craft.actionUrl + '/craftnet/id/account/save-billing-info', qs.stringify(data), {
                headers: {
                    'X-CSRF-Token':  Craft.csrfTokenValue,
                }
            })
            .then(response => cb(response))
            .catch(error => cbError(error.response))
    },

    uploadUserPhoto(data, cb, cbError) {
        let formData = new FormData();

        for (let attribute in data) {
            formData.append(attribute, data[attribute]);
        }

        axios.post(Craft.actionUrl + '/craftnet/id/account/upload-user-photo', formData, {
                headers: {
                    'X-CSRF-Token':  Craft.csrfTokenValue,
                }
            })
            .then(response => cb(response))
            .catch(error => cbError(error.response));
    },

    deleteUserPhoto(cb, cbError) {
        axios.post(Craft.actionUrl + '/craftnet/id/account/delete-user-photo', {}, {
                headers: {
                    'X-CSRF-Token': Craft.csrfTokenValue,
                }
            })
            .then(response => cb(response))
            .catch(error => cbError(error.response));
    },

    getStripeAccount(cb, cbError) {
        axios.get(window.craftIdUrl + '/stripe/account')
            .then(response => cb(response))
            .catch(error => cbError(error.response));
    },

    getStripeCustomer(cb, cbError) {
        axios.get(window.craftIdUrl + '/stripe/customer')
            .then(response => cb(response))
            .catch(error => cbError(error.response));
    },

    disconnectStripeAccount(cb, cbError) {
        axios.post(window.craftIdUrl + '/stripe/disconnect')
            .then(response => cb(response))
            .catch(error => cbError(error.response));
    },

    saveCard(source, cb, cbError) {
        const data = {
            token: source.id
        }

        axios.post(window.craftIdUrl + '/stripe/save-card', qs.stringify(data))
            .then(response => cb(response))
            .catch(error => cbError(error.response));
    },

    removeCard(cb, cbError) {
        axios.post(window.craftIdUrl + '/stripe/remove-card')
            .then(response => cb(response))
            .catch(error => cbError(error.response));
    },

    getInvoices(cb, cbError) {
        axios.get(Craft.actionUrl + '/craftnet/id/account/get-invoices')
            .then(response => cb(response))
            .catch(error => cbError(error.response));
    },
}

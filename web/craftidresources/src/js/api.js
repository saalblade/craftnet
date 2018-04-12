import axios from 'axios';
import qs from 'qs';

export default {

    getCraftIdData(userId, cb, cbError) {
        axios.post(Craft.actionUrl + '/craftnet/id/craft-id')
            .then(response => cb(response))
            .catch(error => cbError(error.response));
    },

    disconnectApp(appHandle, cb, cbError) {
        let data = {};
        data['appTypeHandle'] = appHandle;
        data[Craft.csrfTokenName] = Craft.csrfTokenValue;

        let params = qs.stringify(data);

        axios.post(Craft.actionUrl + '/craftnet/id/apps/disconnect', params)
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

        formData.append(Craft.csrfTokenName, Craft.csrfTokenValue);

        axios.post(Craft.actionUrl + '/users/save-user', formData)
            .then(response => cb(response))
            .catch(error => cbError(error.response));
    },

    saveBillingInfo(data, cb, cbError) {
        axios.post(Craft.actionUrl + '/craftnet/id/account/save-billing-info', data, {
                headers: {
                    'X-CSRF-Token':  Craft.csrfTokenValue,
                }
            })
            .then(response => cb(response))
            .catch(error => cbError(error.response));
    },

    uploadUserPhoto(data, cb, cbError) {
        let formData = new FormData();

        for (let dataKey in data) {
            formData.append(dataKey, data[dataKey]);
        }

        formData.append(Craft.csrfTokenName, Craft.csrfTokenValue);

        axios.post(Craft.actionUrl + '/craftnet/id/account/upload-user-photo', formData)
            .then(response => cb(response))
            .catch(error => cbError(error.response));
    },

    deleteUserPhoto(cb, cbError) {
        const data = qs.stringify({
            [Craft.csrfTokenName]: Craft.csrfTokenValue
        });

        axios.post(Craft.actionUrl + '/craftnet/id/account/delete-user-photo', data)
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
        let data = {
            token: source.id
        };

        let qsData = qs.stringify(data);

        axios.post(window.craftIdUrl + '/stripe/save-card', qsData)
            .then(response => cb(response))
            .catch(error => cbError(error.response));
    },

    removeCard(cb, cbError) {
        axios.post(window.craftIdUrl + '/stripe/remove-card')
            .then(response => cb(response))
            .catch(error => cbError(error.response));
    },

    claimCmsLicense(licenseKey, cb, cbError) {
        let data = {
            key: licenseKey,
            [Craft.csrfTokenName]: Craft.csrfTokenValue
        }

        let qsData = qs.stringify(data);

        axios.post(Craft.actionUrl + '/craftnet/id/cms-licenses/claim', qsData)
            .then(response => cb(response))
            .catch(error => cbError(error.response));
    },

    claimCmsLicenseFile(licenseFile, cb, cbError) {
        let formData = new FormData();
        formData.append('licenseFile', licenseFile);
        formData.append(Craft.csrfTokenName, Craft.csrfTokenValue);

        axios.post(Craft.actionUrl + '/craftnet/id/cms-licenses/claim', formData)
            .then(response => cb(response))
            .catch(error => cbError(error.response));
    },

    claimLicensesByEmail(email, cb, cbError) {
        let data = {
            email: email,
            [Craft.csrfTokenName]: Craft.csrfTokenValue
        }

        let qsData = qs.stringify(data);

        axios.post(Craft.actionUrl + '/craftnet/id/claim-licenses', qsData)
            .then(response => cb(response))
            .catch(error => cbError(error.response));
    },

    claimPluginLicense(licenseKey, cb, cbError) {
        let data = {
            key: licenseKey,
            [Craft.csrfTokenName]: Craft.csrfTokenValue
        }

        let qsData = qs.stringify(data);

        axios.post(Craft.actionUrl + '/craftnet/id/plugin-licenses/claim', qsData)
            .then(response => cb(response))
            .catch(error => cbError(error.response));
    },

    generateApiToken(cb, cbError) {
        let data = {
            [Craft.csrfTokenName]: Craft.csrfTokenValue
        }

        let qsData = qs.stringify(data);

        axios.post(Craft.actionUrl + '/craftnet/id/account/generate-api-token', qsData)
            .then(response => cb(response))
            .catch(error => cbError(error.response));
    },

    getCmsLicenses(cb, cbError) {
        let data = {
            [Craft.csrfTokenName]: Craft.csrfTokenValue
        }

        let qsData = qs.stringify(data);

        axios.post(Craft.actionUrl + '/craftnet/id/cms-licenses/get-licenses', qsData)
            .then(response => cb(response))
            .catch(error => cbError(error.response));
    },

    getPluginLicenses(cb, cbError) {
        let data = {
            [Craft.csrfTokenName]: Craft.csrfTokenValue
        }

        let qsData = qs.stringify(data);

        axios.post(Craft.actionUrl + '/craftnet/id/plugin-licenses/get-licenses', qsData)
            .then(response => cb(response))
            .catch(error => cbError(error.response));
    },

    releaseCmsLicense(licenseKey, cb, cbError) {
        let data = {
            key: licenseKey,
            [Craft.csrfTokenName]: Craft.csrfTokenValue
        }

        let qsData = qs.stringify(data);

        axios.post(Craft.actionUrl + '/craftnet/id/cms-licenses/release', qsData)
            .then(response => cb(response))
            .catch(error => cbError(error.response));
    },

    releasePluginLicense({pluginHandle, licenseKey}, cb, cbError) {
        let data = {
            handle: pluginHandle,
            key: licenseKey,
            [Craft.csrfTokenName]: Craft.csrfTokenValue
        }

        let qsData = qs.stringify(data);

        axios.post(Craft.actionUrl + '/craftnet/id/plugin-licenses/release', qsData)
            .then(response => cb(response))
            .catch(error => cbError(error.response));
    },

    saveCmsLicense(license, cb, cbError) {
        let data = {
            [Craft.csrfTokenName]: Craft.csrfTokenValue
        };

        for (let attribute in license) {
            data[attribute] = license[attribute];
        }

        let qsData = qs.stringify(data);

        axios.post(Craft.actionUrl + '/craftnet/id/cms-licenses/save', qsData)
            .then(response => cb(response))
            .catch(error => cbError(error.response));
    },

    savePluginLicense(license, cb, cbError) {
        let data = {
            [Craft.csrfTokenName]: Craft.csrfTokenValue
        };

        for (let attribute in license) {
            if (attribute !== 'cmsLicense') {
                data[attribute] = license[attribute];
            }
        }

        let qsData = qs.stringify(data);

        axios.post(Craft.actionUrl + '/craftnet/id/plugin-licenses/save', qsData)
            .then(response => cb(response))
            .catch(error => cbError(error.response));
    },

    getInvoices(cb, cbError) {
        let data = {
            [Craft.csrfTokenName]: Craft.csrfTokenValue
        }

        let qsData = qs.stringify(data);

        axios.post(Craft.actionUrl + '/craftnet/id/account/get-invoices', qsData)
            .then(response => cb(response))
            .catch(error => cbError(error.response));
    },

    savePlugin({plugin}, cb, cbError) {
        let formData = new FormData();

        for (let pluginKey in plugin) {
            if (plugin[pluginKey] !== null && plugin[pluginKey] !== undefined) {
                switch (pluginKey) {
                    case 'iconId':
                    case 'categoryIds':
                    case 'screenshots':
                    case 'screenshotUrls':
                    case 'screenshotIds':
                        for (let i = 0; i < plugin[pluginKey].length; i++) {
                            formData.append(pluginKey + '[]', plugin[pluginKey][i]);
                        }
                        break;

                    default:
                        formData.append(pluginKey, plugin[pluginKey]);
                }
            }
        }

        formData.append(Craft.csrfTokenName, Craft.csrfTokenValue);

        axios.post(Craft.actionUrl + '/craftnet/plugins/save', formData)
            .then(response => cb(response))
            .catch(error => cbError(error.response));
    },

    submitPlugin(pluginId, cb, cbError) {
        let data = {
            pluginId: pluginId,
        };

        data[Craft.csrfTokenName] = Craft.csrfTokenValue;

        let params = qs.stringify(data);

        axios.post(Craft.actionUrl + '/craftnet/plugins/submit', params)
            .then(response => cb(response))
            .catch(error => cbError(error.response));
    }

}

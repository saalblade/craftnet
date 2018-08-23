import axios from 'axios';
import qs from 'qs';

export default {

    claimCmsLicense(licenseKey, cb, cbError) {
        const data = {
            key: licenseKey
        }

        axios.post(Craft.actionUrl + '/craftnet/id/cms-licenses/claim', qs.stringify(data), {
                headers: {
                    'X-CSRF-Token': Craft.csrfTokenValue,
                }
            })
            .then(response => cb(response))
            .catch(error => cbError(error.response));
    },

    claimCmsLicenseFile(licenseFile, cb, cbError) {
        let formData = new FormData();
        formData.append('licenseFile', licenseFile);

        axios.post(Craft.actionUrl + '/craftnet/id/cms-licenses/claim', formData, {
                headers: {
                    'X-CSRF-Token': Craft.csrfTokenValue,
                }
            })
            .then(response => cb(response))
            .catch(error => cbError(error.response));
    },

    claimLicensesByEmail(email, cb, cbError) {
        const data = {
            email: email,
        }

        axios.post(Craft.actionUrl + '/craftnet/id/claim-licenses', qs.stringify(data), {
                headers: {
                    'X-CSRF-Token': Craft.csrfTokenValue,
                }
            })
            .then(response => cb(response))
            .catch(error => cbError(error.response));
    },

    claimPluginLicense(licenseKey, cb, cbError) {
        const data = {
            key: licenseKey
        }

        axios.post(Craft.actionUrl + '/craftnet/id/plugin-licenses/claim', qs.stringify(data), {
                headers: {
                    'X-CSRF-Token': Craft.csrfTokenValue,
                }
            })
            .then(response => cb(response))
            .catch(error => cbError(error.response));
    },

    getCmsLicenses(cb, cbError) {
        axios.get(Craft.actionUrl + '/craftnet/id/cms-licenses/get-licenses')
            .then(response => cb(response))
            .catch(error => cbError(error.response));
    },

    getPluginLicenses(cb, cbError) {
        axios.get(Craft.actionUrl + '/craftnet/id/plugin-licenses/get-licenses')
            .then(response => cb(response))
            .catch(error => cbError(error.response));
    },

    releaseCmsLicense(licenseKey, cb, cbError) {
        const data = {
            key: licenseKey
        }

        axios.post(Craft.actionUrl + '/craftnet/id/cms-licenses/release', qs.stringify(data), {
                headers: {
                    'X-CSRF-Token': Craft.csrfTokenValue,
                }
            })
            .then(response => cb(response))
            .catch(error => cbError(error.response));
    },

    releasePluginLicense({pluginHandle, licenseKey}, cb, cbError) {
        const data = {
            handle: pluginHandle,
            key: licenseKey
        }

        axios.post(Craft.actionUrl + '/craftnet/id/plugin-licenses/release', qs.stringify(data), {
                headers: {
                    'X-CSRF-Token': Craft.csrfTokenValue,
                }
            })
            .then(response => cb(response))
            .catch(error => cbError(error.response));
    },

    saveCmsLicense(license, cb, cbError) {
        const data = license

        axios.post(Craft.actionUrl + '/craftnet/id/cms-licenses/save', qs.stringify(data), {
                headers: {
                    'X-CSRF-Token': Craft.csrfTokenValue,
                }
            })
            .then(response => cb(response))
            .catch(error => cbError(error.response));
    },

    savePluginLicense(license, cb, cbError) {
        let data = {};

        for (let attribute in license) {
            if (attribute === 'cmsLicense') {
                continue
            }

            data[attribute] = license[attribute]
        }

        axios.post(Craft.actionUrl + '/craftnet/id/plugin-licenses/save', qs.stringify(data), {
                headers: {
                    'X-CSRF-Token': Craft.csrfTokenValue,
                }
            })
            .then(response => cb(response))
            .catch(error => cbError(error.response));
    },
}
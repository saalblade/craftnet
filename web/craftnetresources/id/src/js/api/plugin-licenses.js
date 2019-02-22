import axios from 'axios';

export default {

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

    getPluginLicense(id) {
        return axios.get(Craft.actionUrl + '/craftnet/id/plugin-licenses/get-license-by-id', {params: {id}})
    },

    getExpiringPluginLicensesTotal(cb, cbError) {
        axios.get(Craft.actionUrl + '/craftnet/id/plugin-licenses/get-expiring-licenses-total')
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
import axios from 'axios';
import FormDataHelper from '../helpers/form-data';
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

        FormDataHelper.append(formData, 'licenseFile', licenseFile);

        axios.post(Craft.actionUrl + '/craftnet/id/cms-licenses/claim', formData, {
                headers: {
                    'X-CSRF-Token': Craft.csrfTokenValue,
                }
            })
            .then(response => cb(response))
            .catch(error => cbError(error.response));
    },

    getCmsLicense(id) {
        return axios.get(Craft.actionUrl + '/craftnet/id/cms-licenses/get-license-by-id', {params: {id}})
    },

    getExpiringCmsLicensesTotal(cb, cbError) {
        axios.get(Craft.actionUrl + '/craftnet/id/cms-licenses/get-expiring-licenses-total')
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
}
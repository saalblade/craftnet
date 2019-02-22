/* global Craft */

import axios from 'axios';
import qs from 'qs';
import FormDataHelper from '../helpers/form-data'

export default {
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
            FormDataHelper.append(formData, attribute, data[attribute]);
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

    generateApiToken(cb, cbError) {
        axios.post(Craft.actionUrl + '/craftnet/id/account/generate-api-token', {}, {
                headers: {
                    'X-CSRF-Token': Craft.csrfTokenValue,
                }
            })
            .then(response => cb(response))
            .catch(error => cbError(error.response));
    },
}

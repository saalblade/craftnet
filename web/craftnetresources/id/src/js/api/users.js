/* global Craft */

import axios from 'axios';

export default {
    saveUser(formData) {
        return axios.post(Craft.actionUrl + '/users/save-user', formData, {
            headers: {
                'X-CSRF-Token': Craft.csrfTokenValue,
            }
        })
    },

    login(formData) {
        return axios.post(Craft.actionUrl + '/users/login', formData, {
            headers: {
                'X-CSRF-Token': Craft.csrfTokenValue,
            }
        })
    },

    login2(params, headers) {
        return axios.post(Craft.actionUrl + '/users/login', params, {headers: headers})
    },

    sendPasswordResetEmail(formData) {
        return axios.post(Craft.actionUrl + '/users/send-password-reset-email', formData, {
            headers: {
                'X-CSRF-Token': Craft.csrfTokenValue,
            }
        })
    },

    getRemainingSessionTime(config) {
        return axios.get(Craft.actionUrl + '/users/get-remaining-session-time', config)
    },

    logout() {
        return axios.get(Craft.actionUrl + '/users/logout')
    }
}

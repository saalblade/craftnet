/* global Craft */

import axios from 'axios';
import FormDataHelper from '../helpers/form-data';

export default {
    getRemainingSessionTime(config) {
        return axios.get(Craft.actionUrl + '/users/get-remaining-session-time', config)
    },

    login(formData) {
        return axios.post(Craft.actionUrl + '/users/login', formData, {
            headers: {
                'X-CSRF-Token': Craft.csrfTokenValue,
            }
        })
    },

    logout() {
        return axios.get(Craft.actionUrl + '/users/logout')
    },

    registerUser(formData) {
        return axios.post(Craft.actionUrl + '/users/save-user', formData, {
            headers: {
                'X-CSRF-Token': Craft.csrfTokenValue,
            }
        })
    },

    saveUser(user, cb, cbError) {
        let formData = new FormData();

        for (let attribute in user) {
            switch (attribute) {
                case 'id':
                    FormDataHelper.append(formData, 'userId', user[attribute]);
                    break;
                case 'email':
                case 'username':
                case 'firstName':
                case 'lastName':
                case 'password':
                case 'newPassword':
                case 'photo':
                    FormDataHelper.append(formData, attribute, user[attribute]);
                    break;
                default:
                    FormDataHelper.append(formData, 'fields[' + attribute + ']', user[attribute]);
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

    sendPasswordResetEmail(formData) {
        return axios.post(Craft.actionUrl + '/users/send-password-reset-email', formData, {
            headers: {
                'X-CSRF-Token': Craft.csrfTokenValue,
            }
        })
    },
}

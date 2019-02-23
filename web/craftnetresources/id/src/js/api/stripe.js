import axios from 'axios';
import qs from 'qs';

export default {
    getAccount(cb, cbError) {
        axios.get(window.craftIdUrl + '/stripe/account')
            .then(response => cb(response))
            .catch(error => cbError(error.response));
    },

    disconnect(cb, cbError) {
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
}
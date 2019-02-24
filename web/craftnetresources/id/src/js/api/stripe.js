import axios from 'axios';
import qs from 'qs';

export default {
    getAccount() {
        return axios.get(window.craftIdUrl + '/stripe/account')
    },

    disconnect() {
        return axios.post(window.craftIdUrl + '/stripe/disconnect')
    },

    saveCard(source) {
        const data = {
            token: source.id
        }

        return axios.post(window.craftIdUrl + '/stripe/save-card', qs.stringify(data))
    },

    removeCard() {
        return axios.post(window.craftIdUrl + '/stripe/remove-card')
    },
}
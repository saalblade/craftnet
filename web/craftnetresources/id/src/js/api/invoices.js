/* global Craft */

import axios from 'axios';

export default {

    getInvoices(cb, cbError) {
        axios.get(Craft.actionUrl + '/craftnet/id/invoices/get-invoices')
            .then(response => cb(response))
            .catch(error => cbError(error.response));
    },

}

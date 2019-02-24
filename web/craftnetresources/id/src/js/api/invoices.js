/* global Craft */

import axios from 'axios'

export default {
    getInvoiceByNumber(number) {
        return axios.get(Craft.actionUrl + '/craftnet/id/invoices/get-invoice-by-number', {params: {number}})
    },
}

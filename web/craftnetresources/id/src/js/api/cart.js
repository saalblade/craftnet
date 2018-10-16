import axios from 'axios'

export default {
    /**
     * Get cart.
     */
    getCart(orderNumber, cb, errorCb) {
        axios.get('https://api.craftcms.test/v1/carts/' + orderNumber)
            .then(response => {
                return cb(response.data)
            })
            .catch(response => {
                return errorCb(response)
            })
    },

    /**
     * Create cart.
     */
    createCart(data, cb, errorCb) {
        axios.post('https://api.craftcms.test/v1/carts', data)
            .then(response => {
                return cb(response.data)
            })
            .catch(response => {
                return errorCb(response)
            })
    },

    /**
     * Update cart.
     */
    updateCart(orderNumber, data, cb, errorCb) {
        if (data.items) {
            data.items.forEach((item, index) => {
                // Todo: Support updating an item with cmsLicenseKey
                if(!item.cmsLicenseKey) {
                    delete item["cmsLicenseKey"]
                }
            })
        }

        axios.post('https://api.craftcms.test/v1/carts/' + orderNumber, data)
            .then(response => {
                return cb(response.data)
            })
            .catch(response => {
                return errorCb(response)
            })
    },

    /**
     * Checkout.
     */
    checkout(data) {
        return axios.post('https://api.craftcms.test/v1/payments', data, {withCredentials: false})
    },

    /**
     * Reset order number.
     */
    resetOrderNumber() {
        localStorage.removeItem('orderNumber')
    },

    /**
     * Save order number
     */
    saveOrderNumber(orderNumber) {
        localStorage.setItem('orderNumber', orderNumber)
    },

    /**
     * Get order number.
     */
    getOrderNumber(cb) {
        const orderNumber = localStorage.getItem('orderNumber')

        return cb(orderNumber)
    },
}
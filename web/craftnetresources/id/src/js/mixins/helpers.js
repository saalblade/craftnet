import get from 'lodash/get'
import update from 'lodash/update'

export default {
    methods: {
        /**
         * Clones an object without references or bindings.
         * Optionally accepts a filtered property list with dot-syntax
         * for nested properties.
         *
         * Example:
         * ```
         * let obj = {
         *     test: 'value',
         *     foo: {
         *         bar: {
         *             baz: 'one',
         *             boo: 'two'
         *         }
         *     }
         * }
         *
         * // an existing value and a missing value, with default
         * let clone = simpleClone(obj, [
         *     'foo.bar.baz',
         *     ['aList', []]
         * ])
         *
         * clone == {foo: {bar: {baz: 'hello'}}, aList: []} // true
         * ```
         *
         * @param {Object} obj
         * @param {Array} propertyList
         */
        simpleClone(obj, propertyList) {
            let clone = JSON.parse(JSON.stringify(obj))

            if (!propertyList) {
                return clone
            }

            let filteredClone = {}

            for (let i = 0; i < propertyList.length; i++) {
                const path = propertyList[i];

                if (typeof path === 'object') {
                    update(filteredClone, path, () => get(clone, path[0], path[1]))
                } else {
                    update(filteredClone, path, () => get(clone, path, null))
                }
            }

            return filteredClone;
        },

        /**
         * Returns a static image URL.
         *
         * @param {String} url
         * @returns {String}
         */
        staticImageUrl(url) {
            if (process.env.NODE_ENV === 'development') {
                return process.env.BASE_URL + 'img/static/' + url;
            }

            return '/craftnetresources/id/dist/img/static/' + url;
        },

        /**
         * Returns the Craft Plugins URL.
         *
         * @returns {String}
         */
        craftPluginsUrl() {
            return process.env.VUE_APP_CRAFT_PLUGINS_URL;
        },

        loadAuthenticatedUserData(cb, cbError) {
            // Account
            this.$store.dispatch('craftId/getCraftIdData')
                .then(() => {
                    // Cart
                    this.$store.dispatch('cart/getCart')
                        .then(() => {
                            this.$store.commit('app/updateLoading', false)

                            if (cb) {
                                cb();
                            }

                            // Stripe Account
                            if (window.stripeAccessToken) {
                                this.$store.dispatch('account/getStripeAccount')
                                    .then(() => {
                                        this.$store.commit('app/updateStripeAccountLoading', false)
                                    }, () => {
                                        this.$store.commit('app/updateStripeAccountLoading', false)
                                    });
                            } else {
                                this.$store.commit('app/updateStripeAccountLoading', false)
                            }

                            // Invoices
                            this.$store.dispatch('account/getInvoices')
                                .then(() => {
                                    this.$store.commit('app/updateInvoicesLoading', false)
                                })
                                .catch(() => {
                                    this.$store.commit('app/updateInvoicesLoading', false)
                                });
                        })
                        .catch(() => {
                            if (cbError) {
                                cbError();
                            }
                        })
                });
        },

        loadGuestUserData() {
            // Cart
            this.$store.dispatch('cart/getCart')
                .then(() => {
                    this.$store.commit('app/updateLoading', false)
                })
        }
    }
}

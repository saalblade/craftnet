// import './../sass/app.scss';

import Vue from 'vue';
import store from './store'
import {currency} from './filters/currency';
import {formatCmsLicense, formatPluginLicense} from './filters/licenses';
import App from './App.vue';

Vue.filter('currency', currency);
Vue.filter('formatCmsLicense', formatCmsLicense);
Vue.filter('formatPluginLicense', formatPluginLicense);

window.craftIdApp = new Vue({

    el: '#app',

    store,

    components: {
        App,
    },

    data() {
        return {
            stripeCustomerLoading: true,
            stripeAccountLoading: true,
            loading: true,
            notification: null,
        }
    },

    methods: {

        /**
         * Connect app callback.
         *
         * @param apps
         */
        connectAppCallback(apps) {
            this.$store.dispatch('connectAppCallback', apps);

            this.$root.displayNotice('App connected.');
        },

        /**
         *  Displays an error.
         *
         * @param {string} message
         */
        displayNotice(message) {
            this.displayNotification('success', message);
        },

        /**
         *  Displays an error.
         *
         * @param {string} message
         */
        displayError(message) {
            this.displayNotification('error', message);
        },

        /**
         *  Displays a notification.
         *
         * @param {string} type
         * @param {string} message
         */
        displayNotification(type, message) {
            this.notification = {
                type: type,
                message: message
            };

            setTimeout(function() {
                this.notification = null;
            }.bind(this), 2000);
        },

    },

    created() {
        this.$store.dispatch('getCraftIdData').then(() => {
            this.loading = false;
        });

        this.$store.dispatch('getStripeCustomer').then(response => {
            this.stripeCustomerLoading = false;
        }, error => {
            this.stripeCustomerLoading = false;
        });

        if (window.stripeAccessToken) {
            this.$store.dispatch('getStripeAccount').then(response => {
                this.stripeAccountLoading = false;
            }, error => {
                this.stripeAccountLoading = false;
            });
        } else {
            this.stripeAccountLoading = false;
        }
    }

});

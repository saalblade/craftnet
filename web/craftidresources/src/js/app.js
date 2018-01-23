// import './../sass/app.scss';

import Vue from 'vue';
import store from './store'
import { currency } from './filters/currency';
import App from './App.vue';

Vue.filter('currency', currency);

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

        connectAppCallback(apps) {
            this.$store.dispatch('connectAppCallback', apps);

            this.$root.displayNotice('App connected.');
        },

        displayNotification(type, message) {
            this.notification = {
                type: type,
                message: message
            };

            setTimeout(function() {
                this.notification = null;
            }.bind(this), 2000);
        },

        displayNotice(message) {
            this.displayNotification('success', message);
        },

        displayError(message) {
            this.displayNotification('danger', message);
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

        if(window.stripeAccessToken) {
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

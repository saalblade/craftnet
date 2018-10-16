// import './../sass/app.scss';

import Vue from 'vue';
import store from './store'
import {currency} from './filters/currency';
import {formatCmsLicense, formatPluginLicense} from './filters/licenses';
import {capitalize} from './filters/capitalize';
import App from './App.vue';

// Font Awesome
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { library } from '@fortawesome/fontawesome-svg-core'
import { faCoffee, faTimes, faTh, faBars, faPlus, faKey, faPlug, faImage, faUser, faPencilAlt, faExclamationTriangle, faBug, faShoppingCart, faDollarSign } from '@fortawesome/free-solid-svg-icons'
library.add([faCoffee, faTimes, faTh, faBars, faPlus, faKey, faPlug, faImage, faUser, faPencilAlt, faExclamationTriangle, faBug, faShoppingCart, faDollarSign])


Vue.component('font-awesome-icon', FontAwesomeIcon)
Vue.config.productionTip = false


Vue.filter('currency', currency);
Vue.filter('formatCmsLicense', formatCmsLicense);
Vue.filter('formatPluginLicense', formatPluginLicense);
Vue.filter('capitalize', capitalize);
Vue.use(require('vue-moment'));

import Vuelidate from 'vuelidate'
Vue.use(Vuelidate)

window.craftIdApp = new Vue({

    store,
    render: h => h(App),

    data() {
        return {
            invoicesLoading: true,
            stripeAccountLoading: true,
            loading: true,
            notification: null,
            renewLicense: null,
            showRenewLicensesModal: false,
        }
    },

    methods: {

        /**
         * Connect app callback.
         *
         * @param apps
         */
        connectAppCallback(apps) {
            this.$store.dispatch('account/connectAppCallback', apps);

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

}).$mount('#app')

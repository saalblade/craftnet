import Vue from 'vue';
import VueResource from 'vue-resource';
import store from './store'
import { currency } from './filters/currency';

import App from './App';

Vue.filter('currency', currency)
Vue.use(VueResource);

window.pluginStoreApp = new Vue({
    el: '#app',
    store,

    components: {
        App,
    },

    data() {
        return {
            loading: true,
            notification: null,
        }
    },

    methods: {
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
    }
});

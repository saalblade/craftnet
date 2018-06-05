import Vue from 'vue'
import App from './App.vue'
import store from './store'
import {currency} from './filters/currency';
import {escapeHtml, formatDate, formatNumber, t} from './filters/craft';

Vue.filter('currency', currency);
Vue.filter('escapeHtml', escapeHtml);
Vue.filter('formatDate', formatDate);
Vue.filter('formatNumber', formatNumber);
Vue.filter('t', t);

window.craftIdApp = new Vue({

    el: '#site',
    store,

    components: {
        App,
    },

    data() {
        return {
        }
    },

})

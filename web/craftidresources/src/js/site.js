import Vue from 'vue';
import Notification from './components/Notification';
import LoginForm from './components/LoginForm';

window.craftIdSite = new Vue({

    el: '#site',

    components: {
        Notification,
        LoginForm
    },

});

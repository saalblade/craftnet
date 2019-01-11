import Vue from 'vue';
import Notification from './components/Notification';
import LoginForm from './components/LoginForm';
import './plugins/craftui'
import './../sass/app.scss'

window.craftIdSite = new Vue({
    el: '#site',

    data() {
        return {
            loading: true,
            registerFormLoading: false,
        }
    },

    components: {
        Notification,
        LoginForm
    },

    methods: {

        register() {
            this.registerFormLoading = true
            this.$refs.registerform.submit()
        }

    },

    mounted() {
        this.loading = false
    }

})



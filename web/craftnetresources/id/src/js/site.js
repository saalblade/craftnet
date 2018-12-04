import Vue from 'vue';
import Notification from './components/Notification';
import LoginForm from './components/LoginForm';
import './plugins/craftui'

window.craftIdSite = new Vue({

    render: h => h(LoginForm),

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

}).$mount('#site');



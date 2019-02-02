<template>
    <div id="app">
        <auth-manager ref="authManager"></auth-manager>
        <renew-licenses-modal v-if="showRenewLicensesModal" :license="renewLicense" @cancel="$store.commit('app/updateShowRenewLicensesModal', false)" />

        <template v-if="notification">
            <div id="notifications-wrapper" :class="{'hide': !notification }">
                <div id="notifications">
                    <div class="notification" :class="notification.type">{{ notification.message }}</div>
                </div>
            </div>
        </template>

        <template v-if="loading">
            <div class="text-center">
                <spinner big cssClass="mt-8"></spinner>
            </div>
        </template>

        <template v-else>
            <layout></layout>
        </template>
    </div>
</template>

<script>
    import {mapState} from 'vuex'
    import router from './router';
    import AuthManager from './components/AuthManager';
    import RenewLicensesModal from './components/licenses/renew-licenses/RenewLicensesModal';
    import Layout from './components/Layout';
    import Spinner from './components/Spinner';

    export default {

        router,

        components: {
            AuthManager,
            RenewLicensesModal,
            Layout,
            Spinner,
        },

        computed: {

            ...mapState({
                notification: state => state.app.notification,
                showRenewLicensesModal: state => state.app.showRenewLicensesModal,
                loading: state => state.app.loading,
                renewLicense: state => state.app.renewLicense,
            }),

        },

        created() {
            this.$store.dispatch('craftId/getCraftIdData')
                .then(() => {
                    this.$store.commit('app/updateLoading', false)
                    this.$store.dispatch('cart/getCart');
                });

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

            this.$store.dispatch('account/getInvoices')
                .then(() => {
                    this.$store.commit('app/updateInvoicesLoading', false)
                })
                .catch(() => {
                    this.$store.commit('app/updateInvoicesLoading', false)
                });

            if(window.sessionNotice) {
                this.$store.dispatch('app/displayNotice', window.sessionNotice);
            }

            if(window.sessionError) {
                this.$store.dispatch('app/displayError', window.sessionError);
            }
        }
    }
</script>

<style lang="scss">
    @import './../sass/app.scss';
</style>
<template>
    <div id="app">
        <auth-manager ref="authManager"></auth-manager>
        <renew-licenses-modal v-if="showRenewLicensesModal" :license="renewLicense" @cancel="$store.dispatch('app/setShowRenewLicensesModal', false)" />

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
            <component :is="layout"></component>
        </template>
    </div>
</template>

<script>
    import {mapState} from 'vuex'
    import router from './router';
    import AuthManager from './components/AuthManager';
    import RenewLicensesModal from './components/licenses/renew-licenses/RenewLicensesModal';
    import Layout from './components/Layout';
    import LayoutNoSidebar from './components/LayoutNoSidebar';
    import Spinner from './components/Spinner';

    export default {

        router,

        components: {
            AuthManager,
            RenewLicensesModal,
            Layout,
            LayoutNoSidebar,
            Spinner,
        },

        computed: {

            layout() {
                return 'layout' + (this.$route.meta.layout ? '-' + this.$route.meta.layout : '')
            },

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
                    this.$store.dispatch('app/setLoading', false)
                });

            this.$store.dispatch('cart/getCart');

            if (window.stripeAccessToken) {
                this.$store.dispatch('account/getStripeAccount')
                    .then(() => {
                        this.$store.dispatch('app/setStripeAccountLoading', false)
                    }, () => {
                        this.$store.dispatch('app/setStripeAccountLoading', false)
                    });
            } else {
                this.$store.dispatch('app/setStripeAccountLoading', false)
            }

            this.$store.dispatch('account/getInvoices')
                .then(() => {
                    this.$store.dispatch('app/setInvoicesLoading', false)
                })
                .catch(() => {
                    this.$store.dispatch('app/setInvoicesLoading', false)
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


    // Notifications

    #notifications-wrapper {
        @apply .fixed .pin-l .w-full .pointer-events-none;
        z-index: 101;

        #notifications {
            @apply .text-center;

            .notification {
                @apply .inline-block .pointer-events-auto;

                padding: 5px 10px;
                border-radius: 0 0 3px 3px;
                border-width: 0 1px 1px;
                color: #fff !important;
                -moz-osx-font-smoothing: grayscale;
                -webkit-font-smoothing: antialiased;
                font-weight: 600;
                -webkit-box-shadow: 0 0 5px rgba(0, 0, 0, 0.25);
                -moz-box-shadow: 0 0 5px rgba(0, 0, 0, 0.25);
                box-shadow: 0 0 5px rgba(0, 0, 0, 0.25);

                &.notice {
                    @apply .bg-blue;
                }

                &.success {
                    @apply .bg-blue;
                }

                &.error {
                    @apply .bg-red;
                }
            }
        }
    }
</style>
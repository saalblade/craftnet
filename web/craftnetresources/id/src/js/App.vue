<template>
    <div id="app" :class="{'has-sidebar': (!$route.meta.layout || $route.meta.layout !== 'no-sidebar')}">
        <auth-manager ref="authManager"></auth-manager>

        <template v-if="currentUser">
            <renew-licenses-modal v-if="showRenewLicensesModal" :license="renewLicense" @cancel="$store.commit('app/updateShowRenewLicensesModal', false)" />
        </template>

        <template v-if="notification">
            <div id="notifications-wrapper" :class="{'hide': !notification }">
                <div id="notifications">
                    <div class="notification" :class="notification.type">{{ notification.message }}</div>
                </div>
            </div>
        </template>

        <template v-if="loading">
            <div class="text-center">
                <spinner class="lg mt-8"></spinner>
            </div>
        </template>

        <template v-else>
            <component :is="layoutComponent"></component>
        </template>
    </div>
</template>

<script>
    import {mapState} from 'vuex'
    import router from './router'
    import helpers from './mixins/helpers'
    import AuthManager from './components/AuthManager'
    import RenewLicensesModal from './components/licenses/renew-licenses/RenewLicensesModal'
    import AppLayout from './components/layouts/AppLayout'
    import SiteLayout from './components/layouts/SiteLayout'

    export default {
        router,

        mixins: [helpers],

        components: {
            AuthManager,
            RenewLicensesModal,
            AppLayout,
            SiteLayout,
        },

        computed: {
            ...mapState({
                notification: state => state.app.notification,
                showRenewLicensesModal: state => state.app.showRenewLicensesModal,
                loading: state => state.app.loading,
                renewLicense: state => state.app.renewLicense,
                currentUser: state => state.account.currentUser,
            }),

            layoutComponent() {
                switch (this.$route.meta.layout) {
                    case 'site':
                        return 'site-layout'

                    default:
                        return 'app-layout'
                }
            }
        },

        methods: {
            loadUserData() {
                if (window.currentUserId) {
                    this.loadAccount()
                } else {
                    this.loadGuestUserData()
                }
            },
        },

        created() {
            this.loadUserData()

            if(window.sessionNotice) {
                this.$store.dispatch('app/displayNotice', window.sessionNotice)
            }

            if(window.sessionError) {
                this.$store.dispatch('app/displayError', window.sessionError)
            }
        }
    }
</script>

<style lang="scss">
    @import './../sass/app.scss';

    #app:not(.has-sidebar) {
        .header {
            #sidebar-toggle {
                @apply .hidden;
            }
        }
    }
</style>

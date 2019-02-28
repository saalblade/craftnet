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
            <layout></layout>
        </template>
    </div>
</template>

<script>
    import {mapState} from 'vuex'
    import router from './router'
    import helpers from './mixins/helpers'
    import AuthManager from './components/AuthManager'
    import RenewLicensesModal from './components/licenses/renew-licenses/RenewLicensesModal'
    import Layout from './components/Layout'

    export default {
        router,

        mixins: [helpers],

        components: {
            AuthManager,
            RenewLicensesModal,
            Layout,
        },

        computed: {
            ...mapState({
                notification: state => state.app.notification,
                showRenewLicensesModal: state => state.app.showRenewLicensesModal,
                loading: state => state.app.loading,
                renewLicense: state => state.app.renewLicense,
                currentUser: state => state.users.currentUser,
            }),

            currentLayout() {
                switch (this.$route.meta.layout) {
                    case 'site-layout':
                        return this.$route.meta.layout

                    default:
                        return 'app-layout'
                }
            }
        },

        methods: {
            loadUserData() {
                if (window.currentUserId) {
                    this.loadAuthenticatedUserData()
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
</style>

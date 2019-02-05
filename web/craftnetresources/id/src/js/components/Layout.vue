<template>
    <div class="app">
        <app-header :showingSidebar="showingSidebar" @toggleSidebar="toggleSidebar()"></app-header>

        <div class="flex-container">
            <template v-if="!$route.meta.layout || $route.meta.layout !== 'no-sidebar'">
                <app-sidebar :showingSidebar="showingSidebar" @closeSidebar="closeSidebar()"></app-sidebar>
            </template>

            <div class="main">
                <div class="page-alerts">
                    <template v-if="$route.meta.stripeAccountAlert">
                        <stripe-account-alert></stripe-account-alert>
                    </template>

                    <template v-if="$route.meta.cmsLicensesRenewAlert">
                        <license-renew-alert type="CMS" :expiring-licenses="expiringCmsLicenses"></license-renew-alert>
                    </template>

                    <template v-if="$route.meta.pluginLicensesRenewAlert">
                        <license-renew-alert type="plugin" :expiring-licenses="expiringPluginLicenses"></license-renew-alert>
                    </template>
                </div>

                <div class="main-content">
                    <router-view :key="$route.path"></router-view>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import {mapGetters} from 'vuex'
    import AppHeader from './AppHeader';
    import AppSidebar from './AppSidebar';
    import StripeAccountAlert from './StripeAccountAlert';
    import LicenseRenewAlert from './LicenseRenewAlert';

    export default {

        components: {
            AppHeader,
            AppSidebar,
            StripeAccountAlert,
            LicenseRenewAlert,
        },

        data() {
            return {
                showingSidebar: false,
            }
        },

        computed: {

            ...mapGetters({
                expiringCmsLicenses: 'licenses/expiringCmsLicenses',
                expiringPluginLicenses: 'licenses/expiringPluginLicenses',
            }),

        },

        methods: {

            /**
             * Toggles the sidebar.
             */
            toggleSidebar() {
                this.showingSidebar = !this.showingSidebar;
            },

            /**
             * Closes the sidebar.
             */
            closeSidebar() {
                this.showingSidebar = false;
            },

        }


    }
</script>
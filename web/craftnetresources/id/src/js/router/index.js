import Vue from 'vue'
import VueRouter from 'vue-router'
import BillingIndex from '../pages/account/billing/index'
import AccountBillingInvoiceNumber from '../pages/account/billing/invoices/_number'
import AccountLicensesClaim from '../pages/account/licenses/claim'
import AccountLicensesCmsIndex from '../pages/account/licenses/cms/index'
import AccountLicensesCmsId from '../pages/account/licenses/cms/_id'
import AccountLicensesPluginsIndex from '../pages/account/licenses/plugins/index'
import AccountLicensesPluginsId from '../pages/account/licenses/plugins/_id'
import DeveloperSalesIndex from '../pages/developer/sales/index'
import DeveloperSalesId from '../pages/developer/sales/_id'
import DeveloperPluginsId from '../pages/developer/plugins/_id'
import DeveloperPlugins from '../pages/developer/plugins/index'
import DeveloperProfile from '../pages/developer/profile'
import DeveloperSettings from '../pages/developer/settings'
import AccountSettings from '../pages/account/settings'
import BuyLicense from '../pages/BuyLicense'
import Cart from '../pages/Cart'
import Payment from '../pages/Payment'
import BuyPlugin from '../pages/BuyPlugin'
import BuyPluginMock from '../pages/BuyPluginMock'
import ThankYou from '../pages/ThankYou'

Vue.use(VueRouter);

const router = new VueRouter({
    mode: 'history',
    linkActiveClass: 'active',
    canReuse: false,
    scrollBehavior (to, from, savedPosition) {
        return savedPosition || { x: 0, y: 0 };
    },
    routes: [
        // Redirects

        {
            path: '/',
            redirect: '/account/licenses',
        },
        {
            path: '/account',
            redirect: '/account/billing',
        },
        {
            path: '/account/licenses',
            redirect: '/account/licenses/cms',
        },
        {
            path: '/developer',
            redirect: '/developer/plugins',
        },


        // Pages
        
        {
            path: '/account/billing',
            name: 'Billing',
            component: BillingIndex
        },
        {
            path: '/account/billing/invoices/:number',
            name: 'AccountBillingInvoiceNumber',
            component: AccountBillingInvoiceNumber
        },
        {
            path: '/account/licenses/cms',
            component: AccountLicensesCmsIndex,
        },
        {
            path: '/account/licenses/cms/:id',
            component: AccountLicensesCmsId
        },
        {
            path: '/account/licenses/plugins',
            component: AccountLicensesPluginsIndex
        },
        {
            path: '/account/licenses/plugins/:id',
            component: AccountLicensesPluginsId
        },
        {
            path: '/account/licenses/claim',
            component: AccountLicensesClaim
        },
        {
            path: '/account/settings',
            name: 'AccountSettings',
            component: AccountSettings
        },
        {
            path: '/developer/plugins',
            name: 'Plugins',
            component: DeveloperPlugins
        },
        {
            path: '/developer/add-plugin',
            component: DeveloperPluginsId,
        },
        {
            path: '/developer/plugins/:id',
            name: 'DeveloperPluginsId',
            component: DeveloperPluginsId,
        },
        {
            path: '/developer/sales',
            name: 'DeveloperSalesIndex',
            component: DeveloperSalesIndex
        },
        {
            path: '/developer/sales/:id',
            name: 'DeveloperSalesId',
            component: DeveloperSalesId,
        },
        {
            path: '/developer/profile',
            name: 'DeveloperProfile',
            component: DeveloperProfile
        },
        {
            path: '/developer/settings',
            name: 'DeveloperSettings',
            component: DeveloperSettings
        },
        {
            path: '/buy',
            name: 'BuyLicense',
            component: BuyLicense
        },
        {
            path: '/buy-plugin-mock/:handle/:edition',
            name: 'BuyPluginMock',
            component: BuyPluginMock
        },
        {
            path: '/buy-plugin/:handle/:edition',
            name: 'BuyPlugin',
            component: BuyPlugin
        },
        {
            path: '/cart',
            name: 'Cart',
            component: Cart
        },
        {
            path: '/payment',
            name: 'Payment',
            component: Payment
        },
        {
            path: '/thank-you',
            name: 'ThankYou',
            component: ThankYou
        },
    ]
});

// Renew session when changing route
router.beforeEach((to, from, next) => {
    if (router.app.$refs.authManager) {
        router.app.$refs.authManager.renewSession();
    }

    next();
});

export default router;

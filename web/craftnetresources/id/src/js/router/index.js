import Vue from 'vue'
import VueRouter from 'vue-router'
import AccountBillingIndex from '../pages/account/billing/index'
import AccountBillingInvoiceNumber from '../pages/account/billing/invoices/_number'
import AccountSettings from '../pages/account/settings'
import BuyPlugin from '../pages/buy-plugin/index'
import BuyPluginMock from '../pages/buy-plugin/_mock'
import Cart from '../pages/cart'
import DeveloperPlugins from '../pages/developer/plugins/index'
import DeveloperPluginsId from '../pages/developer/plugins/_id'
import DeveloperProfile from '../pages/developer/profile'
import DeveloperSalesId from '../pages/developer/sales/_id'
import DeveloperSalesIndex from '../pages/developer/sales/index'
import DeveloperSettings from '../pages/developer/settings'
import LicensesBuy from '../pages/licenses/buy'
import LicensesClaim from '../pages/licenses/claim'
import LicensesCmsId from '../pages/licenses/cms/_id'
import LicensesCmsIndex from '../pages/licenses/cms/index'
import LicensesPluginsId from '../pages/licenses/plugins/_id'
import LicensesPluginsIndex from '../pages/licenses/plugins/index'
import Payment from '../pages/payment'
import ThankYou from '../pages/thank-you'
import PartnerOverview from '../pages/partner/overview'
import PartnerProfile from '../pages/partner/profile'

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
            redirect: '/licenses',
        },
        {
            path: '/account',
            redirect: '/account/billing',
        },
        {
            path: '/licenses',
            redirect: '/licenses/cms',
        },
        {
            path: '/developer',
            redirect: '/developer/plugins',
        },


        // Pages

        {
            path: '/account/billing',
            name: 'Billing',
            component: AccountBillingIndex
        },
        {
            path: '/account/billing/invoices/:number',
            name: 'AccountBillingInvoiceNumber',
            component: AccountBillingInvoiceNumber
        },
        {
            path: '/account/settings',
            name: 'AccountSettings',
            component: AccountSettings
        },
        {
            path: '/buy-plugin/:handle/:edition',
            name: 'BuyPlugin',
            component: BuyPlugin
        },
        {
            path: '/buy-plugin-mock/:handle/:edition',
            name: 'BuyPluginMock',
            component: BuyPluginMock
        },
        {
            path: '/cart',
            name: 'Cart',
            component: Cart,
            meta: { layout: "no-sidebar" }
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
            path: '/licenses/buy',
            name: 'LicensesBuy',
            component: LicensesBuy
        },
        {
            path: '/licenses/claim',
            component: LicensesClaim
        },
        {
            path: '/licenses/cms',
            component: LicensesCmsIndex,
        },
        {
            path: '/licenses/cms/:id',
            component: LicensesCmsId
        },
        {
            path: '/licenses/plugins',
            component: LicensesPluginsIndex
        },
        {
            path: '/licenses/plugins/:id',
            component: LicensesPluginsId
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


        // Partner

        {
            path: '/partner',
            redirect: '/partner/overview',
        },
        {
            path: '/partner/overview',
            name: 'PartnerOverview',
            component: PartnerOverview
        },
        {
            path: '/partner/profile',
            name: 'PartnerProfile',
            component: PartnerProfile
        }
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

import Vue from 'vue'
import VueRouter from 'vue-router'
import Register from '../pages/register/index'
import RegisterSuccess from '../pages/register/success'
import Login from '../pages/login'
import ForgotPassword from '../pages/forgot-password'
import AccountBillingIndex from '../pages/account/billing/index'
import AccountBillingInvoiceNumber from '../pages/account/billing/invoices/_number'
import AccountSettings from '../pages/account/settings'
import BuyPlugin from '../pages/buy-plugin/index'
import BuyCms from '../pages/buy-cms/index'
import Cart from '../pages/cart'
import DeveloperPlugins from '../pages/developer/plugins/index'
import DeveloperPluginsId from '../pages/developer/plugins/_id'
import DeveloperProfile from '../pages/developer/profile'
import DeveloperSalesIndex from '../pages/developer/sales/index'
import DeveloperSettings from '../pages/developer/settings'
import LicensesClaim from '../pages/licenses/claim'
import LicensesCmsId from '../pages/licenses/cms/_id'
import LicensesCmsIndex from '../pages/licenses/cms/index'
import LicensesPluginsId from '../pages/licenses/plugins/_id'
import LicensesPluginsIndex from '../pages/licenses/plugins/index'
import Payment from '../pages/payment'
import ThankYou from '../pages/thank-you'
import PartnerOverview from '../pages/partner/overview'
import PartnerProfile from '../pages/partner/profile'
import NotFound from '../pages/not-found'

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
            path: '/site/register',
            name: 'Register',
            component: Register,
            meta: { layout: "no-sidebar" }
        },
        {
            path: '/site/register/success',
            name: 'RegisterSuccess',
            component: RegisterSuccess,
            meta: { layout: "no-sidebar" }
        },
        {
            path: '/site/login',
            name: 'Login',
            component: Login,
            meta: { layout: "no-sidebar" }
        },
        {
            path: '/site/forgot-password',
            name: 'ForgotPassword',
            component: ForgotPassword,
            meta: { layout: "no-sidebar" }
        },
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
            component: BuyPlugin,
            meta: { layout: "no-sidebar" }
        },
        {
            path: '/buy-cms/:edition',
            name: 'BuyCms',
            component: BuyCms,
            meta: { layout: "no-sidebar" }
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
            component: DeveloperPlugins,
            meta: { stripeAccountAlert: true }
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
            component: DeveloperSalesIndex,
            meta: { stripeAccountAlert: true }
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
            path: '/licenses/claim',
            component: LicensesClaim
        },
        {
            path: '/licenses/cms',
            component: LicensesCmsIndex,
            meta: { cmsLicensesRenewAlert: true }
        },
        {
            path: '/licenses/cms/:id',
            component: LicensesCmsId
        },
        {
            path: '/licenses/plugins',
            component: LicensesPluginsIndex,
            meta: { pluginLicensesRenewAlert: true }
        },
        {
            path: '/licenses/plugins/:id',
            component: LicensesPluginsId
        },
        {
            path: '/payment',
            name: 'Payment',
            component: Payment,
            meta: { layout: "no-sidebar" }
        },
        {
            path: '/thank-you',
            name: 'ThankYou',
            component: ThankYou,
            meta: { layout: "no-sidebar" }
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
        },


        // Not found
        {
            path: '*',
            name: 'NotFound',
            component: NotFound,
            meta: { layout: "no-sidebar" }
        },
    ]
});

import store from '../store'

router.beforeEach((to, from, next) => {
    // Renew the auth manager’s session
    if (router.app.$refs.authManager) {
        router.app.$refs.authManager.renewSession();
    }

    // Guest users are limited to login, registration and cart pages
    if (!store.state.account.currentUser) {
        if (!window.currentUserId) {
            if (to.path !== '/site/login' && to.path !== '/site/register' && to.path !== '/site/register/success' && to.path !== '/site/forgot-password' && to.path !== '/cart') {
                router.push({path: '/site/login'})
            } else {
                next()
            }
        } else {
            store.dispatch('craftId/getCraftIdData')
                .then(() => {
                    next()
                })
                .catch(() => {
                    router.push({path: '/site/login'})
                })
        }
    } else {
        next()
    }
});

export default router;

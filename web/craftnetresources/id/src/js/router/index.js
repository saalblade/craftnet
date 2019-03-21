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
import Identity from '../pages/identity'
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

Vue.use(VueRouter)

const router = new VueRouter({
    mode: 'history',
    linkActiveClass: 'active',
    canReuse: false,
    scrollBehavior (to, from, savedPosition) {
        return savedPosition || { x: 0, y: 0 }
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
            path: '/register',
            name: 'Register',
            component: Register,
            meta: { layout: 'site' }
        },
        {
            path: '/register/success',
            name: 'RegisterSuccess',
            component: RegisterSuccess,
            meta: { layout: 'site' }
        },
        {
            path: '/login',
            name: 'Login',
            component: Login,
            meta: { layout: 'site', mainFull: true }
        },
        {
            path: '/forgot-password',
            name: 'ForgotPassword',
            component: ForgotPassword,
            meta: { layout: 'site' }
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
            meta: { sidebar: false }
        },
        {
            path: '/buy-cms/:edition',
            name: 'BuyCms',
            component: BuyCms,
            meta: { sidebar: false }
        },
        {
            path: '/cart',
            name: 'Cart',
            component: Cart,
            meta: { sidebar: false }
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
            path: '/identity',
            name: 'Identity',
            component: Identity,
            meta: { sidebar: false }
        },
        {
            path: '/payment',
            name: 'Payment',
            component: Payment,
            meta: { sidebar: false }
        },
        {
            path: '/thank-you',
            name: 'ThankYou',
            component: ThankYou,
            meta: { sidebar: false }
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
            meta: { sidebar: false }
        },
    ]
})

import store from '../store'

router.beforeEach((to, from, next) => {
    // Renew the auth manager’s session
    if (router.app.$refs.authManager) {
        router.app.$refs.authManager.renewSession()
    }

    // Guest users are limited to login, registration and cart pages
    if (!store.state.account.currentUser) {
        if (store.state.account.currentUserLoaded) {
            // Todo: Replace conditional paths with meta.requireAuthentication for pages
            if (to.path !== '/login' && to.path !== '/register' && to.path !== '/register/success' && to.path !== '/forgot-password' && to.path !== '/cart' && to.path !== '/identity' && to.path !== '/payment' && to.path !== '/thank-you' && to.path.startsWith('/buy-plugin/') !== true && to.path.startsWith('/buy-cms/') !== true) {
                router.push({path: '/login'})
            } else {
                next()
            }
        } else {
            store.dispatch('account/getAccount')
                .then(() => {
                    next()
                })
                .catch(() => {
                    if (to.path !== '/login' && to.path !== '/register' && to.path !== '/register/success' && to.path !== '/forgot-password' && to.path !== '/cart' && to.path !== '/identity' && to.path !== '/payment' && to.path !== '/thank-you' && to.path.startsWith('/buy-plugin/') !== true && to.path.startsWith('/buy-cms/') !== true) {
                        router.push({path: '/login'})
                    } else {
                        next()
                    }
                })
        }
    } else {
        next()
    }
})

export default router

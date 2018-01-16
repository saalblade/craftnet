import Vue from 'vue'
import VueRouter from 'vue-router'
import Profile from '../pages/Profile'
import Billing from '../pages/Billing'
import BillingInvoiceDetails from '../pages/BillingInvoiceDetails'
import Customers from '../pages/Customers'
import CustomersDetails from '../pages/CustomersDetails'
import Licenses from '../pages/Licenses'
import LicensesClaim from '../pages/LicensesClaim'
import LicensesCraft from '../pages/LicensesCraft'
import LicensesCraftDetails from '../pages/LicensesCraftDetails'
import LicensesPlugins from '../pages/LicensesPlugins'
import LicensesPluginsDetails from '../pages/LicensesPluginsDetails'
import LicensesRenew from '../pages/LicensesRenew'
import Payments from '../pages/Payments'
import PaymentsDetails from '../pages/PaymentsDetails'
import Payouts from '../pages/Payouts'
import PayoutsDetails from '../pages/PayoutsDetails'
import Plugins from '../pages/Plugins'
import PluginsEdit from '../pages/PluginsEdit'
import PluginsIndex from '../pages/PluginsIndex'
import Settings from '../pages/Settings'

Vue.use(VueRouter);

const router = new VueRouter({
    mode: 'history',
    linkActiveClass: 'active',
    routes: [
        {
            path: '/',
            redirect: '/account',
        },
        {
            path: '/account',
            redirect: '/developer/plugins',
        },
        {
            path: '/account/licenses',
            name: 'Licenses',
            component: Licenses,
            redirect: '/account/licenses/craft',
            children: [
                {
                    path: 'craft',
                    component: LicensesCraft,
                },
                {
                    path: 'craft/:id',
                    component: LicensesCraftDetails
                },
                {
                    path: 'plugins',
                    component: LicensesPlugins
                },
                {
                    path: 'plugins/:id',
                    component: LicensesPluginsDetails
                },
                {
                    path: 'claim',
                    component: LicensesClaim
                },
                {
                    path: 'renew',
                    component: LicensesRenew
                }
            ],
        },
        {
            path: '/account/billing',
            name: 'Billing',
            component: Billing
        },
        {
            path: '/account/billing/invoices/:id',
            name: 'BillingInvoiceDetails',
            component: BillingInvoiceDetails
        },
        {
            path: '/account/profile',
            name: 'Profile',
            component: Profile
        },
        {
            path: '/account/settings',
            name: 'Settings',
            component: Settings
        },
        {
            path: '/developer/customers',
            name: 'Customers',
            component: Customers
        },
        {
            path: '/developer/customers/:id',
            name: 'CustomersDetails',
            component: CustomersDetails
        },
        {
            path: '/developer/plugins',
            component: Plugins,
            children: [
                {
                    path: '',
                    name: 'Plugins',
                    component: PluginsIndex
                },
                {
                    path: 'submit',
                    component: PluginsEdit,
                },
                {
                    path: ':id',
                    name: 'PluginsEdit',
                    component: PluginsEdit,
                }

            ]
        },
        {
            path: '/developer/payments',
            name: 'Payments',
            component: Payments
        },
        {
            path: '/developer/payments/:id',
            name: 'PaymentsDetails',
            component: PaymentsDetails,
        },
        {
            path: '/developer/payouts',
            name: 'Payouts',
            component: Payouts,
        },
        {
            path: '/developer/payouts/:id',
            name: 'PayoutsDetails',
            component: PayoutsDetails,
        }
    ]
});

router.beforeEach((to, from, next) => {
    if(router.app.$refs.authManager) {
        router.app.$refs.authManager.renewSession();
    }

    next();
});

export default router;

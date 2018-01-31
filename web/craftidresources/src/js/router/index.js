import Vue from 'vue'
import VueRouter from 'vue-router'
import Profile from '../pages/Profile'
import Billing from '../pages/Billing'
import BillingInvoiceDetails from '../pages/BillingInvoiceDetails'
import LicensesClaim from '../pages/LicensesClaim'
import LicensesCraft from '../pages/LicensesCraft'
import LicensesCraftDetails from '../pages/LicensesCraftDetails'
import LicensesPlugins from '../pages/LicensesPlugins'
import LicensesPluginsDetails from '../pages/LicensesPluginsDetails'
import LicensesRenew from '../pages/LicensesRenew'
import Sales from '../pages/Sales'
import SalesDetails from '../pages/SalesDetails'
import PluginsEdit from '../pages/PluginsEdit'
import Plugins from '../pages/Plugins'
import Settings from '../pages/Settings'
import DeveloperSettings from '../pages/DeveloperSettings'

Vue.use(VueRouter);

const router = new VueRouter({
    mode: 'history',
    linkActiveClass: 'active',
    canReuse: false,
    routes: [
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
            redirect: '/account/licenses/craft',
        },
        {
            path: '/account/licenses/craft',
            component: LicensesCraft,
        },
        {
            path: '/account/licenses/craft/:id',
            component: LicensesCraftDetails
        },
        {
            path: '/account/licenses/plugins',
            component: LicensesPlugins
        },
        {
            path: '/account/licenses/plugins/:id',
            component: LicensesPluginsDetails
        },
        {
            path: '/account/licenses/claim',
            component: LicensesClaim
        },
        {
            path: '/account/licenses/renew',
            component: LicensesRenew
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
            path: '/developer',
            redirect: '/developer/plugins',
        },
        {
            path: '/developer/plugins',
            name: 'Plugins',
            component: Plugins
        },
        {
            path: '/developer/add-plugin',
            component: PluginsEdit,
        },
        {
            path: '/developer/plugins/:id',
            name: 'PluginsEdit',
            component: PluginsEdit,
        },
        {
            path: '/developer/sales',
            name: 'Sales',
            component: Sales
        },
        {
            path: '/developer/sales/:id',
            name: 'SalesDetails',
            component: SalesDetails,
        },
        {
            path: '/developer/settings',
            name: 'DeveloperSettings',
            component: DeveloperSettings
        },
    ]
});

router.beforeEach((to, from, next) => {
    if(router.app.$refs.authManager) {
        router.app.$refs.authManager.renewSession();
    }

    next();
});

export default router;

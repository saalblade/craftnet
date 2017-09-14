import Vue from 'vue'
import Router from 'vue-router'
import Billing from '../pages/Billing'
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
import PluginsSubmit from '../pages/PluginsSubmit'
import Settings from '../pages/Settings'

Vue.use(Router)

export default new Router({
    mode: 'history',
    linkActiveClass: 'active',
    routes: [
        {
            path: '/',
            redirect: '/licenses',
        },
        {
            path: '/licenses',
            name: 'Licenses',
            component: Licenses,
            redirect: '/licenses/craft',
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
            path: '/billing',
            name: 'Billing',
            component: Billing
        },
        {
            path: '/settings',
            name: 'Settings',
            component: Settings
        },
        {
            path: '/customers',
            name: 'Customers',
            component: Customers
        },
        {
            path: '/customers/:id',
            name: 'CustomersDetails',
            component: CustomersDetails
        },
        {
            path: '/plugins',
            name: 'Plugins',
            component: Plugins,
            children: [
                {
                    path: '',
                    component: PluginsIndex
                },
                {
                    path: 'submit',
                    component: PluginsSubmit
                },
                {
                    path: ':id',
                    name: 'PluginsEdit',
                    component: PluginsEdit,
                }

            ]
        },
        {
            path: '/payments',
            name: 'Payments',
            component: Payments
        },
        {
            path: '/payments/:id',
            name: 'PaymentsDetails',
            component: PaymentsDetails,
        },
        {
            path: '/payouts',
            name: 'Payouts',
            component: Payouts,
        },
        {
            path: '/payouts/:id',
            name: 'PayoutsDetails',
            component: PayoutsDetails,
        }
    ]
})

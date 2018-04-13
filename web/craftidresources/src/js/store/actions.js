import * as types from './mutation-types'
import accountApi from '../api/account'

export const getCraftIdData = ({commit}) => {
    return new Promise((resolve, reject) => {
        accountApi.getCraftIdData(response => {
                commit(types.RECEIVE_CRAFT_ID_DATA, {response});
                commit(types.RECEIVE_PLUGINS, {plugins: response.data.plugins});
                commit(types.RECEIVE_UPCOMING_INVOICE, {upcomingInvoice: response.data.upcomingInvoice});
                commit(types.RECEIVE_SALES, {sales: response.data.sales});
                commit(types.RECEIVE_CMS_LICENSES, {cmsLicenses: response.data.cmsLicenses});
                commit(types.RECEIVE_PLUGIN_LICENSES, {pluginLicenses: response.data.pluginLicenses});
                commit(types.RECEIVE_HAS_API_TOKEN, {hasApiToken: response.data.currentUser.hasApiToken});
                commit(types.RECEIVE_APPS, {apps: response.data.apps});
                commit(types.RECEIVE_CURRENT_USER, {currentUser: response.data.currentUser});
                commit(types.RECEIVE_BILLING_ADDRESS, {billingAddress: response.data.billingAddress});
                resolve(response);
            },
            response => {
                reject(response);
            })
    })
};

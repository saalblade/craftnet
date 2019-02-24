import Vue from 'vue'
import Vuex from 'vuex'
import account from './modules/account'
import app from './modules/app'
import apps from './modules/apps'
import cart from './modules/cart'
import craftId from './modules/craft-id'
import licenses from './modules/licenses'
import partner from './modules/partner'
import plugins from './modules/plugins'
import pluginStore from './modules/plugin-store'
import stripe from './modules/stripe'

Vue.use(Vuex);

export default new Vuex.Store({
    strict: true,
    modules: {
        account,
        app,
        apps,
        cart,
        craftId,
        licenses,
        partner,
        plugins,
        pluginStore,
        stripe,
    }
})

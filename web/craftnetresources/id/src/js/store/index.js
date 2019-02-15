import Vue from 'vue'
import Vuex from 'vuex'
import account from './modules/account'
import app from './modules/app'
import apps from './modules/apps'
import cart from './modules/cart'
import craftId from './modules/craft-id'
import developers from './modules/developers'
import licenses from './modules/licenses'
import partner from './modules/partner'
import pluginStore from './modules/plugin-store'

Vue.use(Vuex);

export default new Vuex.Store({
    strict: true,
    modules: {
        account,
        app,
        apps,
        cart,
        craftId,
        developers,
        licenses,
        partner,
        pluginStore,
    }
})

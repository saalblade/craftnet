import Vue from 'vue'
import Vuex from 'vuex'
import app from './modules/app'
import developers from './modules/developers'
import account from './modules/account'
import licenses from './modules/licenses'
import craftId from './modules/craft-id'
import cart from './modules/cart'
import pluginStore from './modules/plugin-store'

Vue.use(Vuex);

export default new Vuex.Store({
    strict: true,
    modules: {
        app,
        craftId,
        account,
        developers,
        licenses,
        cart,
        pluginStore,
    }
})

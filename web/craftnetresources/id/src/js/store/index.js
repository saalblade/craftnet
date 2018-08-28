import Vue from 'vue'
import Vuex from 'vuex'
import developers from './modules/developers'
import account from './modules/account'
import licenses from './modules/licenses'
import craftId from './modules/craftid'
import cart from './modules/cart'
import pluginstore from './modules/pluginstore'

Vue.use(Vuex);

export default new Vuex.Store({
    strict: true,
    modules: {
        craftId,
        account,
        developers,
        licenses,
        cart,
        pluginstore,
    }
})

import Vue from 'vue'
import Vuex from 'vuex'
import developers from './modules/developers'
import account from './modules/account'
import licenses from './modules/licenses'
import craftId from './modules/craftId'

Vue.use(Vuex);

export default new Vuex.Store({
    strict: true,
    modules: {
        craftId,
        account,
        developers,
        licenses,
    }
})

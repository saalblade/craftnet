import Vue from 'vue'
import Vuex from 'vuex'
import developers from './modules/developers'
import account from './modules/account'
import * as actions from './actions'
import * as mutations from './mutations'
import * as getters from './getters'

Vue.use(Vuex);

export default new Vuex.Store({
    strict: true,
    state: {
        craftId: null,
        stripeAccount: null,
        stripeCustomer: null,
        stripeCard: null,
    },
    getters,
    actions,
    mutations,
    modules: {
        developers,
        account,
    }
})

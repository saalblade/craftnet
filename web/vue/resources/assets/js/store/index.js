import api from '../api'
import Vue from 'vue'
import Vuex from 'vuex'
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
})

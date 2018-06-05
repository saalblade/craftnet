import Vue from 'vue'
import Vuex from 'vuex'
import pluginStore from './modules/pluginstore'

Vue.use(Vuex)

export default new Vuex.Store({
    strict: true,
    modules: {
        pluginStore,
    },
})

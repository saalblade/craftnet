import * as types from '../mutation-types'
import Vue from 'vue'
import Vuex from 'vuex'
import partnerApi from '../../api/partner'

Vue.use(Vuex)

/**
* State
*/
const state = {
    partner: null
}

/**
* Getters
*/
const getters = {
    showPartnerFeatures(state) {
        return state.enablePartnerFeatures;
    }
}

/**
* Actions
*/
const actions = {
    initPartner({commit, state}) {
        return new Promise((resolve, reject) => {
            if (state.partner) {
                resolve({data: {partner: state.partner}})
            } else {
                partnerApi.getPartner(response => {
                    if (response.data && !response.data.error) {
                        commit(types.RECEIVE_PARTNER, response.data.partner)
                        resolve(response)
                    } else {
                        reject(response)
                    }
                }, response => {
                    reject(response)
                })
            }
        })
    },

    patchPartner({commit, state}, draft) {
        return new Promise((resolve, reject) => {
            partnerApi.patchPartner(
                draft,
                response => {
                    if (response.data && !response.data.error) {
                        commit(types.RECEIVE_PARTNER, response.data.partner)
                        resolve(response)
                    } else {
                        reject('Validation errors')
                    }
                }, error => {
                    reject(error.statusText)
                })
        })
    }
}

/**
* Mutations
*/
const mutations = {
    [types.RECEIVE_PARTNER](state, partner) {
        state.partner = partner
    }
}

export default {
    state,
    getters,
    actions,
    mutations
}

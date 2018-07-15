import * as types from '../mutation-types'
import Vue from 'vue'
import Vuex from 'vuex'
import partnerApi from '../../api/partner'

Vue.use(Vuex)

/**
* State
*/
const state = {
    partnerProfile: null
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
    initPartnerProfile({commit, state}) {
        return new Promise((resolve, reject) => {
            if (state.partnerProfile) {
                resolve({data: {profile: state.partnerProfile}})
            } else {
                partnerApi.getPartnerProfile(1, response => {
                    if (response.data && !response.data.error) {
                        commit(types.RECEIVE_PARTNER_PROFILE, response.data.profile);
                        resolve(response);
                    } else {
                        reject(response);
                    }
                }, response => {
                    reject(response);
                })
            }
        })
    }
}

/**
* Mutations
*/
const mutations = {
    [types.RECEIVE_PARTNER_PROFILE](state, partnerProfile) {
        state.partnerProfile = partnerProfile
    }
}

export default {
    state,
    getters,
    actions,
    mutations
}

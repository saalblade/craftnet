import * as types from '../../store/mutation-types'
import Vue from 'vue'
import Vuex from 'vuex'
import partnerApi from '../../api/partners'

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

    patchPartner({commit, state}, {draft, files}) {
        // eslint-disable-next-line
        console.warn('store patchPartner()', files)
        // eslint-disable-next-line
        console.warn('store patchPartner() partnerId', state.partner.id)
        return new Promise((resolve, reject) => {
            partnerApi.patchPartner(
                draft,
                files,
                state.partner.id,
                response => {
                    if (response.data.success) {
                        commit(types.RECEIVE_PARTNER, response.data.partner)
                    }
                    resolve(response)

                }, error => {
                    if (error.data) {
                        reject(error.data.error)
                    }

                    reject(error.statusText)
                })
        })
    },

    patchPartnerLocations({commit, state}, locations) {
        return new Promise((resolve, reject) => {
            partnerApi.patchPartnerLocations(
                locations,
                state.partner.id,
                response => {
                    if (response.data.success) {
                        commit(types.RECEIVE_PARTNER_LOCATIONS, response.data.partner.locations)
                    }
                    resolve(response)
                }, error => {
                    reject(error.statusText)
                })
        })
    },

    patchPartnerProjects({commit, state}, projects) {
        return new Promise((resolve, reject) => {
            partnerApi.patchPartnerProjects(
                projects,
                state.partner.id,
                response => {
                    if (response.data.success) {
                        commit(types.RECEIVE_PARTNER_PROJECTS, response.data.partner.projects)
                    }
                    resolve(response)
                }, error => {
                    reject(error.statusText)
                })
        })
    },
}

/**
* Mutations
*/
const mutations = {
    [types.RECEIVE_PARTNER](state, partner) {
        state.partner = partner
    },
    [types.RECEIVE_PARTNER_LOCATIONS](state, locations) {
        state.partner.locations = locations
    },
    [types.RECEIVE_PARTNER_PROJECTS](state, projects) {
        state.partner.projects = projects
    },
}

export default {
    state,
    getters,
    actions,
    mutations
}

import Vue from 'vue'
import Vuex from 'vuex'
import craftIdApi from '../../api/craft-id'

Vue.use(Vuex)

/**
 * State
 */
const state = {
    categories: [],
    countries: [],
}

/**
 * Getters
 */
const getters = {
    countryOptions(state) {
        let options = []

        for (let iso in state.countries) {
            if (state.countries.hasOwnProperty(iso)) {
                options.push({
                    label: state.countries[iso].name,
                    value: iso,
                })
            }
        }

        return options
    },

    stateOptions(state) {
        return iso => {
            let options = []

            if (!state.countries[iso] || (state.countries[iso] && !state.countries[iso].states)) {
                return []
            }

            const states = state.countries[iso].states

            for (let stateIso in states) {
                if (states.hasOwnProperty(stateIso)) {
                    options.push({
                        label: states[stateIso],
                        value: stateIso,
                    })
                }
            }

            return options
        }
    },
}

/**
 * Actions
 */
const actions = {
    getCraftIdData({commit}) {
        return new Promise((resolve, reject) => {
            craftIdApi.getCraftIdData()
                .then((response) => {
                    commit('updateCategories', {categories: response.data.categories})
                    commit('updateCountries', {countries: response.data.countries})
                    resolve(response)
                })
                .catch((response) => {
                    reject(response)
                })
        })
    },
}

/**
 * Mutations
 */
const mutations = {
    updateCategories(state, {categories}) {
        state.categories = categories
    },

    updateCountries(state, {countries}) {
        state.countries = countries
    },
}

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
}

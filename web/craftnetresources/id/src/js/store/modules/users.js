import Vue from 'vue'
import Vuex from 'vuex'
import usersApi from '../../api/users'

Vue.use(Vuex)

/**
 * State
 */
const state = {
    currentUser: null,
    currentUserLoaded: false,
}

/**
 * Getters
 */
const getters = {
    userIsInGroup(state) {
        return handle => {
            return state.currentUser.groups.find(g => g.handle === handle)
        }
    },
}

/**
 * Actions
 */
const actions = {
    saveUser({commit}, user) {
        return new Promise((resolve, reject) => {
            usersApi.saveUser(user)
                .then((response) => {
                    if (!response.data.errors) {
                        commit('saveUser', {user, response})
                        resolve(response)
                    } else {
                        reject(response)
                    }
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
    saveUser(state, {user}) {
        for (let attribute in user) {
            if (attribute === 'id' || attribute === 'email') {
                continue
            }

            state.currentUser[attribute] = user[attribute]

            if (user.enablePluginDeveloperFeatures) {
                let groupExists = state.currentUser.groups.find(g => g.handle === 'developers')

                if (!groupExists) {
                    state.currentUser.groups.push({
                        id: 1,
                        name: 'Developers',
                        handle: 'developers',
                    })
                }
            }
        }
    },

    updateCurrentUser(state, {currentUser}) {
        state.currentUser = currentUser
    },

    updateCurrentUserLoaded(state, loaded) {
        state.currentUserLoaded = loaded
    },

    deleteUserPhoto(state, {response}) {
        state.currentUser.photoId = response.data.photoId
        state.currentUser.photoUrl = response.data.photoUrl
    },

    uploadUserPhoto(state, {response}) {
        state.currentUser.photoId = response.data.photoId
        state.currentUser.photoUrl = response.data.photoUrl
    },
}

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
}

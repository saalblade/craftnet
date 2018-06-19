/**
 * State
 */
export const state = () => ({
    showingSidebar: false,
})


/**
 * Mutations
 */
export const mutations = {

    toggleSidebar(state) {
        state.showingSidebar = !state.showingSidebar
    },

    hideSidebar(state) {
        state.showingSidebar = false
    },

}
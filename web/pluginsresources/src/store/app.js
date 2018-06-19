/**
 * State
 */
export const state = () => ({
    showingSidebar: false,
    searchQuery: '',
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

    updateSearchQuery(state, searchQuery) {
        state.searchQuery = searchQuery
    }

}
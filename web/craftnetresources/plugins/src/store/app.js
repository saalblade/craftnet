/**
 * State
 */
export const state = () => ({
    pageMeta: null,
    showingNavigation: false,
    searchQuery: '',
    showingScreenshotModal: false,
    screenshotModalImages: null,
    screenshotModalImageKey: 0,
})


/**
 * Mutations
 */
export const mutations = {

    toggleNavigation(state) {
        state.showingNavigation = !state.showingNavigation
    },

    toggleScreenshotModal(state) {
        state.showingScreenshotModal = !state.showingScreenshotModal
    },

    hideNavigation(state) {
        state.showingNavigation = false
    },

    updateSearchQuery(state, searchQuery) {
        state.searchQuery = searchQuery
    },

    updateShowingScreenshotModal(state, show) {
        state.showingScreenshotModal = show
    },

    updateScreenshotModalImages(state, images) {
        state.screenshotModalImages = images
    },

    updateScreenshotModalImageKey(state, key) {
        state.screenshotModalImageKey = key
    },

    updatePageMeta(state, pageMeta) {
        state.pageMeta = pageMeta
    },

}
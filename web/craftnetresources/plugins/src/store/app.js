/**
 * State
 */
export const state = () => ({
    pageMeta: null,
    showingSidebar: false,
    searchQuery: '',
    showingScreenshotModal: false,
    screenshotModalImages: null,
    screenshotModalImageKey: 0,
})


/**
 * Mutations
 */
export const mutations = {

    toggleSidebar(state) {
        state.showingSidebar = !state.showingSidebar
    },

    toggleScreenshotModal(state) {
        state.showingScreenshotModal = !state.showingScreenshotModal
    },

    hideSidebar(state) {
        state.showingSidebar = false
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
/**
 * State
 */
export const state = () => ({
    pageMeta: null,
    showingSidebar: false,
    searchQuery: '',
    showingScreenshotModal: false,
    screenshotModalImages: null,
    stickyHeader: false,
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

    updateScreenshotModalImages(state, images) {
        state.screenshotModalImages = images
    },

    updatePageMeta(state, pageMeta) {
        state.pageMeta = pageMeta
    },

    updateStickyHeader(state, stickyHeader) {
        state.stickyHeader = stickyHeader
    }

}
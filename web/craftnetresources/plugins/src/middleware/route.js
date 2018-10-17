export default function ({store, route, app}) {
    if(process.server) {
        return
    }

    if (route.path !== '/search') {
        store.commit('app/updateSearchQuery', '')
    } else {
        store.commit('app/updateSearchQuery', route.query.q)
    }

    return new Promise((resolve) => {
        setTimeout(function() {
            resolve()
        }, 50)
        setTimeout(function() {
            store.commit('app/hideSidebar')
        }, 100)
    })
}

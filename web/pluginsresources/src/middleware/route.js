export default function ({store}) {
    if(process.server) {
        return
    }

    return new Promise((resolve) => {
        setTimeout(function() {
            resolve();
        }, 50);
        setTimeout(function() {
            store.commit('app/hideSidebar')
        }, 100);
    })
}
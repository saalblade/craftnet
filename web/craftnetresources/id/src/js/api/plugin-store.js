import axios from 'axios'

export default {
    getMeta() {
        return axios.get(process.env.VUE_APP_CRAFT_API_ENDPOINT + '/plugin-store/meta', {withCredentials: false})
    },

    getPlugins(pluginIds) {
        return axios.get(process.env.VUE_APP_CRAFT_API_ENDPOINT + '/plugins', {
            params: {
                ids: pluginIds.join(',')
            },
            withCredentials: false
        })
    }
}
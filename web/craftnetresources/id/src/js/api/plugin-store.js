import axios from 'axios';

export default {
    getData() {
        return axios.get(process.env.VUE_APP_CRAFT_API_ENDPOINT + '/plugin-store', {withCredentials: false})
    }
}
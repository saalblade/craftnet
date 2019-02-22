/* global Craft */

import axios from 'axios';

export default {

    loadDetails(repositoryUrl, params) {
        return axios.post(Craft.actionUrl + '/craftnet/plugins/load-details&repository=' + encodeURIComponent(repositoryUrl), params)
    }

}

/* global Craft */

import axios from 'axios';

export default {

    getApps() {
        return axios.get(Craft.actionUrl + '/craftnet/id/apps/get-apps')
    }

}

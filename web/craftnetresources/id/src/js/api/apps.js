/* global Craft */

import axios from 'axios';

export default {
    getApps() {
        return axios.get(Craft.actionUrl + '/craftnet/id/apps/get-apps')
    },

    disconnect(appHandle, cb, cbError) {
        const data = {
            appTypeHandle: appHandle
        }

        axios.post(Craft.actionUrl + '/craftnet/id/apps/disconnect', qs.stringify(data))
            .then(response => cb(response))
            .catch(error => cbError(error.response));
    },
}

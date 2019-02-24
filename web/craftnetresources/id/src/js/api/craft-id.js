/* global Craft */

import axios from 'axios'

export default {
    getCraftIdData() {
        return axios.post(Craft.actionUrl + '/craftnet/id/craft-id', {}, {
                headers: {
                    'X-CSRF-Token': Craft.csrfTokenValue,
                }
            })
    },
}

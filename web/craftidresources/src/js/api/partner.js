import axios from 'axios';
import qs from 'qs';

export default {
    getPartnerProfile(cb, cbError) {
        axios.post(Craft.actionUrl + '/craftnet/partners/fetch-partner', null, {
            headers: {
                'X-CSRF-Token': Craft.csrfTokenValue,
            }
        })
        .then(response => cb(response))
        .catch(error => cbError(error.response));
    },

    patchPartnerProfile(patchObj, cb, cbError) {
        let response = {data: {}}

        // patchObj is a vm (model) so for now, before we have
        // an endpoint, we'll simplify it for merging below
        patchObj = JSON.parse(JSON.stringify(patchObj))

        // we'll get the whole partner profile back
        response.data.partnerProfile = Object.assign({}, devProfile, patchObj)

        setTimeout(() => {
            cb(response)
        }, 500)
    },
}

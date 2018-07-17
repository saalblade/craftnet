import axios from 'axios';
import qs from 'qs';

const devProfile = {
    agencySize: null,
    businessName: '',
    businessSummary: '',
    capabilities: ['Commerce'],
    craftSites: [],
    locations: [],
    minimumBudget: null,
    msaLink: '',
    primaryContactName: '',
    primaryEmail: '',
    primaryPhone: '',
}

export default {
    getPartnerProfile(id, cb, cbError) {
        let response = {data: {}}
        response.data.partnerProfile = Object.assign({}, devProfile)

        setTimeout(() => {
            cb(response)
        }, 500)
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
    }
}

import axios from 'axios';
import qs from 'qs';

const devProfile = {
    agencySize: null,
    businessName: '',
    businessSummary: '',
    capabilities: ['Commerce'],
    craftSites: [],
    locations: [{
        title: 'First Location',
        addressLine1: '100 Any Street',
        addressLine2: 'Suite A',
        businessCity: 'Any Town',
        businessState: 'FL',
        businessCountry: 'USA',
        phone: '999-999-9999',
        email: 'hello@sayhi.com',
    },
    {
        title: 'First Location',
        addressLine1: '100 Any Street',
        addressLine2: 'Suite A',
        businessCity: 'Any Town',
        businessState: 'FL',
        businessCountry: 'USA',
        phone: '999-999-9999',
        email: 'hello@sayhi.com',
    }],
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
    },
}

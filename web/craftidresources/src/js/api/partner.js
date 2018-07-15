import axios from 'axios';
import qs from 'qs';

export default {
    getPartnerProfile(developerId, cb, cbError) {
        let response = {data: {}}
        resonpose.data.partnerProfile = {
            agencySize: null,
            businessName: '',
            businessSummary: '',
            capabilities: [],
            craftSites: [],
            locations: [],
            minimumBudget: null,
            msaLink: '',
            primaryContactName: '',
            primaryEmail: '',
            primaryPhone: '',
        }

        setTimeout(() => {
            cb(response)
        }, 1500);
    }
}

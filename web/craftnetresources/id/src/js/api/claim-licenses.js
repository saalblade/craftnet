import axios from 'axios';

export default {
    claimLicensesByEmail(email, cb, cbError) {
        const data = {
            email: email,
        }

        axios.post(Craft.actionUrl + '/craftnet/id/claim-licenses', qs.stringify(data), {
                headers: {
                    'X-CSRF-Token': Craft.csrfTokenValue,
                }
            })
            .then(response => cb(response))
            .catch(error => cbError(error.response));
    },
}
import axios from 'axios';
import qs from 'qs';

export default {
    getPartner(cb, cbError) {
        axios.post(Craft.actionUrl + '/craftnet/partners/fetch-partner', null, {
            headers: {
                'X-CSRF-Token': Craft.csrfTokenValue,
            }
        })
        .then(response => cb(response))
        .catch(error => cbError(error.response));
    },

    patchPartner(data, cb, cbError) {
        let formData = new FormData()

        for (let attribute in data) {
            switch (attribute) {
                case 'capabilities':
                    for (let i = 0; i < data[attribute].length; i++) {
                        formData.append(attribute + '[]', data[attribute][i])
                    }
                    break;

                default:
                    formData.append(attribute, data[attribute])
                    break;
            }
        }

        axios.post(Craft.actionUrl + '/craftnet/partners/patch-partner', formData, {
            headers: {
                'X-CSRF-Token': Craft.csrfTokenValue,
            }
        })
            .then(response => cb(response))
            .catch(error => cbError(error.response));
    }
}

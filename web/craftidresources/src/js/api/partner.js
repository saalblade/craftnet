import axios from 'axios'
import qs from 'qs'

export default {
    getPartner(cb, cbError) {
        axios.post(Craft.actionUrl + '/craftnet/partners/fetch-partner', null, {
            headers: {
                'X-CSRF-Token': Craft.csrfTokenValue,
            }
        })
        .then(response => cb(response))
        .catch(error => cbError(error.response))
    },

    patchPartner(data, cb, cbError) {
        let formData = new FormData()
        formData.append('scenario', 'scenarioBaseInfo')

        for (let prop in data) {
            switch (prop) {
                case 'capabilities':
                    for (let i = 0; i < data[prop].length; i++) {
                        formData.append(prop + '[]', data[prop][i])
                    }
                    break

                default:
                    formData.append(prop, data[prop])
                    break
            }
        }

        axios.post(Craft.actionUrl + '/craftnet/partners/patch-partner', formData, {
            headers: {
                'X-CSRF-Token': Craft.csrfTokenValue,
            }
        })
            .then(response => cb(response))
            .catch(error => cbError(error.response));
    },

    patchPartnerLocations(locations, partnerId, cb, cbError) {
        let formData = new FormData()
        formData.append('id', partnerId)
        formData.append('scenario', 'scenarioLocations')

        locations.forEach(location => {
            const id = location.id

            for (let prop in location) {
                if (prop !== 'id') {
                    formData.append(
                        `locations[${ location.id }][${prop}]`,
                        location[prop]
                    )
                }
            }
        })

        axios.post(Craft.actionUrl + '/craftnet/partners/patch-partner', formData, {
            headers: {
                'X-CSRF-Token': Craft.csrfTokenValue,
            }
        })
            .then(response => cb(response))
            .catch(error => cbError(error.response));
    }
}

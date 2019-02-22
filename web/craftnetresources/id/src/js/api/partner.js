/* global Craft */

import axios from 'axios'

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

    patchPartner(data, files, partnerId, cb, cbError) {
        let formData = new FormData()
        formData.append('scenario', 'scenarioBaseInfo')
        formData.append('id', partnerId)

        // eslint-disable-next-line
        console.warn('api patchPartner()', files)

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

        formData.append('logoAssetId[]', data.logo.id)

        if (files.length) {
            formData.append('logo', files[0])
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
    },

    patchPartnerProjects(projects, partnerId, cb, cbError) {
        let formData = new FormData()
        formData.append('id', partnerId)
        formData.append('scenario', 'scenarioProjects')

        for (let i in projects) {
            let project = projects[i]

            for (let prop in project) {
                if (prop !== 'id' && prop !== 'screenshots') {
                    formData.append(
                        `projects[${ project.id }][${prop}]`,
                        project[prop]
                    )
                }
            }

            for (let i in project.screenshots) {
                formData.append(
                    `projects[${project.id}][screenshots][]`,
                    project.screenshots[i]['id']
                )
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

    uploadScreenshots(formData, config) {
        return axios.post(Craft.actionUrl + '/craftnet/partners/upload-screenshots', formData, config)
    }
}

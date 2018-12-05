import axios from 'axios';
import qs from 'qs';
import FormDataHelper from '../helpers/form-data'

export default {

    savePlugin({plugin}, cb, cbError) {
        let formData = new FormData();

        for (let attribute in plugin) {
            if (plugin[attribute] !== null && plugin[attribute] !== undefined) {
                switch (attribute) {
                    case 'iconId':
                    case 'categoryIds':
                    case 'screenshots':
                    case 'screenshotUrls':
                    case 'screenshotIds':
                        for (let i = 0; i < plugin[attribute].length; i++) {
                            FormDataHelper.append(formData, attribute + '[]', plugin[attribute][i]);
                        }
                        break;

                    case 'editions':
                        for (let i = 0; i < plugin[attribute].length; i++) {
                            const edition = plugin[attribute][i]
                            const editionKey = edition.id ? edition.id : 'new';

                            FormDataHelper.append(formData, 'editions['+editionKey+'][price]', edition.price);
                            FormDataHelper.append(formData, 'editions['+editionKey+'][renewalPrice]', edition.renewalPrice);

                            for (let j = 0; j < edition.features.length; j++) {
                                const feature = edition.features[j]
                                FormDataHelper.append(formData, 'editions['+editionKey+'][features]['+j+'][name]', feature.name)
                                FormDataHelper.append(formData, 'editions['+editionKey+'][features]['+j+'][description]', feature.description)
                            }
                        }
                        break;

                    default:
                        FormDataHelper.append(formData, attribute, plugin[attribute]);
                }
            }
        }

        axios.post(Craft.actionUrl + '/craftnet/plugins/save', formData, {
                headers: {
                    'X-CSRF-Token': Craft.csrfTokenValue,
                }
            })
            .then(response => cb(response))
            .catch(error => cbError(error.response));
    },

    submitPlugin(pluginId, cb, cbError) {
        const data = {
            pluginId: pluginId,
        }

        axios.post(Craft.actionUrl + '/craftnet/plugins/submit', qs.stringify(data), {
                headers: {
                    'X-CSRF-Token': Craft.csrfTokenValue,
                }
            })
            .then(response => cb(response))
            .catch(error => cbError(error.response));
    },

    generateApiToken(cb, cbError) {
        axios.post(Craft.actionUrl + '/craftnet/id/account/generate-api-token', {}, {
                headers: {
                    'X-CSRF-Token': Craft.csrfTokenValue,
                }
            })
            .then(response => cb(response))
            .catch(error => cbError(error.response));
    },

}
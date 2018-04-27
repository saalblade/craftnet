import * as types from '../mutation-types'
import Vue from 'vue'
import Vuex from 'vuex'
import licensesApi from '../../api/licenses';

Vue.use(Vuex)

/**
 * State
 */
const state = {
    cmsLicenses: [],
    pluginLicenses: [],
}

/**
 * Getters
 */
const getters = {

    licenses(state) {
        return state.pluginLicenses.concat(state.cmsLicenses);
    },

    expiresSoon(state) {
        return license => {
            const today = new Date()
            let expiryDate = new Date()
            expiryDate.setDate(today.getDate() + 45)

            const expiresOn = new Date(license.expiresOn.date)

            if(expiryDate > expiresOn) {
                return true
            }

            return false
        }
    },

    daysBeforeExpiry(state) {
        return license => {
            const today = new Date()
            const expiresOn = new Date(license.expiresOn.date)
            const diff = expiresOn.getTime() - today.getTime()
            const diffDays = Math.round(diff / (1000 * 60 * 60 * 24))
            return diffDays;
        }
    },

    expiringCmsLicenses(state, getters) {
        return state.cmsLicenses.filter(license => {
            if (license.expired) {
                return false
            }

            if (license.autoRenew) {
                return false
            }
            
            return getters.expiresSoon(license)
        })
    },

    expiringPluginLicenses(state, getters) {
        return state.pluginLicenses.filter(license => {
            if (license.expired) {
                return false
            }

            if (license.autoRenew) {
                return false
            }

            return getters.expiresSoon(license)
        })
    },

}

/**
 * Actions
 */
const actions = {

    claimCmsLicense({commit}, licenseKey) {
        return new Promise((resolve, reject) => {
            licensesApi.claimCmsLicense(licenseKey, response => {
                if (response.data && !response.data.error) {
                    resolve(response);
                } else {
                    reject(response);
                }
            }, response => {
                reject(response);
            })
        })
    },

    claimCmsLicenseFile({commit}, licenseFile) {
        return new Promise((resolve, reject) => {
            licensesApi.claimCmsLicenseFile(licenseFile, response => {
                if (response.data && !response.data.error) {
                    resolve(response);
                } else {
                    reject(response);
                }
            }, response => {
                reject(response);
            })
        })
    },

    claimLicensesByEmail({commit}, email) {
        return new Promise((resolve, reject) => {
            licensesApi.claimLicensesByEmail(email, response => {
                if (response.data && !response.data.error) {
                    resolve(response);
                } else {
                    reject(response);
                }
            }, response => {
                reject(response);
            })
        })
    },

    claimPluginLicense({commit}, licenseKey) {
        return new Promise((resolve, reject) => {
            licensesApi.claimPluginLicense(licenseKey, response => {
                if (response.data && !response.data.error) {
                    resolve(response);
                } else {
                    reject(response);
                }
            }, response => {
                reject(response);
            })
        })
    },

    getCmsLicenses({commit}) {
        return new Promise((resolve, reject) => {
            licensesApi.getCmsLicenses(response => {
                if (response.data && !response.data.error) {
                    commit(types.RECEIVE_CMS_LICENSES, {cmsLicenses: response.data});
                    resolve(response);
                } else {
                    reject(response);
                }
            }, response => {
                reject(response);
            })
        })
    },

    getPluginLicenses({commit}) {
        return new Promise((resolve, reject) => {
            licensesApi.getPluginLicenses(response => {
                if (response.data && !response.data.error) {
                    commit(types.RECEIVE_PLUGIN_LICENSES, {pluginLicenses: response.data});
                    resolve(response);
                } else {
                    reject(response);
                }
            }, response => {
                reject(response);
            })
        })
    },

    releaseCmsLicense({commit}, licenseKey) {
        return new Promise((resolve, reject) => {
            licensesApi.releaseCmsLicense(licenseKey, response => {
                if (response.data && !response.data.error) {
                    commit(types.RELEASE_CMS_LICENSE, {licenseKey});
                    resolve(response);
                } else {
                    reject(response);
                }
            }, response => {
                reject(response);
            })
        })
    },

    releasePluginLicense({commit}, {pluginHandle, licenseKey}) {
        return new Promise((resolve, reject) => {
            licensesApi.releasePluginLicense({pluginHandle, licenseKey}, response => {
                if (response.data && !response.data.error) {
                    commit(types.RELEASE_PLUGIN_LICENSE, {licenseKey});
                    resolve(response);
                } else {
                    reject(response);
                }
            }, response => {
                reject(response);
            })
        })
    },

    saveCmsLicense({commit}, license) {
        return new Promise((resolve, reject) => {
            licensesApi.saveCmsLicense(license, response => {
                if (response.data && !response.data.error) {
                    commit(types.SAVE_CMS_LICENSE, { license: response.data.license });
                    resolve(response);
                } else {
                    reject(response);
                }
            }, response => {
                reject(response);
            })
        })
    },

    savePluginLicense({commit}, license) {
        return new Promise((resolve, reject) => {
            licensesApi.savePluginLicense(license, response => {
                if (response.data && !response.data.error) {
                    commit(types.SAVE_PLUGIN_LICENSE, {license});
                    resolve(response);
                } else {
                    reject(response);
                }
            }, response => {
                reject(response);
            })
        })
    },

}

/**
 * Mutations
 */
const mutations = {

    [types.RECEIVE_CMS_LICENSES](state, {cmsLicenses}) {
        state.cmsLicenses = cmsLicenses;
    },

    [types.RECEIVE_PLUGIN_LICENSES](state, {pluginLicenses}) {
        state.pluginLicenses = pluginLicenses;
    },

    [types.RELEASE_CMS_LICENSE](state, {licenseKey}) {
        state.cmsLicenses.find((l, index, array) => {
            if (l.key === licenseKey) {
                array.splice(index, 1);
                return true;
            }

            return false;
        });
    },

    [types.RELEASE_PLUGIN_LICENSE](state, {licenseKey}) {
        state.pluginLicenses.find((l, index, array) => {
            if (l.key === licenseKey) {
                array.splice(index, 1);
                return true;
            }

            return false;
        });
    },

    [types.SAVE_CMS_LICENSE](state, {license}) {
        let stateLicense = state.cmsLicenses.find(l => l.key == license.key);
        for (let attribute in license) {
            stateLicense[attribute] = license[attribute];
        }
    },

    [types.SAVE_PLUGIN_LICENSE](state, {license}) {
        let stateLicense = state.pluginLicenses.find(l => l.key == license.key);
        for (let attribute in license) {
            stateLicense[attribute] = license[attribute];
        }
    },

    [types.SAVE_LICENSE](state, {license}) {
        let stateLicense = null;
        if (license.type === 'cmsLicense') {
            stateLicense = state.cmsLicenses.find(l => l.id == license.id);
        } else if (license.type === 'pluginLicense') {
            stateLicense = state.pluginLicenses.find(l => l.id == license.id);
        }

        for (let attribute in license) {
            switch (attribute) {
                case 'id':
                case 'type':
                    // ignore
                    break;
                case 'autoRenew':
                    stateLicense[attribute] = (license[attribute] ? 1 : 0);
                    break;
                default:
                    stateLicense[attribute] = license[attribute];
            }
        }
    },

}

export default {
    state,
    getters,
    actions,
    mutations
}

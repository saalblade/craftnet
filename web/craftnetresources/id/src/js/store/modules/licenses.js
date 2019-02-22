import Vue from 'vue'
import Vuex from 'vuex'
import cmsLicensesApi from '../../api/cms-licenses';
import pluginLicensesApi from '../../api/plugin-licenses';
import claimLicensesApi from '../../api/claim-licenses';

Vue.use(Vuex)
Vue.use(require('vue-moment'))

var VueApp = new Vue();

/**
 * State
 */
const state = {
    expiringCmsLicensesTotal: 0,
    expiringPluginLicensesTotal: 0,
}

/**
 * Getters
 */
const getters = {
    expiresSoon() {
        return license => {
            if(!license.expiresOn) {
                return false
            }

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

    daysBeforeExpiry() {
        return license => {
            const today = new Date()
            const expiresOn = new Date(license.expiresOn.date)
            const diff = expiresOn.getTime() - today.getTime()
            const diffDays = Math.round(diff / (1000 * 60 * 60 * 24))
            return diffDays;
        }
    },

    renewableLicenses(state, getters, rootState) {
        return (license, renew) => {
            let renewableLicenses = []


            // CMS license

            const expiryDateOptions = license.expiryDateOptions
            let expiryDate = expiryDateOptions[renew][1]

            renewableLicenses.push({
                type: 'cms-renewal',
                key: license.key,
                description: 'Craft ' + license.editionDetails.name,
                renew: renew,
                expiryDate: expiryDate,
                expiresOn: license.expiresOn,
                edition: license.editionDetails,
            })


            // Plugin licenses

            if (license.pluginLicenses.length > 0) {
                // Renewable plugin licenses
                const renewablePluginLicenses = license.pluginLicenses.filter(pluginLicense => {
                    // Plugin licenses with no `expiresOn` are not renewable
                    if (!pluginLicense.expiresOn) {
                        return false
                    }

                    // Ignore the plugin license if it expires after the CMS license
                    if (!pluginLicense.expired) {
                        const pluginExpiresOn = VueApp.$moment(pluginLicense.expiresOn.date)
                        const expiryDateObject = VueApp.$moment(expiryDate)

                        if(expiryDateObject.diff(pluginExpiresOn) < 0) {
                            return false
                        }
                    }

                    return true
                })

                // Add renewable plugin licenses to the `renewableLicenses` array
                renewablePluginLicenses.forEach(function(renewablePluginLicense) {
                    renewableLicenses.push({
                        type: 'plugin-renewal',
                        key: renewablePluginLicense.key,
                        description: renewablePluginLicense.plugin.name,
                        expiryDate: expiryDate,
                        expiresOn: renewablePluginLicense.expiresOn,
                        edition: renewablePluginLicense.edition,
                    })
                })
            }

            return renewableLicenses
        }
    },
}

/**
 * Actions
 */
const actions = {
    // eslint-disable-next-line
    claimLicensesByEmail({}, email) {
        return new Promise((resolve, reject) => {
            claimLicensesApi.claimLicensesByEmail(email, response => {
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

    // eslint-disable-next-line
    claimCmsLicense({}, licenseKey) {
        return new Promise((resolve, reject) => {
            cmsLicensesApi.claimCmsLicense(licenseKey, response => {
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

    // eslint-disable-next-line
    claimCmsLicenseFile({}, licenseFile) {
        return new Promise((resolve, reject) => {
            cmsLicensesApi.claimCmsLicenseFile(licenseFile, response => {
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

    getExpiringCmsLicensesTotal({commit}) {
        return new Promise((resolve, reject) => {
            cmsLicensesApi.getExpiringCmsLicensesTotal(response => {
                if (response.data && !response.data.error) {
                    commit('updateExpiringCmsLicensesTotal', response.data);
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
            cmsLicensesApi.releaseCmsLicense(licenseKey, response => {
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

    saveCmsLicense({commit}, license) {
        return new Promise((resolve, reject) => {
            cmsLicensesApi.saveCmsLicense(license, response => {
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

    // eslint-disable-next-line
    claimPluginLicense({}, licenseKey) {
        return new Promise((resolve, reject) => {
            pluginLicensesApi.claimPluginLicense(licenseKey, response => {
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

    getExpiringPluginLicensesTotal({commit}) {
        return new Promise((resolve, reject) => {
            pluginLicensesApi.getExpiringPluginLicensesTotal(response => {
                if (response.data && !response.data.error) {
                    commit('updateExpiringPluginLicensesTotal', response.data);
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
            pluginLicensesApi.releasePluginLicense({pluginHandle, licenseKey}, response => {
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

    savePluginLicense({commit}, license) {
        return new Promise((resolve, reject) => {
            pluginLicensesApi.savePluginLicense(license, response => {
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
}

/**
 * Mutations
 */
const mutations = {
    updateExpiringCmsLicensesTotal(state, total) {
        state.expiringCmsLicensesTotal = total
    },

    updateExpiringPluginLicensesTotal(state, total) {
        state.expiringPluginLicensesTotal = total
    }
}

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
}

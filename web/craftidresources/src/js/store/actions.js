import * as types from './mutation-types'
import api from '../api'


/**
 * User
 */

export const connectAppCallback = ({commit}, apps) => {
    commit(types.CONNECT_APP_CALLBACK, {apps})
};

export const deleteUserPhoto = ({commit}) => {
    return new Promise((resolve, reject) => {
        api.deleteUserPhoto(response => {
            commit(types.DELETE_USER_PHOTO, {response});
            resolve(response);
        }, response => {
            reject(response);
        })
    })
};

export const disconnectApp = ({commit}, appHandle) => {
    return new Promise((resolve, reject) => {
        api.disconnectApp(appHandle, response => {
                commit(types.DISCONNECT_APP, {appHandle});
                resolve(response);
            },
            response => {
                reject(response);
            })
    })
};

export const getCraftIdData = ({commit}) => {
    return new Promise((resolve, reject) => {
        let userId = window.currentUserId;

        api.getCraftIdData(userId, response => {
                commit(types.RECEIVE_CRAFT_ID_DATA, {response});
                resolve(response);
            },
            response => {
                reject(response);
            })
    })
};

export const saveUser = ({commit}, user) => {
    return new Promise((resolve, reject) => {
        api.saveUser(user, response => {
                if (!response.data.errors) {
                    commit(types.SAVE_USER, {user, response});
                    resolve(response);
                } else {
                    reject(response);
                }
            },
            response => {
                reject(response);
            })
    })
};

export const uploadUserPhoto = ({commit}, data) => {
    return new Promise((resolve, reject) => {
        api.uploadUserPhoto(data, response => {
            commit(types.UPLOAD_USER_PHOTO, {response});
            resolve(response);
        }, response => {
            reject(response);
        })
    })
};


/**
 * Licenses
 */

export const claimCmsLicense = ({commit}, licenseKey) => {
    return new Promise((resolve, reject) => {
        api.claimCmsLicense(licenseKey, response => {
            if (response.data && !response.data.error) {
                commit(types.CLAIM_CMS_LICENSE, {licenseKey});
                resolve(response);
            } else {
                reject(response);
            }
        }, response => {
            reject(response);
        })
    })
};

export const claimCmsLicenseFile = ({commit}, licenseFile) => {
    return new Promise((resolve, reject) => {
        api.claimCmsLicenseFile(licenseFile, response => {
            if (response.data && !response.data.error) {
                commit(types.CLAIM_CMS_LICENSE_FILE, {licenseFile});
                resolve(response);
            } else {
                reject(response);
            }
        }, response => {
            reject(response);
        })
    })
};

export const claimPluginLicense = ({commit}, licenseKey) => {
    return new Promise((resolve, reject) => {
        api.claimPluginLicense(licenseKey, response => {
            if (response.data && !response.data.error) {
                commit(types.CLAIM_PLUGIN_LICENSE, {licenseKey});
                resolve(response);
            } else {
                reject(response);
            }
        }, response => {
            reject(response);
        })
    })
};

export const generateApiToken = ({commit}) => {
    return new Promise((resolve, reject) => {
        api.generateApiToken(response => {
            if (response.data && !response.data.error) {
                commit(types.GENERATE_API_TOKEN, {response});
                resolve(response);
            } else {
                reject(response);
            }
        }, response => {
            reject(response);
        })
    })
};


export const getCmsLicenses = ({commit}) => {
    return new Promise((resolve, reject) => {
        api.getCmsLicenses(response => {
            if (response.data && !response.data.error) {
                commit(types.GET_CMS_LICENSES, {response});
                resolve(response);
            } else {
                reject(response);
            }
        }, response => {
            reject(response);
        })
    })
};

export const getPluginLicenses = ({commit}) => {
    return new Promise((resolve, reject) => {
        api.getPluginLicenses(response => {
            if (response.data && !response.data.error) {
                commit(types.GET_PLUGIN_LICENSES, {response});
                resolve(response);
            } else {
                reject(response);
            }
        }, response => {
            reject(response);
        })
    })
};

export const releaseCmsLicense = ({commit}, licenseKey) => {
    return new Promise((resolve, reject) => {
        api.releaseCmsLicense(licenseKey, response => {
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
};

export const releasePluginLicense = ({commit}, {pluginHandle, licenseKey}) => {
    return new Promise((resolve, reject) => {
        api.releasePluginLicense({pluginHandle, licenseKey}, response => {
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
};

export const saveCmsLicense = ({commit}, license) => {
    return new Promise((resolve, reject) => {
        api.saveCmsLicense(license, response => {
            if (response.data && !response.data.error) {
                commit(types.SAVE_CMS_LICENSE, {license});
                resolve(response);
            } else {
                reject(response);
            }
        }, response => {
            reject(response);
        })
    })
};

export const savePluginLicense = ({commit}, license) => {
    return new Promise((resolve, reject) => {
        api.savePluginLicense(license, response => {
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
};

export const saveLicense = ({commit}, license) => {
    return new Promise((resolve, reject) => {
        api.saveLicense(license, response => {
            if (!response.data.errors) {
                commit(types.SAVE_LICENSE, {license});
                resolve(response);
            } else {
                reject(response);
            }
        }, response => {
            reject(response);
        })
    })
};


/**
 * Plugins
 */

export const savePlugin = ({commit}, {plugin}) => {
    return new Promise((resolve, reject) => {
        api.savePlugin({plugin}, response => {
            if (response.data.success) {
                commit(types.SAVE_PLUGIN, {plugin, response});
                resolve(response);
            } else {
                reject(response);
            }
        }, response => {
            reject(response);
        })
    })
};

export const submitPlugin = ({commit}, pluginId) => {
    return new Promise((resolve, reject) => {
        api.submitPlugin(pluginId, response => {
            if (response.data.success) {
                commit(types.SUBMIT_PLUGIN, {pluginId});
                resolve(response);
            } else {
                reject(response);
            }
        }, response => {
            reject(response);
        })
    })
};


/**
 * Cards
 */

export const removeCard = ({commit}) => {
    return new Promise((resolve, reject) => {
        api.removeCard(response => {
            commit(types.REMOVE_CARD);
            resolve(response);
        }, response => {
            reject(response);
        })
    })
};

export const saveCard = ({commit}, source) => {
    return new Promise((resolve, reject) => {
        api.saveCard(source, response => {
            commit(types.SAVE_CARD, {response});
            resolve(response);
        }, response => {
            reject(response);
        })
    })
};


/**
 * Stripe
 */

export const disconnectStripeAccount = ({commit}) => {
    return new Promise((resolve, reject) => {
        api.disconnectStripeAccount(response => {
            commit(types.DISCONNECT_STRIPE_ACCOUNT);
            resolve(response);
        }, response => {
            reject(response);
        })
    })
};

export const getStripeAccount = ({commit}) => {
    return new Promise((resolve, reject) => {
        api.getStripeAccount(response => {
            commit(types.RECEIVE_STRIPE_ACCOUNT, {response});
            resolve(response);
        }, response => {
            reject(response);
        })
    })
};

export const getStripeCustomer = ({commit}) => {
    return new Promise((resolve, reject) => {
        api.getStripeCustomer(response => {
            commit(types.RECEIVE_STRIPE_CUSTOMER, {response});
            commit(types.RECEIVE_STRIPE_CARD, {response});
            resolve(response);
        }, response => {
            reject(response);
        })
    })
};

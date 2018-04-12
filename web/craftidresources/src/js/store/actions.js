import * as types from './mutation-types'
import accountApi from '../api/account'
import developerApi from '../api/developer'
import licensesApi from '../api/licenses'


/**
 * Account
 */

export const connectAppCallback = ({commit}, apps) => {
    commit(types.CONNECT_APP_CALLBACK, {apps})
};

export const deleteUserPhoto = ({commit}) => {
    return new Promise((resolve, reject) => {
        accountApi.deleteUserPhoto(response => {
            commit(types.DELETE_USER_PHOTO, {response});
            resolve(response);
        }, response => {
            reject(response);
        })
    })
};

export const disconnectApp = ({commit}, appHandle) => {
    return new Promise((resolve, reject) => {
        accountApi.disconnectApp(appHandle, response => {
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

        accountApi.getCraftIdData(userId, response => {
                commit(types.RECEIVE_CRAFT_ID_DATA, {response});
                commit(types.RECEIVE_PLUGINS, {plugins: response.data.plugins});
                commit(types.RECEIVE_UPCOMING_INVOICE, {upcomingInvoice: response.data.upcomingInvoice});
                resolve(response);
            },
            response => {
                reject(response);
            })
    })
};

export const saveUser = ({commit}, user) => {
    return new Promise((resolve, reject) => {
        accountApi.saveUser(user, response => {
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
        accountApi.uploadUserPhoto(data, response => {
            commit(types.UPLOAD_USER_PHOTO, {response});
            resolve(response);
        }, response => {
            reject(response);
        })
    })
};

export const saveBillingInfo = ({commit}, data) => {
    return new Promise((resolve, reject) => {
        accountApi.saveBillingInfo(data, response => {
                if (!response.data.errors) {
                    commit(types.SAVE_BILLING_INFO, {response});
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

export const removeCard = ({commit}) => {
    return new Promise((resolve, reject) => {
        accountApi.removeCard(response => {
            commit(types.REMOVE_CARD);
            resolve(response);
        }, response => {
            reject(response);
        })
    })
};

export const saveCard = ({commit}, source) => {
    return new Promise((resolve, reject) => {
        accountApi.saveCard(source, response => {
            commit(types.SAVE_CARD, {response});
            resolve(response);
        }, response => {
            reject(response);
        })
    })
};

export const disconnectStripeAccount = ({commit}) => {
    return new Promise((resolve, reject) => {
        accountApi.disconnectStripeAccount(response => {
            commit(types.DISCONNECT_STRIPE_ACCOUNT);
            resolve(response);
        }, response => {
            reject(response);
        })
    })
};

export const getStripeAccount = ({commit}) => {
    return new Promise((resolve, reject) => {
        accountApi.getStripeAccount(response => {
            commit(types.RECEIVE_STRIPE_ACCOUNT, {response});
            resolve(response);
        }, response => {
            reject(response);
        })
    })
};

export const getStripeCustomer = ({commit}) => {
    return new Promise((resolve, reject) => {
        accountApi.getStripeCustomer(response => {
            commit(types.RECEIVE_STRIPE_CUSTOMER, {response});
            commit(types.RECEIVE_STRIPE_CARD, {response});
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
        licensesApi.claimCmsLicense(licenseKey, response => {
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
        licensesApi.claimCmsLicenseFile(licenseFile, response => {
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

export const claimLicensesByEmail = ({commit}, email) => {
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
};

export const claimPluginLicense = ({commit}, licenseKey) => {
    return new Promise((resolve, reject) => {
        licensesApi.claimPluginLicense(licenseKey, response => {
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

export const getCmsLicenses = ({commit}) => {
    return new Promise((resolve, reject) => {
        licensesApi.getCmsLicenses(response => {
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
        licensesApi.getPluginLicenses(response => {
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
};

export const releasePluginLicense = ({commit}, {pluginHandle, licenseKey}) => {
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
};

export const saveCmsLicense = ({commit}, license) => {
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
};

export const savePluginLicense = ({commit}, license) => {
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
};


/**
 * Developer
 */

export const generateApiToken = ({commit}) => {
    return new Promise((resolve, reject) => {
        developerApi.generateApiToken(response => {
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

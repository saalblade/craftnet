import * as types from './mutation-types'
import api from '../api'


/**
 * User
 */

export const connectAppCallback = ({commit}, apps) => {
    commit(types.CONNECT_APP_CALLBACK, { apps })
};

export const deleteUserPhoto = ({commit}, data) => {
    return new Promise((resolve, reject) => {
        api.deleteUserPhoto(data, response => {
            commit(types.DELETE_USER_PHOTO, {data, response});
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
            commit(types.UPLOAD_USER_PHOTO, {data, response});
            resolve(response);
        }, response => {
            reject(response);
        })
    })
};


/**
 * Licenses
 */

export const saveCmsLicense = ({commit}, license) => {
    return new Promise((resolve, reject) => {
        api.saveCmsLicense(license, response => {
            if (response.data && !response.data.errors) {
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

export const saveCard = ({commit}, token) => {
    return new Promise((resolve, reject) => {
        api.saveCard(token, response => {
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

import Vue from 'vue'
import Vuex from 'vuex'
import axios from 'axios'
import qs from 'qs'
import api from '../../api/cart'

Vue.use(Vuex)

/**
 * State
 */
const state = {
    cart: null,
    mockCart: {
        items: []
    }
}

/**
 * Getters
 */
const getters = {

    cartTotal(state) {
        let total = 0;

        state.mockCart.items.forEach(item => {
            total += parseFloat(item.lineItem.total);
        })

        return total
    },

    cartItems(state, getters, rootState) {
        let cartItems = []

        if (state.cart) {
            const lineItems = state.cart.lineItems

            lineItems.forEach(lineItem => {
                let cartItem = {}

                cartItem.lineItem = lineItem

                // if (lineItem.purchasable.type === 'plugin-edition') {
                //     cartItem.plugin = rootState.pluginStore.plugins.find(p => p.handle === lineItem.purchasable.plugin.handle)
                // }

                cartItems.push(cartItem)
            })
        }

        return cartItems
    },
}

/**
 * Actions
 */
const actions = {

    getCart({dispatch, commit, rootState, state}) {
        return new Promise((resolve, reject) => {
            if (!state.cart) {
                dispatch('getOrderNumber')
                    .then(orderNumber => {
                        if (orderNumber) {
                            api.getCart(orderNumber, response => {
                                if (!response.error) {
                                    commit('updateCart', {response})
                                    resolve(response)
                                } else {
                                    // Couldn’t get cart for this order number? Try to create a new one.
                                    const data = {
                                        email: rootState.account.currentUser.email
                                    }

                                    api.createCart(data, response2 => {
                                        commit('updateCart', {response: response2})
                                        dispatch('saveOrderNumber', {orderNumber: response2.cart.number})
                                        resolve(response)
                                    }, response => {
                                        reject(response)
                                    })
                                }
                            }, response => {
                                reject(response)
                            })
                        } else {
                            // No order number yet? Create a new cart.
                            const data = {
                                email: rootState.account.currentUser.email
                            }

                            api.createCart(data, response => {
                                commit('updateCart', {response})
                                dispatch('saveOrderNumber', {orderNumber: response.cart.number})
                                resolve(response)
                            }, response => {
                                reject(response)
                            })
                        }
                    })
            } else {
                resolve()
            }
        })
    },

    getOrderNumber({state}) {
        return new Promise((resolve, reject) => {
            if (state.cart && state.cart.number) {
                const orderNumber = state.cart.number
                resolve(orderNumber)
            } else {
                api.getOrderNumber(orderNumber => {
                    resolve(orderNumber)
                }, response => {
                    reject(response)
                })
            }
        })
    },

    saveOrderNumber({state}, {orderNumber}) {
        api.saveOrderNumber(orderNumber)
    },

    createCart() {
        const data = {
            email: 'ben@pixelandtonic.com'
        }

        axios.post('https://api.craftcms.test/v1/carts', data)
            .then(response => {
                console.log('success');
                // return cb(response.data)
            })
            .catch(response => {
                console.log('error');
                // return errorCb(response)
            })
    },

    addToCart({commit, state, dispatch}, newItems) {
        return new Promise((resolve, reject) => {
            dispatch('getCart')
                .then(() => {
                    const cart = state.cart
                    let items = utils.getCartItemsData(cart)

                    newItems.forEach(newItem => {
                        const alreadyInCart = items.find(item => item.plugin === newItem.plugin)

                        if (!alreadyInCart) {
                            items.push(newItem)
                        }
                    })

                    let data = {
                        items,
                    }

                    api.updateCart(cart.number, data, response => {
                        commit('updateCart', {response})
                        resolve(response)
                    }, response => {
                        reject(response)
                    })
                })
                .catch(reject)
        })
    },

    addToCartMock({commit}, {item}) {
        commit('addToCartMock', {item})
    },

    removeFromCartMock({commit, state}, lineItemKey) {
        commit('removeFromCartMock', {lineItemKey})
    },

}

/**
 * Mutations
 */
const mutations = {

    updateCart(state, {response}) {
        state.cart = response.cart
        state.stripePublicKey = response.stripePublicKey
    },

    addToCartMock(state, {item}) {
        state.mockCart.items.push(item)
    },

    removeFromCartMock(state, {lineItemKey}) {
        state.mockCart.items.splice(lineItemKey, 1)
    }

}

/**
 * Utils
 */
const utils = {

    getCartData(cart) {
        let data = {
            email: cart.email,
            billingAddress: {
                firstName: cart.billingAddress.firstName,
                lastName: cart.billingAddress.lastName,
            },
            items: [],
        }

        data.items = this.getCartItemsData(cart)

        return data
    },

    getCartItemsData(cart) {
        let lineItems = []
        // debugger;
        for (let i = 0; i < cart.lineItems.length; i++) {
            let lineItem = cart.lineItems[i]

            switch (lineItem.purchasable.type) {
                case 'plugin-edition':
                    lineItems.push({
                        type: lineItem.purchasable.type,
                        plugin: lineItem.purchasable.plugin.handle,
                        edition: lineItem.purchasable.handle,
                        autoRenew: lineItem.options.autoRenew,
                        cmsLicenseKey: lineItem.options.cmsLicenseKey,
                    })
                    break
                case 'cms-edition':
                    lineItems.push({
                        type: lineItem.purchasable.type,
                        edition: lineItem.purchasable.handle,
                        licenseKey: lineItem.options.licenseKey,
                        autoRenew: lineItem.options.autoRenew,
                    })
                    break
            }
        }

        return lineItems
    }
}

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
}

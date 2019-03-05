import Vue from 'vue'
import Vuex from 'vuex'
import api from '../../api/cart'
import CartHelper from '../../helpers/cart'

Vue.use(Vuex)

/**
 * State
 */
const state = {
    cart: null,
    selectedExpiryDates: {},
}

/**
 * Getters
 */
const getters = {
    cartTotal() {
        return 0
    },

    cartTotalItems(state) {
        if(state.cart && state.cart.lineItems) {
            return state.cart.lineItems.length
        }

        return 0
    },

    cartItems(state, getters, rootState) {
        let cartItems = []

        if (state.cart) {
            const lineItems = state.cart.lineItems

            lineItems.forEach(lineItem => {
                let cartItem = {}

                cartItem.id = lineItem.id
                cartItem.lineItem = lineItem

                if (lineItem.purchasable.type === 'plugin-edition') {
                    cartItem.plugin = rootState.pluginStore.plugins.find(p => p.handle === lineItem.purchasable.plugin.handle)
                }

                cartItems.push(cartItem)
            })
        }

        return cartItems
    },

    cartItemsData(state) {
        return CartHelper.getCartItemsData(state.cart)
    }
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
                            api.getCart(orderNumber)
                                .then((response) => {
                                    if (!response.error) {
                                        commit('updateCart', {response: response.data})
                                        resolve(response.data)
                                    } else {
                                        // Couldn’t get cart for this order number? Try to create a new one.
                                        const data = {
                                            email: rootState.account.currentUser.email
                                        }

                                        api.createCart(data)
                                            .then((response2) => {
                                                commit('updateCart', {response: response2.data})
                                                dispatch('saveOrderNumber', {orderNumber: response2.data.cart.number})
                                                resolve(response)
                                            })
                                            .catch((response) => {
                                                reject(response)
                                            })
                                    }
                                })
                                .catch((response) => {
                                    if (response.response.data.message && response.response.data.message === 'Cart Already Completed') {
                                        const data = {
                                            email: rootState.account.currentUser.email
                                        }

                                        api.createCart(data)
                                            .then((response2) => {
                                                commit('updateCart', {response: response2.data})
                                                dispatch('saveOrderNumber', {orderNumber: response2.data.cart.number})
                                                resolve(response)
                                            })
                                            .catch((response) => {
                                                reject(response)
                                            })
                                    } else {
                                        reject(response)
                                    }
                                })
                        } else {
                            // No order number yet? Create a new cart.
                            const data = {
                                email: rootState.account.currentUser.email
                            }

                            api.createCart(data)
                                .then((response) => {
                                    commit('updateCart', {response: response.data})
                                    dispatch('saveOrderNumber', {orderNumber: response.data.cart.number})
                                    resolve(response)
                                })
                                .catch((response) => {
                                    reject(response)
                                })
                        }
                    })
            } else {
                resolve()
            }
        })
    },

    saveCart({commit, state}, data) {
        return new Promise((resolve, reject) => {
            const cart = state.cart

            api.updateCart(cart.number, data)
                .then((response) => {
                    if (!response.data.errors) {
                        commit('updateCart', {response: response.data})
                        resolve(response)
                    } else {
                        reject(response)
                    }
                })
                .catch((response) => {
                    reject(response)
                })
        })
    },

    resetCart({commit, dispatch}) {
        return new Promise((resolve, reject) => {
            commit('resetCart')
            dispatch('resetOrderNumber')
            dispatch('getCart')
                .then(response => {
                    resolve(response)
                })
                .catch(response => {
                    reject(response)
                })
        })
    },

    resetOrderNumber() {
        api.resetOrderNumber()
    },

    // eslint-disable-next-line
    checkout({}, data) {
        return new Promise((resolve, reject) => {
            api.checkout(data)
                .then(response => {
                    resolve(response)
                })
                .catch(response => {
                    reject(response)
                })
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

    // eslint-disable-next-line
    saveOrderNumber({}, {orderNumber}) {
        api.saveOrderNumber(orderNumber)
    },

    addToCart({commit, state, dispatch}, newItems) {
        return new Promise((resolve, reject) => {
            dispatch('getCart')
                .then(() => {
                    const cart = state.cart
                    let items = CartHelper.getCartItemsData(cart)

                    newItems.forEach(newItem => {
                        let item = {...newItem}

                        if (!item.expiryDate) {
                            item.expiryDate = '1y'
                        }

                        items.push(item)
                    })

                    let data = {
                        items,
                    }

                    api.updateCart(cart.number, data)
                        .then((response) => {
                            commit('updateCart', {response: response.data})
                            resolve(response)
                        })
                        .catch((response) => {
                            reject(response)
                        })
                })
                .catch(reject)
        })
    },

    removeFromCart({commit, dispatch}, lineItemKey) {
        return new Promise((resolve, reject) => {
            dispatch('getCart')
                .then(() => {
                    const cart = state.cart
                    let items = CartHelper.getCartItemsData(cart)
                    items.splice(lineItemKey, 1)

                    let data = {
                        items,
                    }

                    api.updateCart(cart.number, data)
                        .then((response) => {
                            commit('updateCart', {response: response.data})
                            resolve(response)
                        })
                        .catch((response) => {
                            reject(response)
                        })
                })
                .catch(reject)
        })
    },

    updateItem({commit, state}, {itemKey, item}) {
        return new Promise((resolve, reject) => {
            const cart = state.cart

            let items = CartHelper.getCartItemsData(cart)

            items[itemKey] = item

            let data = {
                items,
            }

            api.updateCart(cart.number, data)
                .then((response) => {
                    commit('updateCart', {response: response.data})
                    resolve(response)
                })
                .catch((response) => {
                    reject(response)
                })
        })
    },
}

/**
 * Mutations
 */
const mutations = {
    updateCart(state, {response}) {
        state.cart = response.cart
        state.stripePublicKey = response.stripePublicKey

        const selectedExpiryDates = {}

        state.cart.lineItems.forEach(lineItem => {
            selectedExpiryDates[lineItem.id] = lineItem.options.expiryDate
        })

        state.selectedExpiryDates = selectedExpiryDates
    },

    resetCart(state) {
        state.cart = null
    },

    updateSelectedExpiryDates(state, selectedExpiryDates) {
        state.selectedExpiryDates = selectedExpiryDates
    },
}

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
}

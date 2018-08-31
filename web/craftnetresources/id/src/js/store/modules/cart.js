import Vue from 'vue'
import Vuex from 'vuex'
import axios from 'axios'
import qs from 'qs'

Vue.use(Vuex)

/**
 * State
 */
const state = {
    cart: {
        items: []
    }
}

/**
 * Getters
 */
const getters = {

    cartTotal(state) {
        let total = 0;

        state.cart.items.forEach(item => {
            total += parseFloat(item.lineItem.total);
        })

        return total
    },

}

/**
 * Actions
 */
const actions = {

    createCart() {
        console.log('create cart action');
        const data = {some:'data'}
        const formData = new FormData();
        const qsData = qs.stringify(data)

        const params = new URLSearchParams();
        params.append('param1', 'value1');
        params.append('param2', 'value2');

        axios.post('https://api.craftcms.test/v1/carts', qsData, {
            headers: {
                'X-CSRF-Token': Craft.csrfTokenValue,
            }
        })
            .then(response => {
                console.log('success');
                // return cb(response.data)
            })
            .catch(response => {
                // return errorCb(response)
            })
    },

    addToCart({commit}, {plugin, pluginEditionHandle}) {
        const pluginEdition = plugin.editions.find(edition => edition.handle === pluginEditionHandle)

        const item = {
            plugin,
            pluginEditionHandle,
            lineItem: {
                total: pluginEdition.price
            }
        }

        commit('addToCart', {item})
    },

    removeFromCart({commit, state}, lineItemKey) {
        commit('removeFromCart', {lineItemKey})
    },

}

/**
 * Mutations
 */
const mutations = {

    addToCart(state, {item}) {
        state.cart.items.push(item)
    },

    removeFromCart(state, {lineItemKey}) {
        state.cart.items.splice(lineItemKey, 1)
    }

}

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
}

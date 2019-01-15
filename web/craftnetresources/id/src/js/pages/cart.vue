<template>
    <div>
        <h1>Cart</h1>

        <spinner v-if="loading"></spinner>

        <template v-else>
            <template v-if="cart">
                <template v-if="cartItems.length">
                    <table class="table">
                        <template v-if="cartItems.length">
                            <thead>
                            <tr>
                                <th colspan="2">Item</th>
                                <th>Type</th>
                                <th>Updates</th>
                                <th>Quantity</th>
                                <th></th>
                            </tr>
                            </thead>

                            <tbody>
                            <tr v-for="(item, itemKey) in cartItems" :key="itemKey">
                                <template v-if="item.lineItem.purchasable.type === 'cms-edition'">
                                    <td class="icon-col">
                                        <div class="plugin-icon">
                                            <img :src="craftLogo" width="42" height="42" />
                                        </div>
                                    </td>
                                    <td>Craft {{ item.lineItem.purchasable.name }}</td>
                                </template>

                                <template v-else-if="item.lineItem.purchasable.type === 'plugin-edition'">
                                    <td class="icon-col">
                                        <div v-if="item.plugin" class="plugin-icon">
                                            <img v-if="item.plugin.iconUrl" :src="item.plugin.iconUrl" width="42" height="42" />
                                        </div>
                                    </td>
                                    <td>
                                        <strong class="text-xl">{{item.lineItem.description}}</strong>

                                        <div class="text-secondary">
                                            {{item.lineItem.purchasable.name}}
                                        </div>
                                    </td>
                                </template>

                                <template v-else>
                                    <td colspan="2">
                                        <strong class="text-xl">
                                            <template v-if="item.lineItem.purchasable.type === 'cms-renewal'">Craft </template>
                                            {{item.lineItem.description}}
                                        </strong>

                                        <div class="text-secondary">
                                            <code>{{ item.lineItem.options.licenseKey.substr(0, 4) }}</code>
                                        </div>
                                    </td>
                                </template>

                                <td>
                                    <code>{{item.lineItem.purchasable.type}}</code>
                                </td>

                                <td>
                                    <template v-if="item.lineItem.purchasable.type === 'cms-edition' || item.lineItem.purchasable.type === 'plugin-edition'">
                                        <select-field v-model="itemUpdates[itemKey]" :options="itemUpdateOptions(itemKey)" />
                                    </template>
                                    <template v-else>
                                        Updates until <strong>{{item.lineItem.options.expiryDate}}</strong>
                                    </template>
                                </td>
                                <td>
                                    <number-input
                                                  ref="quantityInput"
                                                  v-model="itemQuantity[itemKey]"
                                                  min="minQuantity"
                                                  max="maxQuantity"
                                                  step="1"
                                                  @keydown="onQuantityKeyDown($event, itemKey)"
                                                  @input="onQuantityInput($event, itemKey)"
                                                  :disabled="(item.lineItem.purchasable.type === 'cms-edition' || item.lineItem.purchasable.type === 'plugin-edition' ? false : true)"
                                    ></number-input>
                                </td>
                                <td class="text-right">
                                    <strong class="block text-xl">
                                        <!--{{ itemTotal(itemKey)|currency }}-->
                                        {{ item.lineItem.total|currency }}
                                    </strong>
                                    <a @click="removeFromCart(itemKey)">Remove</a>
                                </td>
                            </tr>

                            <tr>
                                <th class="text-right text-xl" colspan="5">Total</th>
                                <td class="text-right text-xl"><strong>{{ total|currency }}</strong></td>
                            </tr>
                            </tbody>
                        </template>
                    </table>

                    <div class="mt-4 text-right"><input type="button" class="btn btn-lg btn-primary" @click="checkout()" value="Check Out" /></div>
                </template>

                <div v-else>
                    <empty>
                        <font-awesome-icon icon="shopping-cart" class="text-5xl mb-4 text-grey" />
                        <div class="font-bold">Your cart is empty</div>
                        <div class="mt-4">
                            <p>Browse plugins on <a :href="craftPluginsUrl()">plugins.craftcms.com</a></p>
                        </div>
                    </empty>
                </div>
            </template>
        </template>
    </div>
</template>

<script>
    import {mapState, mapGetters, mapActions} from 'vuex'
    import Empty from '../components/Empty'
    import Spinner from '../components/Spinner'
    import helpers from '../mixins/helpers'

    export default {
        mixins: [helpers],

        components: {
            Empty,
            Spinner,
        },

        data() {
            return {
                loading: false,
                itemUpdates: {},
                itemQuantity: {},
                minQuantity: 1,
                maxQuantity: 1000,
            }
        },

        computed: {

            ...mapState({
                cart: state => state.cart.cart,
                expiryDateOptions: state => state.pluginStore.expiryDateOptions,
            }),

            ...mapGetters({
                cartItems: 'cart/cartItems',
            }),

            total() {
                let total = 0

                this.cartItems.forEach(function(item, key) {
                    total += this.itemTotal(key)
                }.bind(this))

                return total
            }

        },

        methods: {

            ...mapActions({
                getCart: 'cart/getCart',
                removeFromCart: 'cart/removeFromCart',
                getPluginStoreData: 'pluginStore/getPluginStoreData',
            }),

            checkout() {
                this.$router.push({path: '/payment'})
            },

            itemUpdateOptions(itemKey) {
                const item = this.cartItems[itemKey]
                const renewalPrice = parseFloat(item.lineItem.purchasable.renewalPrice)

                let options = []
                let selectedOption = 0

                this.expiryDateOptions.forEach((option, key) => {
                    if (option === item.lineItem.options.expiryDate) {
                        selectedOption = key
                    }
                })

                for (let i = 0; i < this.expiryDateOptions.length; i++) {
                    const expiryDateOption = this.expiryDateOptions[i]
                    const value = expiryDateOption[0]
                    const date = expiryDateOption[1]
                    const price = renewalPrice * (i - selectedOption)

                    let label = "Updates Until " + this.$options.filters.moment(date, 'L')

                    if (price !== 0) {
                        let sign = '';

                        if (price > 0) {
                            sign = '+';
                        }

                        label += " (" + sign + this.$options.filters.currency(price) + ")"
                    }

                    options.push({
                        label: label,
                        value: value,
                    })
                }

                return options
            },

            itemTotal(itemKey) {
                const purchasable = this.cartItems[itemKey].lineItem.purchasable
                const price = parseInt(purchasable.price)
                const renewalsTotal = parseInt(purchasable.renewalPrice) * this.itemUpdates[itemKey]
                const quantity = this.itemQuantity[itemKey]

                return (price + renewalsTotal) * quantity
            },

            onQuantityInput(value, itemKey) {
                value = parseInt(value)

                if (isNaN(value) || value < this.minQuantity) {
                    value = this.minQuantity
                } else if (value > this.maxQuantity) {
                    value = this.maxQuantity
                }

                this.$set(this.itemQuantity, itemKey, value)
                this.$refs.quantityInput[itemKey].$el.value = value
            },

            onQuantityKeyDown($event) {
                let charCode = ($event.which) ? $event.which : $event.keyCode;

                // prevent `e` and `-` to prevent exponent and negative notations
                if(charCode === 69 || charCode === 189) {
                    $event.preventDefault();

                    return false
                }
            },

        },

        mounted() {
            this.loading = true

            this.getPluginStoreData()
                .then(() => {
                    this.getCart()
                        .then(() => {
                            this.loading = false

                            this.cartItems.forEach(function(item, key) {
                                this.$set(this.itemUpdates, key, '1y')
                                this.$set(this.itemQuantity, key, 1)
                            }.bind(this))
                        })
                        .catch(() => {
                            this.loading = false
                        })
                })
                .catch(() => {
                    this.loading = false
                })
        },
    }
</script>

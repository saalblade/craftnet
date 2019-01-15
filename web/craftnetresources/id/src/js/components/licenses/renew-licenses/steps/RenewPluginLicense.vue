<template>
    <div>
        <spinner v-if="loading"></spinner>

        <template v-else>
            <select-field v-model="renew" :options="extendUpdateOptions" />

            <table class="table mb-2">
                <thead>
                <tr>
                    <th>Item</th>
                    <th>Renewal Date</th>
                    <th>New Renewal Date</th>
                    <th>Subtotal</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>{{ license.plugin.name }}</td>
                    <td>{{ license.expiresOn.date|moment('L') }}</td>
                    <td>{{ expiryDate }}</td>
                    <td>{{ price|currency }}</td>
                </tr>
                </tbody>
            </table>

            <input type="button" class="btn btn-secondary" @click="$emit('cancel')" value="Cancel" />
            <input type="button" class="btn btn-primary" @click="addToCart()" value="Add to cart" />
        </template>
    </div>
</template>

<script>
    import {mapState, mapActions} from 'vuex'
    import Spinner from '../../../Spinner'

    export default {

        props: ['license'],

        components: {
            Spinner,
        },

        data() {
            return {
                loading: false,
                renew: null,
            }
        },

        computed: {

            ...mapState({
                licenseExpiryDateOptions: state => state.pluginStore.licenseExpiryDateOptions,
            }),

            expiryDateOptions() {
                return this.licenseExpiryDateOptions.pluginLicenses[this.license.id]
            },

            extendUpdateOptions() {
                if (!this.expiryDateOptions) {
                    return []
                }

                let options = [];

                for (let i = 0; i < this.expiryDateOptions.length; i++) {
                    const expiryDateOption = this.expiryDateOptions[i]
                    const date = expiryDateOption[1]
                    const formattedDate = this.$moment(date).format('L')
                    const label = "Extend updates until " + formattedDate

                    options.push({
                        label: label,
                        value: i,
                    })
                }

                return options;
            },

            newExpiresOn() {
                const expiresOn = this.$moment(this.license.expiresOn.date)
                return expiresOn.add(this.renew, 'years')
            },

            price() {
                return (parseFloat(this.renew) + 1) * this.license.edition.renewalPrice;
            },

            expiryDate() {
                if (!this.expiryDateOptions) {
                    return null
                }

                if (!this.expiryDateOptions[this.renew]) {
                    return null
                }

                const date = this.expiryDateOptions[this.renew][1]

                return this.$moment(date).format('L')
            }
        },

        methods: {

            ...mapActions({
                getPluginStoreData: 'pluginStore/getPluginStoreData',
            }),

            addToCart() {
                const expiryDate = this.expiryDateOptions[this.renew][0]
                const item = {
                    type: 'plugin-renewal',
                    licenseKey: this.license.key,
                    expiryDate: expiryDate,
                }

                this.$store.dispatch('cart/addToCart', [item])
                    .then(() => {
                        this.$router.push({path: '/cart'})
                        this.$emit('addToCart')
                    })
                    .catch(error => {
                        const errorMessage = error.response.data.errors && error.response.data.errors[0] && error.response.data.errors[0].message ? error.response.data.errors[0].message : 'Couldn’t add update to cart.';
                        this.$store.dispatch('app/displayError', errorMessage);
                    })
            },

        },

        mounted() {
            this.loading = true

            this.getPluginStoreData()
                .then(() => {
                    this.loading = false
                    this.renew = 0
                })
                .catch(() => {
                    this.loading = false
                })
        }

    }
</script>

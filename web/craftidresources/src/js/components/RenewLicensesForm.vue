<template>
    <div v-if="license.expirable && license.expiresOn">
        <h5>Renew Licenses</h5>

        <template v-if="step === 'extend-updates'">
            <select-field v-model="renew" :options="renewOptions" />
            <button @click="$emit('cancel')" class="btn btn-secondary">Cancel</button>
            <button @click="step = 'cart'" class="btn btn-primary">Continue</button>
        </template>

        <template v-if="step === 'cart'">

            <table class="table mb-2">
                <thead>
                <tr>
                    <td><input type="checkbox" @change="checkAll"></td>
                    <th>Item</th>
                    <th>Renewal Date</th>
                    <th>New Renewal Date</th>
                    <th>Renewal Price</th>
                    <th>Subtotal</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="renewableLicense, key in renewableLicenses">
                    <td><input type="checkbox" :value="key" v-model="checkedLicenses"></td>
                    <td>{{ renewableLicense.description }}</td>
                    <td>{{ renewableLicense.expiresOn.date|moment('L') }}</td>
                    <td>{{ newExpiresOn|moment('L') }}</td>
                    <td>{{ renewableLicense.edition.renewalPrice|currency }} <span class="text-grey-dark">&times;</span> {{ Math.round(newExpiresOn.diff(renewableLicense.expiresOn.date, 'years', true) * 100) / 100 }} year(s)</td>
                    <td>{{ newExpiresOn.diff(renewableLicense.expiresOn.date, 'years', true) * renewableLicense.edition.renewalPrice|currency }}</td>
                </tr>
                <tr>
                    <th></th>
                    <th colspan="4" class="text-right">Total</th>
                    <th>{{ renewableLicensesTotal|currency }}</th>
                </tr>
                </tbody>
            </table>

            <button @click="step = 'extend-updates'" class="btn btn-secondary">Back</button>
            <button @click="step = 'payment'" class="btn btn-primary" :disabled="renewableLicensesTotal === 0" :class="{disabled: renewableLicensesTotal === 0}">Checkout</button>
        </template>

        <template v-if="step === 'payment'">
            <div class="md:flex -mx-4">
                <div class="md:w-1/2 px-4">
                    <h6>Payment Method</h6>
                    <text-field placeholder="Card Number" id="card-number" />
                    <text-field placeholder="Card Expiry" id="card-expiry" />
                    <text-field placeholder="Card CVC" id="card-cvc" />

                    <h6>Coupon Code</h6>
                    <text-field placeholder="XXXXXXX" id="coupon-code" size="12" />
                </div>
                <div class="md:w-1/2 px-4">
                    <h6>Billing Informations</h6>
                    <text-field placeholder="First Name" id="first-name" />
                    <text-field placeholder="Last Name" id="last-name" />
                    <text-field placeholder="Business Name" id="business-name" />
                    <text-field placeholder="Business Tax ID" id="business-tax-id" />
                    <text-field placeholder="Address 1" id="address-1" />
                    <text-field placeholder="Address 2" id="address-2" />
                    <text-field placeholder="Zip Code" id="zip-code" />
                    <text-field placeholder="City" id="city" />
                    <text-field placeholder="Country" id="country" />
                </div>
            </div>

            <a @click="step = 'cart'" href="#" class="btn btn-secondary">Back</a>
            <a @click="step = 'thank-you'" href="#" class="btn btn-primary">Pay {{ renewableLicensesTotal|currency }}</a>
        </template>
    </div>
</template>

<script>
    import {mapState} from 'vuex'
    import SelectField from '../components/fields/SelectField'
    import TextField from '../components/fields/TextField'

    export default {

        props: ['license'],

        data() {
            return {
                renew: 1,
                checkedLicenses: [],
                step: 'extend-updates',
            }
        },

        components: {
            SelectField,
            TextField,
        },

        computed: {

            ...mapState({
                pluginLicenses: state => state.licenses.pluginLicenses,
            }),

            renewOptions() {
                let options = []
                const edition = this.license.editionDetails
                const renewalPrice = edition.renewalPrice

                for (let i = 1; i <= 5; i++) {
                    const date = this.$moment(this.license.expiresOn.date).add(i, 'year')
                    const formattedDate = this.$moment(date).format('L')
                    const price = renewalPrice * i
                    const label = "Extend updates until " + formattedDate

                    options.push({
                        label: label,
                        value: i,
                    })
                }

                return options
            },

            renewableLicenses() {
                let renewableLicenses = []


                // CMS license

                renewableLicenses.push({
                    description: 'Craft ' + this.license.editionDetails.name,
                    expiresOn: this.license.expiresOn,
                    edition: this.license.editionDetails,
                })


                // Plugin licenses

                let renewablePluginLicenses = this.license.pluginLicenses.filter(license => !!license.key)
                const cmsExpiresOn = this.$moment(this.license.expiresOn.date)
                let cmsNewExpiresOn = cmsExpiresOn.add(this.renew, 'years')

                renewablePluginLicenses = this.pluginLicenses.filter(license => {
                    const pluginExpiresOn = this.$moment(license.expiresOn.date)
                    if(pluginExpiresOn > cmsNewExpiresOn) {
                        return false
                    }
                    return renewablePluginLicenses.find(renewablePluginLicense => renewablePluginLicense.id === license.id)
                })

                renewablePluginLicenses.forEach(function(renewablePluginLicense) {
                    renewableLicenses.push({
                        description: renewablePluginLicense.plugin.name,
                        expiresOn: renewablePluginLicense.expiresOn,
                        edition: renewablePluginLicense.edition,
                    })
                }.bind(this))

                return renewableLicenses
            },

            newExpiresOn() {
                const cmsExpiresOn = this.$moment(this.license.expiresOn.date)
                return cmsExpiresOn.add(this.renew, 'years')
            },

            renewableLicensesTotal() {
                let total = 0

                this.renewableLicenses.forEach(function(renewableLicense, key) {
                    let isChecked = this.checkedLicenses.find(checkedKey => checkedKey === key)

                    isChecked = typeof(isChecked) !== 'undefined' ? true : false

                    if (isChecked) {
                        total += this.newExpiresOn.diff(renewableLicense.expiresOn.date, 'years', true) * renewableLicense.edition.renewalPrice
                    }
                }.bind(this))

                return total
            }
        },

        methods: {
            checkAll($event) {
                const checked = $event.target.checked

                this.checkedLicenses = []

                if(checked) {
                    this.renewableLicenses.forEach(function(renewableLicense, key) {
                        this.checkedLicenses.push(key)
                    }.bind(this))
                }
            }
        }

    }
</script>
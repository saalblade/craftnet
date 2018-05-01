<template>
    <div v-if="license.expirable && license.expiresOn">
        <h5>Renew Licenses</h5>

        <select-field v-model="renew" :options="renewOptions" />

        <table class="table mb-2">
            <thead>
            <tr>
                <td><input type="checkbox"></td>
                <th>Item</th>
                <th>Renewal Date</th>
                <th>New Renewal Date</th>
                <th>Renewal Price</th>
                <th>Subtotal</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="renewableLicense in renewableLicenses">
                <td><input type="checkbox"></td>
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

        <a href="#" class="btn btn-primary">Renew Your Licenses</a>
    </div>
</template>

<script>
    import {mapState} from 'vuex'
    import SelectField from '../components/fields/SelectField'

    export default {

        props: ['license'],

        data() {
            return {
                renew: 1,
            }
        },

        components: {
            SelectField,
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

                this.renewableLicenses.forEach(function(renewableLicense) {
                    total += this.newExpiresOn.diff(renewableLicense.expiresOn.date, 'years', true) * renewableLicense.edition.renewalPrice
                }.bind(this))

                return total
            }
        }

    }
</script>
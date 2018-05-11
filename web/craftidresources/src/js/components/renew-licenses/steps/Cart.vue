<template>
    <div>
        <p>Do you want to renew plugin licenses as well?</p>
        <table class="table mb-2">
            <thead>
            <tr>
                <td><input type="checkbox" ref="checkAll" @change="checkAll"></td>
                <th>Item</th>
                <th>Renewal Date</th>
                <th>New Renewal Date</th>
                <th>Renewal Price</th>
                <th>Subtotal</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="renewableLicense, key in renewableLicenses(license, renew)">
                <td><input type="checkbox" :value="1" :checked="checkedLicenses[key]" @input="checkLicense($event, key)"></td>
                <td>{{ renewableLicense.description }}</td>
                <td>{{ renewableLicense.expiresOn.date|moment('L') }}</td>
                <td>{{ newExpiresOn(license, renew)|moment('L') }}</td>
                <td>{{ renewableLicense.edition.renewalPrice|currency }} <span class="text-grey-dark">&times;</span> {{ Math.round(newExpiresOn(license, renew).diff(renewableLicense.expiresOn.date, 'days', true)) }} day(s)</td>
                <td>{{ newExpiresOn(license, renew).diff(renewableLicense.expiresOn.date, 'years', true) * renewableLicense.edition.renewalPrice|currency }}</td>
            </tr>
            <tr>
                <th></th>
                <th colspan="4" class="text-right">Total</th>
                <th>{{ renewableLicensesTotal(license, renew, checkedLicenses)|currency }}</th>
            </tr>
            </tbody>
        </table>

        <button @click="$emit('back')" class="btn btn-secondary">Back</button>
        <button @click="$emit('checkout')" class="btn btn-primary" :disabled="renewableLicensesTotal(license, renew, checkedLicenses) === 0" :class="{disabled: renewableLicensesTotal(license, renew, checkedLicenses) === 0}">Checkout</button>
    </div>
</template>

<script>
    import {mapState, mapGetters} from 'vuex'

    export default {

        props: ['license', 'renew', 'checkedLicenses'],

        data() {
            return {
            }
        },

        computed: {

            ...mapState({
                pluginLicenses: state => state.licenses.pluginLicenses,
            }),

            ...mapGetters({
                renewableLicenses: 'renewableLicenses',
                newExpiresOn: 'newExpiresOn',
                renewableLicensesTotal: 'renewableLicensesTotal',
            }),

        },

        methods: {
            checkLicense($event, key) {
                let checkedLicenses = JSON.parse(JSON.stringify(this.checkedLicenses))
                checkedLicenses[key] = $event.target.checked ? 1 : 0

                this.$emit('update:checkedLicenses', checkedLicenses)
            },

            checkAll($event) {
                const checked = $event.target.checked

                let checkedLicenses = []

                if(checked) {
                    this.renewableLicenses(this.license, this.renew).forEach(function(renewableLicense, key) {
                        checkedLicenses[key] = 1
                    })
                }

                this.$emit('update:checkedLicenses', checkedLicenses)
            }

        },

        mounted() {
            if(this.checkedLicenses.length === 0) {
                this.$refs.checkAll.click();
            }
        }

    }
</script>
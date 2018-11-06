<template>
    <div>
        <p>Do you want to renew plugin licenses as well?</p>
        <table class="table mb-2">
            <thead>
            <tr>
                <td><input type="checkbox" v-model="checkAllChecked" ref="checkAll" @change="checkAll"></td>
                <th>Item</th>
                <th>Renewal Date</th>
                <th>New Renewal Date</th>
                <th>Renewal Price</th>
                <th>Subtotal</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="renewableLicense, key in renewableLicenses(license, renew)">
                <td>
                    <input
                            type="checkbox"
                            :value="1"
                            :disabled="key === 0 ? true : false"
                            :checked="checkedLicenses[key]"
                            @input="checkLicense($event, key)" />
                </td>
                <td>{{ renewableLicense.description }}</td>
                <td>{{ renewableLicense.expiresOn.date|moment('L') }}</td>
                <td>{{ renewableLicense.newExpiresOn|moment('L') }} <small class="text-grey-dark">(+{{renewableLicense.newExpiresOn|moment('diff', renewableLicense.newBaseExpiresOn.date, 'days')}} days)</small></td>
                <td>{{ renewableLicense.edition.renewalPrice|currency }}/year</td>
                <td>{{ renewableLicense.newExpiresOn|moment('diff', renewableLicense.newBaseExpiresOn.date, 'years', true) * renewableLicense.edition.renewalPrice|currency }}</td>
            </tr>
            <tr>
                <th></th>
                <th colspan="4" class="text-right">Total</th>
                <th>{{ renewableLicensesTotal(license, renew, checkedLicenses)|currency }}</th>
            </tr>
            </tbody>
        </table>

        <button @click="$emit('back')" class="btn btn-secondary">Back</button>
        <button @click="addToCart()" class="btn btn-primary" :disabled="renewableLicensesTotal(license, renew, checkedLicenses) === 0" :class="{disabled: renewableLicensesTotal(license, renew, checkedLicenses) === 0}">Add to cart</button>
    </div>
</template>

<script>
    import {mapState, mapGetters} from 'vuex'

    export default {

        props: ['license', 'renew', 'checkedLicenses'],

        data() {
            return {
                checkAllChecked: false
            }
        },

        computed: {

            ...mapState({
                pluginLicenses: state => state.licenses.pluginLicenses,
            }),

            ...mapGetters({
                renewableLicenses: 'licenses/renewableLicenses',
                newExpiresOn: 'licenses/newExpiresOn',
                renewableLicensesTotal: 'licenses/renewableLicensesTotal',
            }),

        },

        methods: {
            checkLicense($event, key) {
                let checkedLicenses = JSON.parse(JSON.stringify(this.checkedLicenses))
                checkedLicenses[key] = $event.target.checked ? 1 : 0

                const allChecked = checkedLicenses.find(license => license === 0)

                if (allChecked === undefined) {
                    this.checkAllChecked = true
                } else {
                    this.checkAllChecked = false
                }
                
                this.$emit('update:checkedLicenses', checkedLicenses)
            },

            checkAll($event) {
                let checkedLicenses = []

                if($event.target.checked) {
                    this.renewableLicenses(this.license, this.renew).forEach(function(renewableLicense, key) {
                        checkedLicenses[key] = 1
                    })
                } else {
                    checkedLicenses[0] = 1
                }

                this.$emit('update:checkedLicenses', checkedLicenses)
            },

            addToCart() {
                const item = {
                    type: 'renewal',
                    lineItem: {
                        total: this.renewableLicensesTotal(this.license, this.renew, this.checkedLicenses)
                    }
                }

                this.$store.dispatch('cart/addToCartMock', {item})
                    .then(response => {
                        this.$router.push({path: '/cart'})
                    })

                this.$emit('addToCart')
            },

        },

        mounted() {
            this.$refs.checkAll.click();
        }

    }
</script>
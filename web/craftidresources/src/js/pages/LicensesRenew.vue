<template>
    <div>
        <h1>Renew Licenses</h1>

        <div class="card">
            <div class="card-body">
                <p>Renew your licenses for another year of great updates.</p>

                <table class="table">
                    <thead>
                    <tr>
                        <th></th>
                        <th>License Key</th>
                        <th>Item</th>
                        <th>Domain</th>
                        <th>Updates Until</th>
                        <th>Price</th>
                    </tr>
                    </thead>
                    <tbody>
                    <template>
                        <tr v-for="license in renewLicenses">
                            <td><input type="checkbox" :value="license.type+'-'+license.id" v-model="selectedLicenses" /></td>
                            <td><code>{{ license.shortKey }}</code></td>
                            <td>{{ license.item }}</td>
                            <td>{{ license.domain }}</td>
                            <td>{{ license.renewalDate.date|moment('L') }}</td>
                            <td>{{license.renewalPrice|currency}}</td>
                        </tr>
                    </template>
                    </tbody>
                </table>

                <hr>

                <div class="text-center mt-4">
                    <div class="mb-4">
                        <select name="" id="">
                            <option value="">Renew for 3 years and save $XX.00</option>
                        </select>
                    </div>

                    <div class="flex">
                        <div class="w-1/2 px-4 py-2 text-right"><strong>Subtotal</strong></div>
                        <div class="w-1/2 px-4 py-2 text-left">{{ subtotal|currency }}</div>
                    </div>
                    <div class="flex">
                        <div class="w-1/2 px-4 py-2 text-right"><strong>Pro-Rate discount</strong></div>
                        <div class="w-1/2 px-4 py-2 text-left">$XX.00</div>
                    </div>
                    <div class="flex">
                        <div class="w-1/2 px-4 py-2 text-right"><strong>Total</strong></div>
                        <div class="w-1/2 px-4 py-2 text-left">$XX.00</div>
                    </div>

                    <div class="mt-3">
                        <template v-if="selectedLicenses.length > 0">
                            <a class="btn btn-primary btn-lg" href="#">Renew {{ selectedLicenses.length }} licenses</a>
                        </template>

                        <template v-else>
                            <a class="btn btn-primary btn-lg disabled" href="#">Renew {{ selectedLicenses.length }} licenses</a>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import {mapState} from 'vuex'

    export default {

        data() {
            return {
                selectedLicenses: []
            }
        },

        computed: {

            ...mapState({
                renewLicenses: state => state.craftId.renewLicenses,
            }),

            subtotal() {
                return this.renewLicenses.reduce((accumulator, license) => {
                    if (this.selectedLicenses.find(selectedLicense => selectedLicense === license.type+'-'+license.id)) {
                        return accumulator + parseFloat(license.renewalPrice);
                    }

                    return accumulator;
                }, 0);
            },

        },

        mounted() {
            this.renewLicenses.forEach(license => {
                this.selectedLicenses.push(license.type+'-'+license.id)
            })
        }

    }
</script>

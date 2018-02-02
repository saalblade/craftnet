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
                        <th>Next Payment</th>
                        <th>Price</th>
                    </tr>
                    </thead>
                    <tbody>
                    <template>
                        <tr v-for="license in licenses">
                            <td>
                                <input type="checkbox" :value="license.id" v-model="selectedLicenses" />
                            </td>

                            <template v-if="license.type == 'pluginLicense'">
                                <td><router-link :to="'/account/licenses/plugins/'+license.id">000000{{ license.id }}</router-link></td>
                                <td>{{ license.plugin.name }}</td>
                            </template>

                            <template v-if="license.type == 'craftLicense'">
                                <td><router-link :to="'/account/licenses/craft/'+license.id">000000{{ license.id }}</router-link></td>
                                <td>Craft {{ license.craftEdition.value }}</td>
                            </template>

                            <td>{{ license.domain }}</td>

                            <td>November 16th, 2017</td>

                            <td>
                                <template v-if="license.plugin">
                                    {{ license.plugin.renewalPrice|currency }} for 1 year
                                </template>
                            </td>
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
    import { mapGetters } from 'vuex'
    import LicenseTable from '../components/LicenseTable';

    export default {

        components: {
            LicenseTable
        },

        data() {
            return {
                selectedLicenses: []
            }
        },

        computed: {

            ...mapGetters({
                licenses: 'licenses',
            }),

            subtotal() {
                return this.licenses.reduce((a, b) => {
                    if(b.plugin && this.selectedLicenses.find(lId => lId == b.id)) {
                        return a + parseFloat(b.plugin.renewalPrice);
                    }

                    return a;
                }, 0);
            },

        },

        mounted() {
            this.licenses.forEach(license => {
                this.selectedLicenses.push(license.id)
            })
        }

    }
</script>

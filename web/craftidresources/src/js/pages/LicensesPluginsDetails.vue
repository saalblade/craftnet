<template>
    <div>
        <license-details :license="license"></license-details>

        <div class="card mb-3">
            <div class="card-header">Attached Craft License</div>
            <div class="card-body">
                <license-table type="craft" v-if="attachedCraftLicense" :licenses="attachedCraftLicenses"></license-table>
                <p v-else><em>No Craft license attached.</em></p>
            </div>
        </div>

        <invoices></invoices>

        <div class="card card-danger mb-3">
            <div class="card-header">Danger Zone</div>
            <div class="card-body">
                <h5>Unlink license</h5>
                <p>Unlink this license from the Craft license it is currently attached to.</p>
                <p><a class="btn btn-outline-danger" href="#">Unlink License</a></p>
                <hr>
                <h5>Transfer ownership</h5>
                <p>Transfer this license to another Craft ID.</p>
                <p><a class="btn btn-outline-danger" href="#">Transfer License</a></p>
            </div>

        </div>

    </div>
</template>

<script>
    import { mapGetters } from 'vuex'
    import LicenseDetails from '../components/LicenseDetails'
    import LicenseTable from '../components/LicenseTable';
    import Invoices from '../components/Invoices';

    export default {

        components: {
            LicenseDetails,
            LicenseTable,
            Invoices,
        },

        computed: {

            ...mapGetters({
                pluginLicenses: 'pluginLicenses',
            }),

            license() {
                return this.pluginLicenses.find(l => l.id == this.$route.params.id);
            },

            attachedCraftLicense() {
                let license = this.license;

                if(license) {
                    return license.craftLicense;
                }
            },

            attachedCraftLicenses() {
                if(this.attachedCraftLicense) {
                    return [this.attachedCraftLicense];
                }

                return [];
            }

        }

    }
</script>

<template>
    <div>
        <p><router-link class="nav-link" to="/account/licenses/plugins" exact>← Plugins</router-link></p>
        <h1>#PLU000{{license.id}}</h1>

        <license-details type="plugin" :license="license"></license-details>

        <div class="card mb-3">
            <div class="card-body">
                <h4>Craft License</h4>
                <p class="text-secondary mb-4">Craft license this plugin license is attached to.</p>

                <license-table type="craft" v-if="attachedCraftLicense" :licenses="attachedCmsLicenses"></license-table>
                <p v-else><em>This plugin license is not attached to a Craft CMS license.</em></p>
            </div>
        </div>

        <div class="card card-danger mb-3">
            <div class="card-header">Danger Zone</div>
            <div class="card-body">
                <h5>Release license</h5>
                <p>Release this license if you no longer wish to use it, and so that it can be claimed by someone else.</p>
                <div><a class="btn btn-danger" href="#">Release License</a></div>
            </div>

        </div>

    </div>
</template>

<script>
    import { mapGetters } from 'vuex'
    import LicenseDetails from '../components/LicenseDetails'
    import LicenseTable from '../components/LicenseTable';

    export default {

        components: {
            LicenseDetails,
            LicenseTable,
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

            attachedCmsLicenses() {
                if(this.attachedCraftLicense) {
                    return [this.attachedCraftLicense];
                }

                return [];
            }

        }

    }
</script>

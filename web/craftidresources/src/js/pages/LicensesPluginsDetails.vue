<template>
    <div>
        <p><router-link class="nav-link" to="/account/licenses/plugins" exact>← Plugins</router-link></p>
        <h1><code>{{ license.key.substr(0, 10) }}…</code></h1>

        <plugin-license-details :license="license"></plugin-license-details>

        <div class="card card-danger mb-3">
            <div class="card-header">Danger Zone</div>
            <div class="card-body">
                <h5>Release license</h5>
                <p>Release this license if you no longer wish to use it, so that it can be claimed by someone else.</p>
                <div><a class="btn btn-danger" href="#">Release License</a></div>
            </div>

        </div>

    </div>
</template>

<script>
    import { mapGetters } from 'vuex'
    import CmsLicensesTable from '../components/CmsLicensesTable';
    import LicenseDetails from '../components/LicenseDetails'
    import PluginLicenseDetails from '../components/PluginLicenseDetails';

    export default {

        components: {
            CmsLicensesTable,
            LicenseDetails,
            PluginLicenseDetails,
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

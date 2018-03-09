<template>
    <div>
        <p><router-link class="nav-link" to="/account/licenses/craft" exact>← Craft CMS</router-link></p>
        <h1><code>{{ license.key.substr(0, 10) }}…</code></h1>

        <cms-license-details :license="license"></cms-license-details>

        <div class="card mb-3">
            <div class="card-body">
                <h4>Plugin Licenses</h4>
                <p class="text-secondary mb-4">Plugin licenses attached to this Craft CMS license.</p>
                <license-table type="plugins" :licenses="attachedPluginLicenses"></license-table>
            </div>
        </div>

        <div class="card card-danger mb-3">
            <div class="card-header">Danger Zone</div>

            <div class="card-body">
                <h5>Unlock license</h5>
                <p>Unlock this license to make it ready for transfer to another domain.</p>
                <div><a class="btn btn-danger" href="#">Unlock License</a></div>

                <hr>

                <h5>Transfer ownership</h5>
                <p>Transfer this license to another Craft ID.</p>
                <div><a class="btn btn-danger" href="#">Transfer License</a></div>
            </div>
        </div>
    </div>
</template>

<script>
    import { mapGetters } from 'vuex'
    import CmsLicenseDetails from '../components/CmsLicenseDetails'
    import LicenseTable from '../components/LicenseTable';

    export default {

        components: {
            CmsLicenseDetails,
            LicenseTable,
        },

        computed: {

            ...mapGetters({
                cmsLicenses: 'cmsLicenses',
                pluginLicenses: 'pluginLicenses',
            }),

            license() {
                return this.cmsLicenses.find(l => l.id == this.$route.params.id);
            },

            attachedPluginLicenses() {
                return this.pluginLicenses;
            }
        }

    }
</script>

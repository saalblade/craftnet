<template>
    <div>
        <p><router-link class="nav-link" to="/account/licenses/craft" exact>← Craft CMS</router-link></p>
        <h1><code>{{ license.key.substr(0, 10) }}…</code></h1>

        <cms-license-details :license="license"></cms-license-details>

        <div class="card mb-3">
            <div class="card-body">
                <h4>Plugin Licenses</h4>
                <p class="text-secondary mb-4">Plugin licenses attached to this Craft CMS license.</p>
                <plugin-licenses-table type="plugins" :licenses="attachedPluginLicenses"></plugin-licenses-table>
            </div>
        </div>

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
    import CmsLicenseDetails from '../components/CmsLicenseDetails'
    import PluginLicensesTable from '../components/PluginLicensesTable';

    export default {

        components: {
            CmsLicenseDetails,
            PluginLicensesTable,
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

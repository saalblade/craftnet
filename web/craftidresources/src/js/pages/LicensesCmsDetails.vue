<template>
    <div v-if="license">
        <p><router-link class="nav-link" to="/account/licenses/cms" exact>← Craft CMS</router-link></p>
        <h1><code>{{ license.key.substr(0, 10) }}</code></h1>

        <cms-license-details :license="license"></cms-license-details>

        <div class="card mb-3">
            <div class="card-body">
                <h4>Plugin Licenses</h4>

                <template v-if="license.pluginLicenses.length > 0">
                    <p class="text-secondary mb-4">Plugin licenses attached to this Craft CMS license.</p>
                    <plugin-licenses-table :licenses="license.pluginLicenses" :exclude-cms-license-column="true" :auto-renew-switch="true"></plugin-licenses-table>
                </template>
                <template v-else>
                    <p class="text-secondary mb-4">No plugin licenses are attached to this Craft CMS license.</p>
                </template>
            </div>
        </div>

        <license-history :history="license.history" />

        <div class="card card-danger mb-3">
            <div class="card-header">Danger Zone</div>
            <div class="card-body">
                <h5>Release license</h5>
                <p>Release this license if you no longer wish to use it, so that it can be claimed by someone else.</p>
                <div><button class="btn btn-danger" @click="releaseCmsLicense()">Release License</button></div>
            </div>
        </div>
    </div>
</template>

<script>
    import {mapState} from 'vuex'
    import CmsLicenseDetails from '../components/CmsLicenseDetails'
    import PluginLicensesTable from '../components/PluginLicensesTable';
    import LicenseHistory from '../components/LicenseHistory';

    export default {

        components: {
            CmsLicenseDetails,
            PluginLicensesTable,
            LicenseHistory,
        },

        computed: {

            ...mapState({
                cmsLicenses: state => state.licenses.cmsLicenses,
                pluginLicenses: state => state.licenses.pluginLicenses,
            }),

            license() {
                return this.cmsLicenses.find(l => l.id == this.$route.params.id);
            },

        },

        methods: {

            releaseCmsLicense() {
                if (!window.confirm("Are you sure you want to release this license?")) {
                    return false;
                }

                this.$store.dispatch('releaseCmsLicense', this.license.key)
                    .then(response => {
                        this.$store.dispatch('getCmsLicenses');
                        this.$store.dispatch('getPluginLicenses');
                        this.$store.dispatch('getInvoices');
                        this.$root.displayNotice('CMS license released.');
                        this.$router.push({path: '/account/licenses/cms'});
                    })
                    .catch(response => {
                        const errorMessage = response.data && response.data.error ? response.data.error : 'Couldn’t release CMS license.';
                        this.$root.displayError(errorMessage);
                    });
            }

        }

    }
</script>

<template>
    <div v-if="license">
        <p><router-link class="nav-link" to="/account/licenses/plugins" exact>← Plugins</router-link></p>
        <h1><code>{{ license.key.substr(0, 4) }}</code></h1>

        <plugin-license-details :license="license"></plugin-license-details>

        <license-history :history="license.history" />

        <div class="card card-danger mb-3">
            <div class="card-header">Danger Zone</div>
            <div class="card-body">
                <template v-if="license.cmsLicense && license.cmsLicense.key">
                    <h5>Unlink license</h5>
                    <p>Unlink this plugin license from the CMS license it’s attached to.</p>
                    <div><button class="btn btn-danger" @click="unlinkPluginLicense()">Unlink License</button></div>
                    <hr>
                </template>
                <h5>Release license</h5>
                <p>Release this license if you no longer wish to use it, so that it can be claimed by someone else.</p>
                <div><button class="btn btn-danger" @click="releasePluginLicense()">Release License</button></div>
            </div>
        </div>
    </div>
</template>

<script>
    import {mapGetters} from 'vuex'
    import CmsLicensesTable from '../components/CmsLicensesTable';
    import LicenseDetails from '../components/LicenseDetails'
    import PluginLicenseDetails from '../components/PluginLicenseDetails';
    import LicenseHistory from '../components/LicenseHistory';

    export default {

        components: {
            CmsLicensesTable,
            LicenseDetails,
            PluginLicenseDetails,
            LicenseHistory,
        },

        computed: {

            ...mapGetters({
                pluginLicenses: 'pluginLicenses',
            }),

            license() {
                return this.pluginLicenses.find(l => l.id == this.$route.params.id);
            },

        },

        methods: {

            releasePluginLicense() {
                if (!window.confirm("Are you sure you want to release this license?")) {
                    return false;
                }

                this.$store.dispatch('releasePluginLicense', {
                        pluginHandle: this.license.plugin.handle,
                        licenseKey: this.license.key,
                    })
                    .then(response => {
                        this.$store.dispatch('getCmsLicenses');
                        this.$store.dispatch('getPluginLicenses');
                        this.$store.dispatch('getInvoices');
                        this.$root.displayNotice('Plugin license released.');
                        this.$router.push({path: '/account/licenses/plugins'});
                    })
                    .catch(response => {
                        const errorMessage = response.data && response.data.error ? response.data.error : 'Couldn’t release plugin license.';
                        this.$root.displayError(errorMessage);
                    });
            },

            unlinkPluginLicense() {
                if (!window.confirm("Are you sure you want to unlink this license from the CMS license it’s attached to?")) {
                    return false
                }

                this.$store.dispatch('unlinkPluginLicense', {
                        pluginHandle: this.license.plugin.handle,
                        licenseKey: this.license.key,
                    })
                    .then(response => {
                        this.$store.dispatch('getPluginLicenses')
                            .then(response => {
                                this.$root.displayNotice('Plugin license unlinked from CMS license.')
                                this.$store.dispatch('getCmsLicenses')
                                this.$store.dispatch('getInvoices')
                                this.$router.push({path: '/account/licenses/plugins'})
                            })
                            .catch(response => {
                                const errorMessage = response.data && response.data.error ? response.data.error : 'Couldn’t unlink plugin license from CMS license.'
                                this.$root.displayError(errorMessage)
                            })
                    })
                    .catch(response => {
                        const errorMessage = response.data && response.data.error ? response.data.error : 'Couldn’t unlink plugin license from CMS license.'
                        this.$root.displayError(errorMessage)
                    });
            }

        }

    }
</script>

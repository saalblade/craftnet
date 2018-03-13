<template>
    <div v-if="license">
        <p><router-link class="nav-link" to="/account/licenses/plugins" exact>← Plugins</router-link></p>
        <h1><code>{{ license.key.substr(0, 10) }}</code></h1>

        <plugin-license-details :license="license"></plugin-license-details>

        <div class="card card-danger mb-3">
            <div class="card-header">Danger Zone</div>
            <div class="card-body">
                <h5>Release license</h5>
                <p>Release this license if you no longer wish to use it, so that it can be claimed by someone else.</p>
                <div><button class="btn btn-danger" @click="releasePluginLicense()">Release License</button></div>
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
                        this.$root.displayNotice('Plugin license released.');
                        this.$router.push({path: '/account/licenses/plugins'});
                    })
                    .catch(response => {
                        const errorMessage = response.data && response.data.error ? response.data.error : 'Couldn’t release plugin license.';
                        this.$root.displayError(errorMessage);
                    });
            }

        }

    }
</script>

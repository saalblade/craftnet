<template>
    <div>
        <p><router-link class="nav-link" to="/licenses/plugins" exact>← Plugins</router-link></p>

        <template v-if="pluginLicensesLoading || !license">
            <spinner></spinner>
        </template>
        <template v-else>
            <h1><code>{{ license.key.substr(0, 4) }}</code></h1>

            <plugin-license-details :license="license"></plugin-license-details>

            <license-history :history="license.history" />

            <div class="card card-danger mb-3">
                <div class="card-header">Danger Zone</div>
                <div class="card-body">
                    <h5>Release license</h5>
                    <p>Release this license if you no longer wish to use it, so that it can be claimed by someone else.</p>
                    <div><button class="btn btn-danger" @click="releasePluginLicense()">Release License</button></div>
                </div>
            </div>
        </template>
    </div>
</template>

<script>
    import {mapState} from 'vuex'
    import CmsLicensesTable from '../../../components/licenses/CmsLicensesTable';
    import PluginLicenseDetails from '../../../components/licenses/PluginLicenseDetails';
    import LicenseHistory from '../../../components/licenses/LicenseHistory';
    import Spinner from '../../../components/Spinner';

    export default {

        components: {
            CmsLicensesTable,
            PluginLicenseDetails,
            LicenseHistory,
            Spinner,
        },

        computed: {

            ...mapState({
                pluginLicenses: state => state.licenses.pluginLicenses,
                pluginLicensesLoading: state => state.licenses.pluginLicensesLoading,
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

                this.$store.dispatch('licenses/releasePluginLicense', {
                        pluginHandle: this.license.plugin.handle,
                        licenseKey: this.license.key,
                    })
                    .then(() => {
                        this.$store.dispatch('licenses/getCmsLicenses');
                        this.$store.dispatch('licenses/getPluginLicenses');
                        this.$store.dispatch('account/getInvoices');
                        this.$store.dispatch('app/displayNotice', 'Plugin license released.');
                        this.$router.push({path: '/licenses/plugins'});
                    })
                    .catch(response => {
                        const errorMessage = response.data && response.data.error ? response.data.error : 'Couldn’t release plugin license.';
                        this.$store.dispatch('app/displayError', errorMessage);
                    });
            },

        },

        mounted() {
            this.$store.commit('app/updateRenewLicense', this.license)

            this.$store.dispatch('licenses/getPluginLicenses')
        }

    }
</script>

<template>
    <div>
        <h1>Plugins</h1>
        <license-renew-alert type="plugin" :expiring-licenses="expiringPluginLicenses"></license-renew-alert>

        <div v-if="pluginLicenses.length > 0" class="card card-table">
            <plugin-licenses-table :licenses="pluginLicenses"></plugin-licenses-table>
        </div>

        <div v-else class="card card-empty">
            <div class="card-body">
                <div class="font-bold">No plugin licenses</div>
                <div>You don’t have any plugin licenses yet.</div>
            </div>
        </div>
    </div>
</template>

<script>
    import {mapState, mapGetters} from 'vuex'
    import LicenseRenewAlert from '../components/LicenseRenewAlert';
    import PluginLicensesTable from '../components/PluginLicensesTable';

    export default {

        components: {
            LicenseRenewAlert,
            PluginLicensesTable,
        },

        computed: {

            ...mapState({
                pluginLicenses: state => state.licenses.pluginLicenses,
            }),

            ...mapGetters({
                expiringPluginLicenses: 'licenses/expiringPluginLicenses',
            }),

        },

    }
</script>

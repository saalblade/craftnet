<template>
    <div>
        <h1>Plugins</h1>

        <license-renew-alert></license-renew-alert>

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
    import {mapGetters} from 'vuex'
    import LicenseRenewAlert from '../components/LicenseRenewAlert';
    import PluginLicensesTable from '../components/PluginLicensesTable';

    export default {

        components: {
            LicenseRenewAlert,
            PluginLicensesTable,
        },

        computed: {

            ...mapGetters({
                pluginLicenses: 'pluginLicenses',
            }),

        },

    }
</script>

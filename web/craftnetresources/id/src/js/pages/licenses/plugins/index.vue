<template>
    <div>
        <license-renew-alert type="plugin" :expiring-licenses="expiringPluginLicenses"></license-renew-alert>

        <h1>Plugins</h1>

        <div v-if="pluginLicenses.length > 0" class="card card-table responsive-content">
            <plugin-licenses-table :licenses="pluginLicenses"></plugin-licenses-table>
        </div>

        <empty v-else>
            <font-awesome-icon icon="key" class="text-5xl mb-4 text-grey-light" />
            <div class="font-bold">No plugin licenses</div>
            <div>You don’t have any plugin licenses yet.</div>
        </empty>
    </div>
</template>

<script>
    import {mapState, mapGetters} from 'vuex'
    import LicenseRenewAlert from '../../../components/licenses/LicenseRenewAlert';
    import PluginLicensesTable from '../../../components/licenses/PluginLicensesTable';
    import Empty from '../../../components/Empty';

    export default {

        components: {
            LicenseRenewAlert,
            PluginLicensesTable,
            Empty,
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

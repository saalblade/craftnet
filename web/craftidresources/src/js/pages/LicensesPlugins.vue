<template>
    <div>
        <license-renew-alert></license-renew-alert>

        <div v-if="pluginLicenses.length > 0" class="card card-table">
            <license-table type="plugins" :licenses="pluginLicenses"></license-table>
        </div>

        <div v-else class="card card-empty">
            <div class="card-body">
                No plugin licenses.
            </div>
        </div>
    </div>
</template>

<script>
    import { mapGetters } from 'vuex'
    import LicenseTable from '../components/LicenseTable';
    import LicenseRenewAlert from '../components/LicenseRenewAlert';

    export default {

        components: {
            LicenseTable,
            LicenseRenewAlert,
        },

        computed: {

            ...mapGetters({
                pluginLicenses: 'pluginLicenses',
            }),

        },

    }
</script>

<template>
    <div>
        <license-renew-alert></license-renew-alert>

        <license-table type="plugins" :licenses="pluginLicenses"></license-table>
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

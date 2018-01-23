<template>
    <div>
        <license-renew-alert></license-renew-alert>

        <div class="card card-table">
            <license-table type="craft" :licenses="craftLicenses"></license-table>
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
                craftLicenses: 'craftLicenses',
            }),

        },

    }
</script>

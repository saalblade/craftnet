<template>
    <div>
        <h1>Craft CMS</h1>
        <license-renew-alert></license-renew-alert>

        <div v-if="cmsLicenses.length > 0" class="card card-table">
            <license-table type="craft" :licenses="cmsLicenses"></license-table>
        </div>

        <div v-else class="card card-empty">
            <div class="card-body">
                <div class="font-bold">No Craft licenses</div>
                <div>You don’t have any Craft licenses yet.</div>
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
                cmsLicenses: 'cmsLicenses',
            }),

        },

    }
</script>

<template>
    <div>
        <h1>Craft CMS</h1>
        <license-renew-alert></license-renew-alert>

        <div v-if="cmsLicenses.length > 0" class="card card-table">
            <cms-licenses-table type="craft" :licenses="cmsLicenses"></cms-licenses-table>
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
    import {mapState, mapGetters} from 'vuex'
    import CmsLicensesTable from '../components/CmsLicensesTable';
    import LicenseRenewAlert from '../components/LicenseRenewAlert';

    export default {

        components: {
            CmsLicensesTable,
            LicenseRenewAlert,
        },

        computed: {

            ...mapState({
                cmsLicenses: state => state.licenses.cmsLicenses,
            }),

        },

    }
</script>

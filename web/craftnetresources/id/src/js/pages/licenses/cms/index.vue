<template>
    <div>
        <license-renew-alert type="CMS" :expiring-licenses="expiringCmsLicenses"></license-renew-alert>

        <h1>Craft CMS</h1>

        <div v-if="cmsLicenses.length > 0" class="card card-table responsive-content">
            <cms-licenses-table type="craft" :licenses="cmsLicenses"></cms-licenses-table>
        </div>

        <div v-else class="empty">
            <div class="empty-body">
                <font-awesome-icon icon="key" class="text-5xl mb-4 text-grey-light" />
                <div class="font-bold">No Craft CMS licenses</div>
                <div>You don’t have any Craft CMS licenses yet.</div>
            </div>
        </div>
    </div>
</template>

<script>
    import {mapState, mapGetters} from 'vuex'
    import CmsLicensesTable from '../../../components/licenses/CmsLicensesTable';
    import LicenseRenewAlert from '../../../components/licenses/LicenseRenewAlert';

    export default {

        components: {
            CmsLicensesTable,
            LicenseRenewAlert,
        },

        computed: {

            ...mapState({
                cmsLicenses: state => state.licenses.cmsLicenses,
            }),

            ...mapGetters({
                expiringCmsLicenses: 'licenses/expiringCmsLicenses',
            }),

        },

    }
</script>

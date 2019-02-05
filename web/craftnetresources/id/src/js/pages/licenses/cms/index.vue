<template>
    <div>
        <h1>Craft CMS</h1>

        <div v-if="cmsLicenses.length > 0" class="card card-table responsive-content">
            <cms-licenses-table type="craft" :licenses="cmsLicenses"></cms-licenses-table>
        </div>

        <empty v-else>
            <icon icon="key" cssClass="text-5xl mb-4 text-grey-light" />
            <div class="font-bold">No Craft CMS licenses</div>
            <div>You don’t have any Craft CMS licenses yet.</div>
        </empty>
    </div>
</template>

<script>
    import {mapState} from 'vuex'
    import CmsLicensesTable from '../../../components/licenses/CmsLicensesTable';
    import Empty from '../../../components/Empty';

    export default {

        components: {
            CmsLicensesTable,
            Empty,
        },

        computed: {

            ...mapState({
                cmsLicenses: state => state.licenses.cmsLicenses,
            }),

        },

    }
</script>

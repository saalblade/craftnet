<template>
    <div>
        <h1>Plugins</h1>

        <template v-if="pluginLicensesLoading">
            <spinner></spinner>
        </template>
        <template v-else>
            <div v-if="pluginLicenses.length > 0" class="card card-table responsive-content">
                <plugin-licenses-table :licenses="pluginLicenses"></plugin-licenses-table>
            </div>

            <empty v-else>
                <icon icon="key" cssClass="text-5xl mb-4 text-grey-light" />
                <div class="font-bold">No plugin licenses</div>
                <div>You don’t have any plugin licenses yet.</div>
            </empty>
        </template>
    </div>
</template>

<script>
    import {mapState} from 'vuex'
    import PluginLicensesTable from '../../../components/licenses/PluginLicensesTable';
    import Empty from '../../../components/Empty';
    import Spinner from '../../../components/Spinner';

    export default {

        components: {
            PluginLicensesTable,
            Empty,
            Spinner,
        },

        computed: {

            ...mapState({
                pluginLicenses: state => state.licenses.pluginLicenses,
                pluginLicensesLoading: state => state.licenses.pluginLicensesLoading,
            }),

        },

        mounted() {
            this.$store.dispatch('licenses/getPluginLicenses')
        }
    }
</script>

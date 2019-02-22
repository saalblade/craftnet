<template>
    <div>
        <div class="card mb-3">
            <div class="card-header">Invoices</div>
            <div class="card-body">
                <invoices-table :invoices="invoices"></invoices-table>
            </div>
        </div>

    </div>
</template>

<script>
    import {mapState} from 'vuex'
    import InvoicesTable from './InvoicesTable';

    export default {
        components: {
            InvoicesTable
        },

        computed: {

            ...mapState({
                invoices: state => state.account.invoices,
            }),
        },
    }
</script>

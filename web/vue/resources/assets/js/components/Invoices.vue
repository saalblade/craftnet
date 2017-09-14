<template>
    <div>

        <div class="card mb-3">
            <div class="card-header">Upcoming Invoice</div>
            <div class="card-body">
                <invoices-table upcoming="true"></invoices-table>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">Invoices</div>
            <div class="card-body">
                <invoices-table></invoices-table>
            </div>
        </div>

    </div>
</template>

<script>
    import InvoicesTable from './InvoicesTable';

    export default {
        components: {
            InvoicesTable
        }
    }
</script>

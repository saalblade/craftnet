<template>
    <div>
        <div class="flex mb-6">
            <div class="flex-1">
                <filter-bar placeholder="Invoice number…" @filter-set="onFilterSet" @filter-reset="onFilterReset"></filter-bar>
            </div>

            <div class="mx-2 flex items-center">
                <spinner :class="{invisible: !loading}"></spinner>
            </div>

            <div class="text-right">
                <vuetable-pagination ref="pagination" @vuetable-pagination:change-page="onChangePage"></vuetable-pagination>
            </div>
        </div>

        <div class="card card-table" :class="{'opacity-25': loading}">
            <vuetable
                    ref="vuetable"
                    pagination-path=""
                    :api-url="apiUrl"
                    :fields="fields"
                    :append-params="moreParams"
                    @vuetable:pagination-data="onPaginationData"
                    @vuetable:loading="onLoading"
                    @vuetable:loaded="onLoaded"
            >
                <template slot="number" slot-scope="props">
                    <router-link :to="'/account/billing/invoices/' + props.rowData.number">{{ props.rowData.shortNumber }}</router-link>
                </template>
                <template slot="price" slot-scope="props">
                    {{ props.rowData.totalPrice|currency }}
                </template>
                <template slot="date" slot-scope="props">
                    <template v-if="props.rowData.datePaid">{{ props.rowData.datePaid.date|moment("L") }}</template>
                </template>
                <template slot="receipt" slot-scope="props">
                    <a :href="props.rowData.pdfUrl">Download Receipt</a>
                </template>
            </vuetable>
        </div>

        <!--<div class="responsive-content">
        <table class="table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Price</th>
                <th>Date</th>
                <th>Receipt</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="(invoice, key) in invoices" :key="key">
                <td><router-link :to="'/account/billing/invoices/' + invoice.number">{{ invoice.shortNumber }}</router-link></td>
                <td>{{ invoice.totalPrice|currency }}</td>
                <td><template v-if="invoice.datePaid">{{ invoice.datePaid.date|moment("L") }}</template></td>
                <td><a :href="invoice.pdfUrl">Download Receipt</a></td>
            </tr>
            </tbody>
        </table>
    </div>-->
    </div>
</template>

<script>
    /* global Craft */

    import FilterBar from '../FilterBar'
    import Vuetable from 'vuetable-2/src/components/Vuetable'
    import VuetablePagination from 'vuetable-2/src/components/VuetablePaginationDropdown'

    export default {
        components: {
            FilterBar,
            Vuetable,
            VuetablePagination,
        },

        data() {
            return {
                searchQuery: '',
                loading: false,
                fields: [
                    {
                        name: '__slot:number',
                        title: 'Number',
                    },
                    {
                        name: '__slot:price',
                        title: 'Price',
                    },
                    {
                        name: '__slot:date',
                        title: 'Date',
                    },
                    {
                        name: '__slot:receipt',
                        title: 'Receipt',
                    },
                ],
                moreParams: {}
            }
        },

        computed: {
            apiUrl() {
                return Craft.actionUrl + '/craftnet/id/invoices/get-invoices'
            }
        },

        methods: {
            onFilterSet(filterText) {
                this.moreParams = {
                    'filter': filterText
                }

                this.$nextTick(() => this.$refs.vuetable.refresh())
            },

            onFilterReset() {
                this.moreParams = {}
                this.$nextTick(() => this.$refs.vuetable.refresh())
            },

            onPaginationData(paginationData) {
                this.$refs.pagination.setPaginationData(paginationData)
            },

            onChangePage(page) {
                this.$refs.vuetable.changePage(page)
            },

            onLoading() {
                this.loading = true
            },

            onLoaded() {
                this.loading = false
            }
        },
    }
</script>

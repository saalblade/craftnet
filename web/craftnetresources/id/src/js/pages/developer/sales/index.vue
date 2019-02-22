<template>
    <div>
        <h1>Sales</h1>

        <div class="flex mb-6">
            <div class="flex-1">
                <filter-bar placeholder="Customer email…" @filter-set="onFilterSet" @filter-reset="onFilterReset"></filter-bar>
            </div>

            <div class="mx-2 flex items-center">
                <spinner :cssClass="{invisible: !loading}"></spinner>
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
                <template slot="item" slot-scope="props">
                    {{props.rowData.plugin.name}}
                </template>

                <template slot="customer" slot-scope="props">
                    <a :href="'mailto:'+props.rowData.customer.email">{{ props.rowData.customer.email }}</a>
                </template>

                <template slot="type" slot-scope="props">
                    <span class="text-secondary">License Purchase</span>
                </template>

                <template slot="grossAmount" slot-scope="props">
                    {{props.rowData.grossAmount|currency}}
                </template>

                <template slot="netAmount" slot-scope="props">
                    {{props.rowData.netAmount|currency}}
                </template>

                <template slot="date" slot-scope="props">
                    {{props.rowData.saleTime|moment("LLL")}}
                </template>
            </vuetable>
        </div>

        <!--
        <empty>
            <icon icon="dollar-sign" cssClass="text-5xl mb-4 text-grey-light" />
            <div class="font-bold">No sales</div>
            <div>You don’t have any sales yet.</div>
        </empty>
        -->
    </div>
</template>

<script>
    import Empty from '../../../components/Empty'
    import Spinner from '../../../components/Spinner'
    import FilterBar from '../../../components/FilterBar'
    import Vuetable from 'vuetable-2/src/components/Vuetable'
    import VuetablePagination from 'vuetable-2/src/components/VuetablePaginationDropdown'

    export default {
        components: {
            Empty,
            Spinner,
            FilterBar,
            Vuetable,
            VuetablePagination,
        },

        data() {
            return {
                searchQuery: '',
                loading: false,
                options:{
                    perPage: 10,
                    texts: {
                        filter: "",
                        filterPlaceholder: "Search licenses"
                    },
                    headings: {
                        expiresOn: 'Updates Until',
                        autoRenew: 'Auto Renew'
                    },
                    filterable: true,
                },
                fields: [
                    {
                        name: '__slot:item',
                        title: 'Item',
                    },
                    {
                        name: '__slot:customer',
                        title: 'Customer',
                    },
                    {
                        name: '__slot:type',
                        title: 'Type',
                    },
                    {
                        name: '__slot:grossAmount',
                        title: 'Gross Amount',
                    },
                    {
                        name: '__slot:netAmount',
                        title: 'Net Amount',
                    },
                    {
                        name: '__slot:date',
                        title: 'Date',
                    },
                ],
                moreParams: {}
            }
        },

        computed: {
            apiUrl() {
                return Craft.actionUrl + '/craftnet/id/sales/get-sales'
            }
        },

        methods: {
            onFilterSet (filterText) {
                this.moreParams = {
                    'filter': filterText
                }

                this.$nextTick( () => this.$refs.vuetable.refresh())
            },

            onFilterReset () {
                this.moreParams = {}
                this.$nextTick( () => this.$refs.vuetable.refresh())
            },

            onPaginationData (paginationData) {
                this.$refs.pagination.setPaginationData(paginationData)
            },

            onChangePage (page) {
                this.$refs.vuetable.changePage(page)
            },

            onLoading() {
                this.loading = true
            },

            onLoaded() {
                this.loading = false
            },
        },
    }
</script>

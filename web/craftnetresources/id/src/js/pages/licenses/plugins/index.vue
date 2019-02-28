<template>
    <div>
        <h1>Plugins</h1>

        <div class="flex mb-6">
            <div class="flex-1">
                <filter-bar placeholder="License key, domain, edition, …" @filter-set="onFilterSet" @filter-reset="onFilterReset"></filter-bar>
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
                <template slot="key" slot-scope="props">
                    <code>
                        <router-link v-if="props.rowData.key" :to="'/licenses/plugins/'+props.rowData.id">{{ props.rowData.key.substr(0, 4) }}</router-link>
                        <template v-else>{{ props.rowData.shortKey }}</template>
                    </code>
                </template>


                <template slot="plugin" slot-scope="props">
                    {{props.rowData.plugin.name}}
                </template>

                <template slot="notes" slot-scope="props">
                    {{props.rowData.notes}}
                </template>

                <template slot="cmsLicense" slot-scope="props">
                    <template v-if="props.rowData.cmsLicense">
                        <code>
                            <router-link v-if="props.rowData.cmsLicense.key" :to="'/licenses/cms/'+props.rowData.cmsLicenseId">{{ props.rowData.cmsLicense.key.substr(0, 10) }}</router-link>
                            <template v-else>{{ props.rowData.cmsLicense.shortKey }}</template>
                        </code>
                    </template>

                    <template v-else>
                        —
                    </template>
                </template>

                <template slot="expiresOn" slot-scope="props">
                    <template v-if="props.rowData.expirable && props.rowData.expiresOn">
                        <template v-if="!props.rowData.expired">
                            <template v-if="expiresSoon(props.rowData)">
                                <span class="text-orange">{{ props.rowData.expiresOn.date|moment("L") }}</span>
                            </template>
                            <template v-else>
                                {{ props.rowData.expiresOn.date|moment("L") }}
                            </template>
                        </template>
                        <template v-else>
                            <span class="text-grey-dark">Expired</span>
                        </template>
                    </template>
                    <template v-else>
                        Forever
                    </template>
                </template>

                <template slot="autoRenew" slot-scope="props">
                    <template v-if="props.rowData.expirable && props.rowData.expiresOn">
                        <badge v-if="props.rowData.autoRenew == 1" type="success">Enabled</badge>
                        <badge v-else>Disabled</badge>
                    </template>
                </template>
            </vuetable>
        </div>

        <!--
        <empty>
            <icon icon="key" cssClass="text-5xl mb-4 text-grey-light" />
            <div class="font-bold">No plugin licenses</div>
            <div>You don’t have any plugin licenses yet.</div>
        </empty>
        -->
    </div>
</template>

<script>
    import PluginLicensesTable from '../../../components/licenses/PluginLicensesTable'
    import Empty from '../../../components/Empty'
    import Badge from '../../../components/Badge'
    import FilterBar from '../../../components/FilterBar'
    import Vuetable from 'vuetable-2/src/components/Vuetable'
    import VuetablePagination from 'vuetable-2/src/components/VuetablePaginationDropdown'
    import helpers from '../../../mixins/helpers'

    export default {
        mixins: [helpers],

        components: {
            PluginLicensesTable,
            Empty,
            Badge,
            Vuetable,
            VuetablePagination,
            FilterBar,
        },

        data() {
            return {
                searchQuery: '',
                vueTableInitiatedRouteChange: false,
                loading: false,
                options: {
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
                        name: '__slot:key',
                        title: 'License Key',
                    },
                    {
                        name: '__slot:plugin',
                        title: 'Plugin',
                    },
                    {
                        name: '__slot:notes',
                        title: 'Notes',
                    },
                    {
                        name: '__slot:cmsLicense',
                        title: 'CMS License',
                    },
                    {
                        name: '__slot:expiresOn',
                        title: 'Updates until',
                    },
                    {
                        name: '__slot:autoRenew',
                        title: 'Auto renew',
                    }
                ],
                moreParams: {}
            }
        },

        computed: {
            apiUrl() {
                return Craft.actionUrl + '/craftnet/id/plugin-licenses/get-licenses'
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

        mounted() {
            this.$store.dispatch('pluginLicenses/getExpiringPluginLicensesTotal')
        }
    }
</script>

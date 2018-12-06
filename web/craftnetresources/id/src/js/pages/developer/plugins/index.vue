<template>
    <div>
        <stripe-account-alert></stripe-account-alert>

        <div class="flex justify-between mb-2">
            <h1>Plugins</h1>
            <div>
                <router-link to="/developer/add-plugin" class="btn btn-primary">
                    <font-awesome-icon icon="plus" />
                    Add a plugin
                </router-link>
            </div>
        </div>

        <div v-if="computedPlugins.length > 0" class="card card-table responsive-content">
            <table class="table">
                <thead>
                <tr>
                    <th></th>
                    <th>Name</th>
                    <th>Active Installs</th>
                    <th>Price</th>
                    <th>Status</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="(plugin, pluginKey) in computedPlugins">
                    <td class="icon-col">
                        <router-link :to="'/developer/plugins/' + plugin.id">
                            <img v-if="plugin.iconUrl" :src="plugin.iconUrl" height="36" />
                            <img v-else src="~@/images/default-plugin.svg" height="36" />
                        </router-link>
                    </td>
                    <td class="name-col">
                        <router-link :to="'/developer/plugins/' + plugin.id">{{ plugin.name }}</router-link>
                        <small class="text-secondary" v-if="plugin.latestVersion">{{ plugin.latestVersion }}</small>
                        <div>{{ plugin.shortDescription }}</div>
                    </td>
                    <td>{{ plugin.activeInstalls }}</td>
                    <td>
                        <div class="text-nowrap">
                            <template v-if="priceRanges[pluginKey].min !== priceRanges[pluginKey].max">
                                <template v-if="priceRanges[pluginKey].min > 0">
                                    {{priceRanges[pluginKey].min|currency}}
                                </template>
                                <template v-else>
                                    Free
                                </template>
                                -
                                {{priceRanges[pluginKey].max|currency}}
                            </template>
                            <template v-else>
                                <template v-if="priceRanges[pluginKey].min > 0">
                                    {{priceRanges[pluginKey].min|currency}}
                                </template>
                                <template v-else>
                                    Free
                                </template>
                            </template>
                        </div>
                    </td>
                    <td>
                        <template v-if="plugin.enabled">
                            <span class="text-green">Approved</span>
                        </template>
                        <template v-else>

                            <span v-if="plugin.pendingApproval" class="text-secondary">In Review</span>
                            <template v-else>
                                <span v-if="plugin.lastHistoryNote && plugin.lastHistoryNote.devComments" class="text-warning">Changes requested</span>
                                <span v-else class="text-secondary">Prepare for submission</span>
                            </template>
                        </template>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>

        <div v-else class="empty">
            <div class="empty-body">
                <font-awesome-icon icon="plug" class="text-5xl mb-4 text-grey-light" />
                <div class="font-bold">No plugins</div>
                <div>You haven’t added any plugins yet.</div>
            </div>
        </div>
    </div>
</template>

<script>
    import {mapState} from 'vuex'
    import StripeAccountAlert from '../../../components/developer/StripeAccountAlert'

    export default {

        components: {
            StripeAccountAlert
        },

        data() {
            return {
                showSpinner: 1,
            }
        },

        computed: {

            ...mapState({
                plugins: state => state.developers.plugins,
            }),

            computedPlugins() {
                let plugins = JSON.parse(JSON.stringify(this.plugins));

                plugins.sort((a, b) => {
                    if (a['name'].toLowerCase() < b['name'].toLowerCase()) {
                        return -1;
                    }
                    if (a['name'].toLowerCase() > b['name'].toLowerCase()) {
                        return 1;
                    }
                    return 0;
                });

                return plugins;
            },

            priceRanges() {
                let priceRanges = []

                for (let i = 0; i < this.plugins.length; i++) {
                    const plugin = this.plugins[i]
                    let priceRange = this.getPriceRange(plugin.editions)
                    priceRanges.push(priceRange)
                }

                return priceRanges;
            }

        },

        methods: {

            getPriceRange(editions) {
                let min = null;
                let max = null;

                for(let i = 0; i < editions.length; i++) {
                    const edition = editions[i];
                    const price = parseInt(edition.price)

                    if(min === null) {
                        min = price;
                    }

                    if(max === null) {
                        max = price;
                    }

                    if(price < min) {
                        min = price
                    }

                    if(price > max) {
                        max = price
                    }
                }

                return {
                    min,
                    max
                }
            }

        }

    }
</script>

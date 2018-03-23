<template>
    <div>
        <div class="flex justify-between mb-2">
            <h1>Plugins</h1>
            <div>
                <router-link to="/developer/add-plugin" class="btn btn-primary"><i class="fas fa-plus"></i> Add a plugin</router-link>
            </div>
        </div>

        <stripe-account-alert></stripe-account-alert>

        <div v-if="plugins.length > 0" class="card card-table responsive-content">
            <table class="table">
                <thead>
                <tr>
                    <th></th>
                    <th>Name</th>
                    <th>Price</th>
                    <th>Status</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="plugin in plugins">
                    <td class="icon-col">
                        <router-link :to="'/developer/plugins/' + plugin.id"><img v-if="plugin.iconUrl" :src="plugin.iconUrl" height="36" /></router-link>
                    </td>
                    <td>
                        <router-link :to="'/developer/plugins/' + plugin.id">{{ plugin.name }}</router-link>
                        <small class="text-secondary" v-if="plugin.latestVersion">{{ plugin.latestVersion }}</small>
                        <div>{{ plugin.shortDescription }}</div>
                    </td>
                    <td>
                        <template v-if="!plugin.price || plugin.price == '0.00'">
                            Free
                        </template>

                        <template v-else>
                            {{ plugin.price|currency }}

                            <template v-if="plugin.renewalPrice && plugin.renewalPrice != '0.00'">
                                <br />
                                <em class="text-secondary text-nowrap">{{ plugin.renewalPrice|currency }} per year</em>
                            </template>
                        </template>
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

        <div v-else class="card card-empty">
            <div class="card-body">
                <div class="font-bold">No plugins</div>
                <div>You haven’t added any plugins yet.</div>
            </div>
        </div>
    </div>
</template>

<script>
    import StripeAccountAlert from '../components/StripeAccountAlert'

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

            plugins() {
                let plugins = JSON.parse(JSON.stringify(this.$store.getters.plugins));

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
            }

        },

    }
</script>

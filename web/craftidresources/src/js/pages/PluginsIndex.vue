<template>
    <div>
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
                <td class="thin">
                    <div class="plugin-icon">
                        <img v-if="plugin.iconUrl" :src="plugin.iconUrl" height="32" />
                    </div>
                </td>
                <td>
                    <h6>
                        <strong>
                            <router-link :to="'/developer/plugins/' + plugin.id">{{ plugin.name }}</router-link>
                        </strong>
                    </h6>
                    <p>{{ plugin.shortDescription }}</p>
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
                        <span class="text-success">Approved</span>
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
</template>

<script>
    import { mapGetters } from 'vuex'

    export default {

        data () {
            return {
                showSpinner: 1,
            }
        },

        computed: {

            plugins() {
                let plugins = JSON.parse(JSON.stringify(this.$store.getters.plugins));

                plugins.sort((a,b) => {
                    if (a['name'].toLowerCase() < b['name'].toLowerCase())
                        return -1;
                    if (a['name'].toLowerCase() > b['name'].toLowerCase())
                        return 1;
                    return 0;
                });

                return plugins;
            }

        },

    }
</script>

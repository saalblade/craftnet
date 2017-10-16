<template>

    <div>
        <table class="table">
            <thead>
            <tr>
                <th></th>
                <th>Name</th>
                <th>Price</th>
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
                            <router-link v-if="plugin.status == 'enabled'" :to="'/developer/plugins/' + plugin.id">{{ plugin.name }}</router-link>
                            <span v-else>{{plugin.name}}</span>
                        </strong>
                    </h6>
                    <p>{{ plugin.shortDescription }}</p>
                    <p v-if="plugin.status == 'enabled'" class="text-secondary">
                        XXX Downloads
                    </p>
                    <p v-if="plugin.status == 'disabled'" class="text-secondary">
                        <em>Your plugin is being reviewed by the staff for activation.</em>
                    </p>
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

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
                    <h6><strong><router-link :to="'/plugins/' + plugin.id">{{ plugin.title }}</router-link></strong></h6>
                    <p>{{ plugin.shortDescription }}</p>
                    <p class="text-secondary">
                        {{ plugin.package ? plugin.package.downloads.total : 0 }} Downloads &nbsp;
                        {{ plugin.package ? plugin.package.github_stars : 0 }} Stars &nbsp;
                        {{ plugin.package ? plugin.package.github_open_issues : 0 }} Issues
                    </p>
                </td>
                <td>

                    <template v-if="!plugin.price || plugin.price == '0.00'">
                        Free
                    </template>

                    <template v-else>

                        {{ plugin.price|currency }}

                        <template v-if="plugin.updatePrice && plugin.updatePrice != '0.00'">
                            <br />
                            <em class="text-secondary text-nowrap">{{ plugin.updatePrice|currency }} per year</em>
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
                    if (a['title'].toLowerCase() < b['title'].toLowerCase())
                        return -1;
                    if (a['title'].toLowerCase() > b['title'].toLowerCase())
                        return 1;
                    return 0;
                });

                return plugins;
            }
        },
    }
</script>

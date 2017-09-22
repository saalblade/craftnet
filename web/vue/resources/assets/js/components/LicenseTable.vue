<template>
    <div>
        <table class="table">
            <thead>
            <tr>
                <th>License Key</th>
                <th>Item</th>

                <template v-if="type == 'plugins'">
                    <th>Craft License</th>
                </template>

                <template v-if="type == 'craft'">
                    <th>Plugin Licenses</th>
                </template>


                <th>Domain</th>
                <th>Next Payment</th>
                <th>Auto Renew</th>
            </tr>
            </thead>
            <tbody>
            <template>
                <tr v-for="license in licenses">
                    <td><router-link :to="'/account/licenses/'+type+'/'+license.id">000000{{ license.id }}</router-link></td>

                    <template v-if="type == 'plugins'">
                        <td>{{ license.plugin.name }}</td>

                        <td>
                            <template v-if="license.craftLicense">
                                <a href="#">000000{{ license.craftLicense.id }}</a>
                            </template>

                            <template v-else>
                                —
                            </template>
                        </td>
                    </template>

                    <template v-if="type == 'craft'">
                        <td>Craft {{ license.craftEdition.value }}</td>
                        <td>0</td>
                    </template>

                    <td>{{ license.domain }}</td>

                    <td>November 16th, 2017</td>

                    <td>
                        <span v-if="license.autoRenew == 1" class="badge badge-success">Enabled</span>
                        <span v-else="" class="badge badge-secondary">Disabled</span>
                    </td>
                </tr>
            </template>
            </tbody>
        </table>
    </div>
</template>


<script>
    import { mapGetters } from 'vuex'

    export default {
        props: ['type', 'licenses']
    }
</script>

<template>
    <div class="responsive-content">
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

                <template v-if="enableCommercialFeatures">
                    <th>Next Payment</th>
                    <th>Auto Renew</th>
                </template>
            </tr>
            </thead>
            <tbody>
            <template>
                <tr v-for="license in licenses">
                    <td><router-link :to="'/account/licenses/'+type+'/'+license.id">LIC000{{ license.id }}</router-link></td>

                    <template v-if="type == 'plugins'">
                        <td>{{ license.plugin.name }}</td>
                        <td>
                            <template v-if="license.craftLicense">
                                <a href="#">LIC000{{ license.craftLicense.id }}</a>
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

                    <template v-if="enableCommercialFeatures">
                        <td>November 16th, 2017</td>
                        <td>
                            <span v-if="license.autoRenew == 1" class="badge badge-success">Enabled</span>
                            <span v-else="" class="badge">Disabled</span>
                        </td>
                    </template>
                </tr>
            </template>
            </tbody>
        </table>
    </div>
</template>


<script>
    import { mapGetters } from 'vuex'

    export default {

        props: ['type', 'licenses'],

        ...mapGetters({
            enableCommercialFeatures: 'enableCommercialFeatures',
        }),

    }
</script>

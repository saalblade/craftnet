<template>
    <div class="responsive-content">
        <table class="table">
            <thead>
            <tr>
                <th>License Key</th>
                <th>Edition</th>
                <th>Domain</th>
                <th>Notes</th>
                <th>Updates Until</th>
                <th>Auto Renew</th>
            </tr>
            </thead>
            <tbody>
            <template>
                <tr v-for="license in licenses">
                    <td>
                        <code>
                            <router-link v-if="license.key" :to="'/account/licenses/cms/'+license.id">
                                {{ license.key.substr(0, 10) }}
                            </router-link>

                            <template v-else>
                                {{ license.shortKey }}
                            </template>
                        </code>
                    </td>
                    <td>{{ license.edition }}</td>
                    <td>{{ license.domain }}</td>
                    <td>{{ license.notes }}</td>
                    <td>
                        <template v-if="license.expirable && license.expiresOn">
                            <template v-if="!license.expired">
                                <template v-if="expiresSoon(license)">
                                    <span class="text-orange">{{ license.expiresOn.date|moment("L") }}</span>
                                </template>
                                <template v-else>
                                    {{ license.expiresOn.date|moment("L") }}
                                </template>
                            </template>
                            <template v-else>
                                <span class="text-grey-dark">Expired</span>
                            </template>
                        </template>
                        <template v-else>
                            Forever
                        </template>
                    </td>
                    <td>
                        <template v-if="license.expirable && license.expiresOn">
                            <span v-if="license.autoRenew == 1" class="badge badge-success">Enabled</span>
                            <span v-else="" class="badge">Disabled</span>
                        </template>
                    </td>
                </tr>
            </template>
            </tbody>
        </table>
    </div>
</template>

<script>
    import {mapGetters} from 'vuex'

    export default {

        props: ['licenses'],

        computed: {

            ...mapGetters({
                expiresSoon: 'expiresSoon',
            }),

        }

    }
</script>

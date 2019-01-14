<template>
    <div class="plugin-pricing" v-if="pluginSnippet.editions[0].price != null && pluginSnippet.editions[0].price !== '0.00'">
        <h2 id="pricing" class="mt-8">Pricing</h2>
        <table class="data w-full">
            <tr class="editions">
                <th></th>
                <th class="edition">
                    <div class="mb-2">
                        <div class="edition-name">Lite</div>
                    </div>
                    <div class="price">
                        <strong>{{ (pluginSnippet.editions[0].price / 4)|currency }}</strong>
                        then {{ (pluginSnippet.editions[0].renewalPrice / 4)|currency }}/year
                    </div>
                </th>
                <th class="edition">
                    <div class="mb-2">
                        <div class="edition-name">Standard</div>
                    </div>
                    <div class="price">
                        <strong>{{ (pluginSnippet.editions[0].price * 1)|currency }}</strong>
                        then {{ (pluginSnippet.editions[0].renewalPrice * 1)|currency }}/year
                    </div>
                </th>
                <th class="edition">
                    <div class="mb-2">
                        <div class="edition-name">Pro</div>
                    </div>
                    <div class="price">
                        <strong>{{ (pluginSnippet.editions[0].price * 2)|currency }}</strong>
                        then {{ (pluginSnippet.editions[0].renewalPrice * 2)|currency }}/year
                    </div>
                </th>
            </tr>
            <tr>
                <th colspan="4" class="group">Features</th>
            </tr>
            <tr>
                <th class="feature">Content Modeling</th>
                <td><font-awesome-icon icon="check" /></td>
                <td><font-awesome-icon icon="check" /></td>
                <td><font-awesome-icon icon="check" /></td>

            </tr>
            <tr>
                <th class="feature">Multi-site Multi-lingual</th>
                <td><font-awesome-icon icon="check" /></td>
                <td><font-awesome-icon icon="check" /></td>
                <td><font-awesome-icon icon="check" /></td>
            </tr>
            <tr>
                <th class="feature">Cloud Storage Integration</th>
                <td><font-awesome-icon icon="check" /></td>
                <td><font-awesome-icon icon="check" /></td>
                <td><font-awesome-icon icon="check" /></td>
            </tr>
            <tr>
                <th class="feature">User Accounts</th>
                <td><font-awesome-icon icon="check" /></td>
                <td><font-awesome-icon icon="check" /></td>
                <td><font-awesome-icon icon="check" /></td>
            </tr>
            <tr>
                <th class="feature">System Branding</th>
                <td><font-awesome-icon icon="check" /></td>
                <td><font-awesome-icon icon="check" /></td>
                <td><font-awesome-icon icon="check" /></td>
            </tr>
            <tr>
                <th colspan="4" class="group">Support</th>
            </tr>
            <tr>
                <th class="feature">Security & Bug Fixes</th>
                <td></td>
                <td><font-awesome-icon icon="check" /></td>
                <td><font-awesome-icon icon="check" /></td>
            </tr>
            <tr>
                <th class="feature">Community Support (Slack, Stack Exchange)</th>
                <td></td>
                <td><font-awesome-icon icon="check" /></td>
                <td><font-awesome-icon icon="check" /></td>
            </tr>
            <tr>
                <th class="feature">Developer Support</th>
                <td></td>
                <td></td>
                <td><font-awesome-icon icon="check" /></td>
            </tr>
        </table>
    </div>
</template>

<script>
    export default {

        props: ['pluginSnippet'],

    }
</script>

<style lang="scss">
    .plugin-pricing {
        .edition-name {
            @apply .border .border-blue .rounded .inline-block .px-2 .py-1 .uppercase .font-normal .text-blue;
        }

        .price {
            @apply .font-normal;
        }

        table {
            tr {
                th {
                    &.group {
                        @apply .pt-6;
                    }

                    &.feature {
                        @apply .font-normal;
                    }
                }
            }
        }
    }

    @media (max-width: 991px) {
        .plugin-pricing {
            table {
                tr {
                    &.editions {
                        @apply .flex;

                        th:first-child {
                            display: none;
                        }
                    }

                    th:not(.edition) {
                        display: block;
                        width: auto;
                        clear: left;
                    }

                    td, th.edition {
                        display: block;
                        width: 33%;
                        float: left;
                        text-align: center;
                        min-height: 2.2em;

                        &:nth-child(2n) {
                            @apply .bg-grey-lighter;
                        }
                    }
                }
            }
        }
    }
</style>
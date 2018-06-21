<template>
    <div class="plugin-pricing" v-if="pluginSnippet.editions[0].price != null && pluginSnippet.editions[0].price !== '0.00'">
        <h2 id="pricing" class="mt-4">Pricing</h2>
        <table class="data w-full">
            <tr>
                <th></th>
                <th>
                    <div class="mb-2">
                        <div class="edition-name">Lite</div>
                    </div>
                    <div class="price">
                        <strong>{{ (pluginSnippet.editions[0].price / 4)|currency }}</strong>
                        then {{ (pluginSnippet.editions[0].renewalPrice / 4)|currency }}/year
                    </div>
                </th>
                <th>
                    <div class="mb-2">
                        <div class="edition-name">Standard</div>
                    </div>
                    <div class="price">
                        <strong>{{ (pluginSnippet.editions[0].price * 1)|currency }}</strong>
                        then {{ (pluginSnippet.editions[0].renewalPrice * 1)|currency }}/year
                    </div>
                </th>
            </tr>
            <tr>
                <th colspan="3" class="group">Features</th>
            </tr>
            <tr>
                <th class="feature">Content Modeling</th>
                <td>Yes</td>
                <td>Yes</td>
            </tr>
            <tr>
                <th class="feature">Multi-site Multi-lingual</th>
                <td>Yes</td>
                <td>Yes</td>
            </tr>
            <tr>
                <th class="feature">Cloud Storage Integration</th>
                <td>Yes</td>
                <td>Yes</td>
            </tr>
            <tr>
                <th class="feature">User Accounts</th>
                <td>Yes</td>
                <td>Yes</td>
            </tr>
            <tr>
                <th class="feature">System Branding</th>
                <td>Yes</td>
                <td>Yes</td>
            </tr>
            <tr>
                <th colspan="3" class="group">Support</th>
            </tr>
            <tr>
                <th class="feature">Security & Bug Fixes</th>
                <td>No</td>
                <td>Yes</td>
            </tr>
            <tr>
                <th class="feature">Community Support (Slack, Stack Exchange)</th>
                <td>No</td>
                <td>Yes</td>
            </tr>
            <tr>
                <th class="feature">Developer Support</th>
                <td>No</td>
                <td>Yes</td>
            </tr>
        </table>
    </div>
</template>

<script>
    export default {

        props: ['pluginSnippet'],

    }
</script>
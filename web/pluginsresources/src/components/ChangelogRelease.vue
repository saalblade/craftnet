<template>
    <div class="changelog-release">
        <div class="version">
            <h2>Version {{ version }}</h2>
            <div class="date">2018.06.01</div>
        </div>

        <div class="details">
            <h3>Added</h3>
            <ul>
                <li>Allow the use of Emoji in the plugin settings, such as for the devMode title prefix</li>
            </ul>

            <h3>Changed</h3>
            <ul>
                <li>Set the default devMode title prefix to 🚧</li>
                <li>Sync section / category group handles that are renamed</li>
                <li>Don't log meta item error messages unless devMode is on</li>
                <li>Don’t encode preview URLs</li>
                <li>Fixed an issue where disabling a section’s URLs was not sync properly with the Content SEO settings</li>
            </ul>
        </div>
    </div>
</template>

<script>
    export default {

        props: ['version']

    }
</script>
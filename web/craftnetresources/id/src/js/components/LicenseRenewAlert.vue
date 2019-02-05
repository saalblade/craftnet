<template>
    <page-alert v-if="expiringLicenses.length > 0">
        <template v-if="expiringLicenses.length === 1">
            {{ expiringLicenses.length }} {{ type }} license will lose its access to updates soon. Renew it now to keep getting updates.
        </template>
        <template v-else>
            {{ expiringLicenses.length }} {{ type }} licenses will lose their access to updates soon. Renew them now to keep getting updates.
        </template>
    </page-alert>
</template>


<script>
    import PageAlert from './PageAlert'

    export default {

        props: ['expiringLicenses', 'type'],

        components: {
            PageAlert,
        },

    }
</script>

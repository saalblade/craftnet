<template>
    <div v-if="expiringLicenses.length > 0" class="license-renew-alert alert alert-info" role="alert">
        <template v-if="expiringLicenses.length === 1">
            {{ expiringLicenses.length }} {{ type }} license will lose its access to updates soon. Renew it now to keep getting updates.
        </template>
        <template v-else>
            {{ expiringLicenses.length }} {{ type }} licenses will lose their access to updates soon. Renew them now to keep getting updates.
        </template>
    </div>
</template>


<script>
    export default {

        props: ['expiringLicenses', 'type'],

    }
</script>

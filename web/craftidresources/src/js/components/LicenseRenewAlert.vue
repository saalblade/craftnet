<template>
    <div v-if="expiringLicenses.length > 0" class="alert alert-info" role="alert">
        <template v-if="expiringLicenses.length === 1">
            {{ expiringLicenses.length }} {{ type }} license is about to expire. Renew it now to keep getting updates.
        </template>
        <template v-else>
            {{ expiringLicenses.length }} {{ type }} licenses are about to expire. Renew them now to keep getting updates.
        </template>
    </div>
</template>


<script>
    export default {

        props: ['expiringLicenses', 'type'],

    }
</script>

<template>
    <div v-if="renewLicensesTotal > 0" class="alert alert-info" role="alert">
        <template v-if="renewLicensesTotal === 1">
            {{ renewLicensesTotal }} of your licenses is about to expire. Renew it now to keep getting updates.
        </template>
        <template v-else>
            {{ renewLicensesTotal }} of your licenses are about to expire. Renew them now to keep getting updates.
        </template>
    </div>
</template>


<script>
    import {mapState} from 'vuex'

    export default {

        computed: {

            ...mapState({
                renewLicensesTotal: state => state.craftId.renewLicensesTotal,
            }),

        },

    }
</script>

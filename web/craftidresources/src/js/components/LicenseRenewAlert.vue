<template>

    <div v-if="enableRenewalFeatures" class="alert alert-info" role="alert">
        {{ renewLicenses.length }} of your licenses are about to expire. <router-link to="/account/licenses/renew">Renew them now</router-link> and get a discount.
    </div>

</template>


<script>
    import {mapState} from 'vuex'

    export default {

        computed: {

            ...mapState({
                enableRenewalFeatures: state => state.craftId.enableRenewalFeatures,
                renewLicenses: state => state.craftId.renewLicenses,
            }),

        },

    }
</script>

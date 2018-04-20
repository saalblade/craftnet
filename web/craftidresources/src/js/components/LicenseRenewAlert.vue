<template>

    <div class="alert alert-info" role="alert">
        {{ renewLicenses.length }} of your licenses are about to expire. Renew them now to keep getting updates.
    </div>

</template>


<script>
    import {mapState} from 'vuex'

    export default {

        computed: {

            ...mapState({
                renewLicenses: state => state.craftId.renewLicenses,
            }),

        },

    }
</script>

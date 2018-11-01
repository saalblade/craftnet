<template>
	<div>
		<h1>Partner</h1>

        <div v-if="loadState == LOADING" class="text-center">
            <div class="spinner big mt-8"></div>
        </div>

        <p v-if="loadState == LOAD_ERROR">Error: {{ loadError }}</p>

        <div v-if="loadState == LOADED">
            <partner-business-info :partner="partner"></partner-business-info>
            <!-- <partner-locations></partner-locations> -->
        </div>
	</div>
</template>

<script>
    import {mapState} from 'vuex'
    import PartnerBusinessInfo from '../components/PartnerInfo'
    import PartnerLocations from '../components/PartnerLocations'

    export default {

        data() {
            return {
                LOADED: 'loaded',
                LOADING: 'loading',
                LOAD_ERROR: 'loadError',

                loadState: 'loading',
                loadError: ''
            }
        },

        components: {
            PartnerBusinessInfo,
            PartnerLocations
        },

        computed: {
            ...mapState({
                partner: state => state.partner.partner,
            }),
        },

        mounted() {
            this.$store.dispatch('initPartner')
                .then(() => {
                    this.loadState = this.LOADED
                })
                .catch((response) => {
                    this.loadState = this.LOAD_ERROR
                    this.loadError = response.data.error || 'Couldn’t load partner profile'
                })
        }
    }
</script>

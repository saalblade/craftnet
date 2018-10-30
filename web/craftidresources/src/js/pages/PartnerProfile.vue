<template>
	<div>
		<h1>Partner</h1>

        <div v-if="loadState == LOADING" class="text-center">
            <div class="spinner big mt-8"></div>
        </div>

        <p v-if="loadState == LOAD_ERROR">{{ loadError }}</p>

        <div v-if="loadState == LOADED">
            {{ partner }}
            <partner-business-info></partner-business-info>
            <!-- <partner-locations></partner-locations> -->
        </div>
	</div>
</template>

<script>
    import {mapState} from 'vuex'
    import PartnerBusinessInfo from '../components/PartnerBusinessInfo'
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
                partner: state => state.partner.partnerProfile,
            }),
        },

        mounted() {
            if (this.partner) {
                this.loadState = this.LOADED
            } else {
                this.$store.dispatch('initPartnerProfile')
                    .then(() => {
                        this.loadState = this.LOADED
                    })
                    .catch((response) => {
                        this.loadState = this.LOAD_ERROR
                        this.loadError = response.data.error || 'Couldn’t load partner profile'
                    })
            }
        }
    }
</script>

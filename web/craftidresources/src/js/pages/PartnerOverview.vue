<template>
	<div>
		<h1>Partner Overview</h1>

        <div v-if="!partnerConsent">
            <p>Links to privacy policy, terms, etc. here</p>
            <p>
                <label><input type="checkbox" v-model="partnerConsent"> I have read
                    and all policies and consent</label>
            </p>
        </div>
        <div v-else>
            <p>Overview of Profile here.</p>
            <p>Profile completion progress or badges for:</p>
            <ul>
                <li>Craft Verified</li>
                <li>Craft Commerce Verified</li>
                <li>Enterprise Verified</li>
            </ul>
        </div>
	</div>
</template>

<script>
    import {mapState} from 'vuex'

    export default {

        data() {
            return {
                partnerConsent: false,
            }
        },

        components: {},

        computed: {
          ...mapState({
            hasApiToken: state => state.developers.hasApiToken,
            currentUser: state => state.account.currentUser,
          }),
        },

        methods: {},

        mounted() {}
    }
</script>

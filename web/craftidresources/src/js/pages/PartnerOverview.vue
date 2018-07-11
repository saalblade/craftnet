<template>
	<div>
		<h1>Partner Overview</h1>

        <partner-policy-agreement v-if="!currentUser.acceptsPartnerPolicy"></partner-policy-agreement>

        <div v-else class="card mb-4">
            <div class="card-body">
                <p class="mb-4">Overview of Profile here.</p>
                <p>Profile completion progress or badges for:</p>
                <ul>
                    <li>Craft Verified</li>
                    <li>Craft Commerce Verified</li>
                    <li>Enterprise Verified</li>
                </ul>
            </div>
        </div>
	</div>
</template>

<script>
    import {mapState} from 'vuex'
    import PartnerPolicyAgreement from '../components/PartnerPolicyAgreement'

    export default {

        data() {},

        components: {
            PartnerPolicyAgreement
        },

        computed: {
            ...mapState({
                currentUser: state => state.account.currentUser,
            }),
        },
    }
</script>

<template>
	<div>
		<h1>Developer Settings</h1>

		<stripe-account></stripe-account>
		
		<connected-apps title="Connected Apps"></connected-apps>

		<div class="card mb-4">
			<div class="card-body">
				<h4>API Token</h4>

				<form @submit.prevent="generateToken()">

					<p v-if="notice">This is your new API token, <strong>keep it someplace safe</strong>.</p>
					<text-field id="apiToken" class="mono" spellcheck="false"
								v-model="apiToken" :disabled="true"/>

					<input v-if="apiToken" type="submit" class="btn btn-primary" value="Generate new API Token"/>
					<input v-else type="submit" class="btn btn-primary" value="Generate API Token"/>

					<div class="spinner" v-if="loading"></div>
				</form>
			</div>
		</div>
	</div>
</template>

<script>
    import {mapGetters} from 'vuex'
    import StripeAccount from '../components/StripeAccount'
    import TextField from '../components/fields/TextField'
    import ConnectedApps from '../components/ConnectedApps'

    export default {

        data() {
            return {
                apiToken: '',
                loading: false,
				notice: false,
            }
        },

        components: {
            StripeAccount,
            TextField,
            ConnectedApps
        },

        computed: {

            ...mapGetters({
                currentUser: 'currentUser',
            }),

        },

        methods: {

            generateToken() {
                this.loading = true

                this.$store.dispatch('generateApiToken')
                    .then(response => {
                        this.apiToken = response.data.apiToken
                        this.notice = true
                        this.loading = false
                        this.$root.displayNotice('API token generated.')
                    })
                    .catch(response => {
                        this.loading = false
                        const errorMessage = response.data && response.data.error ? response.data.error : 'Couldn’t generate API token.'
                        this.$root.displayError(errorMessage)
                    });
            }

        },

        created() {
            if (this.currentUser.hasApiToken) {
                this.apiToken = '****************************************'
            }
        }

    }
</script>

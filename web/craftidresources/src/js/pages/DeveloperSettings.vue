<template>
	<div>
		<h1>Developer Settings</h1>
		<div class="card mb-3">
			<div class="card-body">
				<h4>Connected Apps</h4>
				<connected-apps title="Connected Apps" :show-stripe="true"></connected-apps>
			</div>
		</div>
		<div class="card mb-4">
			<div class="card-body">
				<h4>API Token</h4>

				<form @submit.prevent="generateToken()">
					<p v-if="notice">This is your new API token, <strong>keep it someplace safe</strong>.</p>
					<text-field id="apiToken" ref="apiTokenField" class="mono" spellcheck="false" v-model="apiToken" :readonly="true"/>

					<input v-if="apiToken" type="submit" class="btn btn-primary" value="Generate new API Token"/>
					<input v-else type="submit" class="btn btn-primary" value="Generate API Token"/>

					<div class="spinner" v-if="loading"></div>
				</form>
			</div>
		</div>
	</div>
</template>

<script>
    import {mapState} from 'vuex'
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
            TextField,
            ConnectedApps
        },

        computed: {

			...mapState({
                hasApiToken: state => state.developers.hasApiToken,
				currentUser: state => state.account.currentUser,
			}),

        },

        methods: {

            generateToken() {
                this.loading = true

                this.$store.dispatch('generateApiToken')
                    .then(response => {
						this.apiToken = response.data.apiToken

						const apiTokenInput = this.$refs.apiTokenField.$el.querySelector('input')

						this.$nextTick(() => {
							apiTokenInput.select();
						})

						this.notice = true
						this.loading = false
						this.$root.displayNotice('API token generated.')
                    })
                    .catch(response => {
                        this.loading = false
                        const errorMessage = response.data && response.data.error ? response.data.error : 'Couldn’t generate API token.'
                        this.$root.displayError(errorMessage)
                    });
            },

        },

        mounted() {
            if (this.hasApiToken) {
                this.apiToken = '****************************************'
            }
        }

    }
</script>

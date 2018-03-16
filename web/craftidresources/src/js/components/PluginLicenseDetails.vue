<template>
	<div class="card mb-3">
		<div class="card-body">
			<h4 class="mb-4">License Details</h4>
			<template v-if="license">
				<div class="md:flex -mx-4">
					<div class="md:w-1/2 px-4">
						<dl>
							<template v-if="license.plugin">
								<dt>Plugin</dt>
								<dd>{{ license.plugin.name }}</dd>
							</template>

							<dt>License Key</dt>
							<dd><code>{{ license.key|formatPluginLicense }}</code></dd>

							<dt>CMS License</dt>
							<dd>
								<template v-if="license.cmsLicense">
									<code>
										<router-link :to="'/account/licenses/craft/'+license.cmsLicenseId">
											{{ license.cmsLicense.key.substr(0, 10) }}
										</router-link>
									</code>
									<span class="text-secondary">(Craft {{ license.cmsLicense.edition }})</span>
								</template>
								<template v-else>
									<span class="text-secondary">Not attached to a CMS license.</span>
								</template>
							</dd>
						</dl>
					</div>
					<div class="md:w-1/2 px-4">
						<dl>
							<dt>Email</dt>
							<dd>{{ license.email }}</dd>

							<template v-if="enableCommercialFeatures">
								<dt>Update Period</dt>
								<dd>2017/05/11 to 2018/05/11</dd>

								<dt>Auto Renew</dt>
								<dd>
									<lightswitch-input @input="saveAutoRenew()" v-model="licenseDraft.autoRenew"></lightswitch-input>
								</dd>
							</template>

							<dt>Created</dt>
							<dd>{{ license.dateCreated }}</dd>
						</dl>
					</div>
				</div>
			</template>
		</div>
	</div>
</template>

<script>
    import {mapGetters} from 'vuex'
    import LightswitchInput from '../components/inputs/LightswitchInput'

    export default {

        props: ['license', 'type'],

        data() {
            return {
                errors: {},
                licenseDraft: {},
            }
        },

        components: {
            LightswitchInput,
        },

        computed: {

            ...mapGetters({
                enableCommercialFeatures: 'enableCommercialFeatures',
            }),

        },

        methods: {

            /**
             * Save auto renew
             */
            saveAutoRenew() {
                this.$store.dispatch('saveLicense', {
                    id: this.license.id,
                    type: this.type,
                    autoRenew: (this.licenseDraft.autoRenew ? 1 : 0),
                }).then((data) => {
                    if (this.licenseDraft.autoRenew) {
                        this.$root.displayNotice('Auto renew enabled.');
                    } else {
                        this.$root.displayNotice('Auto renew disabled.');
                    }

                }).catch((data) => {
                    this.$root.displayError('Couldn’t save license.');
                    this.errors = data.errors;
                });
            },

        },

        mounted() {
            this.licenseDraft = {
                autoRenew: (this.license.autoRenew == 1 ? true : false),
                notes: this.license.notes,
            };
        }

    }
</script>

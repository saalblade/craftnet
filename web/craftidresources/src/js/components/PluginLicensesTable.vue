<template>
	<div class="responsive-content">
		<table class="table">
			<thead>
			<tr>
				<th>License Key</th>
				<th>Plugin</th>
				<th v-if="!excludeCmsLicenseColumn">CMS License</th>
				<th>Updates Until</th>
				<th>Auto Renew</th>
			</tr>
			</thead>
			<tbody>
			<template>
				<tr v-for="license in licenses">
					<td>
						<code>
							<router-link v-if="license.key" :to="'/account/licenses/plugins/'+license.id">{{ license.key.substr(0, 4) }}</router-link>
							<template v-else>{{ license.shortKey }}</template>
						</code>
					</td>
					<td>
						<template v-if="license.plugin">
							{{ license.plugin.name }}
						</template>
					</td>
					<td v-if="!excludeCmsLicenseColumn">
						<template v-if="license.cmsLicense">
							<code>
								<router-link v-if="license.cmsLicense.key" :to="'/account/licenses/cms/'+license.cmsLicenseId">{{ license.cmsLicense.key.substr(0, 10) }}</router-link>
								<template v-else>{{ license.cmsLicense.shortKey }}</template>
							</code>
						</template>

						<template v-else>
							—
						</template>
					</td>
					<td>
						<template v-if="license.expiresOn">
							<template v-if="expiresSoon(license)">
								<span class="text-orange">
									{{ license.expiresOn.date|moment("L") }}
								</span>
							</template>
							<template v-else>
								{{ license.expiresOn.date|moment("L") }}
							</template>
						</template>
					</td>
					<td>
						<template v-if="autoRenewSwitch">

							<template v-if="!!license.key">
								<lightswitch-field
										:id="'auto-renew-'+license.id"
										@change="savePluginLicenseAutoRenew(license, $event)"
										:checked.sync="pluginLicensesAutoRenew[license.id]"
										:disabled="!license.key"
								/>
							</template>
							<template v-else>
								<lightswitch-field
										:id="'auto-renew-'+license.id"
										:checked="license.autoRenew"
										:disabled="true"
								/>
							</template>

						</template>
						<template v-else>
							<span v-if="license.autoRenew == 1" class="badge badge-success">Enabled</span>
							<span v-else="" class="badge">Disabled</span>
						</template>
					</td>
				</tr>
			</template>
			</tbody>
		</table>
	</div>
</template>


<script>
    import {mapGetters} from 'vuex'
    import LightswitchField from './fields/LightswitchField'

    export default {

        data() {
            return {
                pluginLicensesAutoRenew: {},
            }
        },

        props: ['licenses', 'excludeCmsLicenseColumn', 'autoRenewSwitch'],

        components: {
            LightswitchField,
        },

        computed: {

            ...mapGetters({
                expiresSoon: 'expiresSoon',
            }),

        },

        methods: {
            savePluginLicenseAutoRenew(license, $event) {
                if(!license.key) {
                    return false;
				}

                const autoRenew = $event.target.checked
                const data = {
                    pluginHandle: license.plugin.handle,
                    key: license.key,
                    autoRenew: autoRenew ? 1 : 0,
                }

                this.$store.dispatch('savePluginLicense', data)
                    .then(response => {
                        if (autoRenew) {
                            this.$root.displayNotice('Auto renew enabled.');
                        } else {
                            this.$root.displayNotice('Auto renew disabled.');
                        }

                        this.$store.dispatch('getCmsLicenses');
                    }).catch(response => {
                    this.$root.displayError('Couldn’t save license.');
                    this.errors = response.errors;
                });
            }
        },

        mounted() {
            this.pluginLicensesAutoRenew = {};

            this.licenses.forEach(function(license) {
                this.pluginLicensesAutoRenew[license.id] = license.autoRenew
            }.bind(this))
        }

    }
</script>

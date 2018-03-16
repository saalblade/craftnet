<template>
	<div class="responsive-content">
		<table class="table">
			<thead>
			<tr>
				<th>License Key</th>
				<th>Plugin</th>
				<th v-if="!excludeCmsLicenseColumn">CMS License</th>

				<template v-if="enableCommercialFeatures">
					<th>Next Payment</th>
					<th>Auto Renew</th>
				</template>
			</tr>
			</thead>
			<tbody>
			<template>
				<tr v-for="license in licenses">
					<td>
						<code>
							<router-link :to="'/account/licenses/plugins/'+license.id">
								{{ license.key.substr(0, 4) }}
							</router-link>
						</code>
					</td>
					<td>
						<template v-if="license.plugin">
							{{ license.plugin.name }}
						</template>
					</td>
					<td v-if="!excludeCmsLicenseColumn">
						<template v-if="license.cmsLicense">
							<code><router-link :to="'/account/licenses/craft/'+license.cmsLicenseId">{{ license.cmsLicense.key.substr(0, 10) }}</router-link></code>
						</template>

						<template v-else>
							—
						</template>
					</td>

					<template v-if="enableCommercialFeatures">
						<td>{{ license.dateCreated }}</td>
						<td>
							<span v-if="license.autoRenew == 1" class="badge badge-success">Enabled</span>
							<span v-else="" class="badge">Disabled</span>
						</td>
					</template>
				</tr>
			</template>
			</tbody>
		</table>
	</div>
</template>


<script>
    import {mapGetters} from 'vuex'

    export default {

        props: ['excludeCmsLicenseColumn', 'licenses'],

        computed: {

            ...mapGetters({
                enableCommercialFeatures: 'enableCommercialFeatures',
            }),

        }

    }
</script>

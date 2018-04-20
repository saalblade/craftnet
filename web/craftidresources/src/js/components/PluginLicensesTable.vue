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
						<template v-if="license.renewalDate">
							{{ license.renewalDate.date|moment("L") }}
						</template>
					</td>
					<td>
						<span v-if="license.autoRenew == 1" class="badge badge-success">Enabled</span>
						<span v-else="" class="badge">Disabled</span>
					</td>
				</tr>
			</template>
			</tbody>
		</table>
	</div>
</template>


<script>
    export default {

        props: ['excludeCmsLicenseColumn', 'licenses'],

    }
</script>

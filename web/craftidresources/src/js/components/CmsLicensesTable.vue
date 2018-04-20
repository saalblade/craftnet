<template>
	<div class="responsive-content">
		<table class="table">
			<thead>
			<tr>
				<th>License Key</th>
				<th>Edition</th>
				<th>Domain</th>
				<th>Updates Until</th>
				<th>Auto Renew</th>
			</tr>
			</thead>
			<tbody>
			<template>
				<tr v-for="license in licenses">
					<td>
						<code>
							<router-link v-if="license.key" :to="'/account/licenses/cms/'+license.id">
								{{ license.key.substr(0, 10) }}
							</router-link>

							<template v-else>
								{{ license.shortKey }}
							</template>
						</code>
					</td>
					<td>{{ license.edition }}</td>
					<td>{{ license.domain }}</td>
					<td>{{ license.renewalDate.date|moment("L") }}</td>
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

        props: ['licenses'],

    }
</script>

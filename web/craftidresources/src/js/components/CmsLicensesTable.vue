<template>
	<div class="responsive-content">
		<table class="table">
			<thead>
			<tr>
				<th>License Key</th>
				<th>Edition</th>
				<th>Domain</th>

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
							<router-link :to="'/account/licenses/cms/'+license.id">
								{{ license.key.substr(0, 10) }}
							</router-link>
						</code>
					</td>

					<td>{{license.edition}}</td>

					<td>{{ license.domain }}</td>

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

        props: ['licenses'],

        computed: {

            ...mapGetters({
                enableCommercialFeatures: 'enableCommercialFeatures',
            }),

        }

    }
</script>

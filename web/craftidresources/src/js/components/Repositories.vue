<template>
	<div>
		<h3>{{ appHandle }}</h3>

		<div v-if="app.repositories.length > 0" class="list-group">
			<div v-for="repository in app.repositories" class="list-group-item">
				<div class="d-flex">
					<div class="media-body">
						{{ repository.full_name }}
					</div>
					<div>
						<a href="#" class="btn btn-sm btn-primary" @click.prevent="$emit('selectRepository', repository)">Select</a>
					</div>
				</div>
			</div>
		</div>

		<p v-else="">No repositories.</p>
	</div>
</template>


<script>
    import { mapGetters } from 'vuex'

    export default {

        props: ['appHandle'],

        computed: {

            ...mapGetters({
                apps: 'apps',
            }),

			app() {
                return this.apps[this.appHandle];
			}

        }

    }
</script>
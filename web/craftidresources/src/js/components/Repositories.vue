<template>
	<div>
		<!--<h3>{{ appHandle }}</h3>-->

		<div v-if="app.repositories.length > 0" class="list-group">
			<div v-for="repository in app.repositories" class="list-group-item">
				<div class="d-flex">
					<div class="media-body">
						{{ repository.full_name }}
					</div>
					<div>
						<div v-if="isLoading(repository.html_url)" class="spinner"></div>
						<a v-if="!repositoryIsInUse(repository.html_url)" href="#" class="btn btn-sm btn-primary" @click.prevent="$emit('selectRepository', repository)">Select</a>
						<a v-else href="#" class="btn btn-sm btn-light disabled" :class="{ disabled: repositoryIsInUse(repository.html_url )}">Already in use</a>
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

        props: ['appHandle', 'loadingRepository'],

        computed: {

            ...mapGetters({
                apps: 'apps',
                repositoryIsInUse: 'repositoryIsInUse',
            }),

			app() {
                return this.apps[this.appHandle];
			}

        },

        methods: {
          	isLoading(repositoryUrl) {
				if(this.loadingRepository === repositoryUrl) {
				    return true;
				}

				return false;
			}
		}

    }
</script>
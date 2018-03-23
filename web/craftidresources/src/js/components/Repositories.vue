<template>
	<div>
		<!--<h3>{{ appHandle }}</h3>-->

		<text-field placeholder="Filter repositories" v-model="q" />


		<div v-if="filteredRepositories.length > 0" class="list-group">
			<div v-for="repository in filteredRepositories" class="list-group-item">
				<div class="flex items-center">
					<div class="flex-1">
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
    import filter from 'lodash/filter';
    import includes from 'lodash/includes';
    import {mapGetters} from 'vuex';
    import TextField from '../components/fields/TextField';

    export default {

        components: {
            TextField
        },

        props: ['appHandle', 'loadingRepository'],

        data() {
            return {
                q: ''
            };
        },

        computed: {

            ...mapGetters({
                apps: 'apps',
                repositoryIsInUse: 'repositoryIsInUse',
            }),

            app() {
                return this.apps[this.appHandle];
            },

            repositories() {
                let unusedRepos = this.app.repositories.filter(r => !this.repositoryIsInUse(r.html_url));
                let inUseRepos = this.app.repositories.filter(r => this.repositoryIsInUse(r.html_url));

                return unusedRepos.concat(inUseRepos);
            },

            filteredRepositories() {
                let searchQuery = this.q;

                if (!searchQuery) {
                    return this.repositories;
                }

                return filter(this.repositories, r => {
                    if (r.full_name && includes(r.full_name.toLowerCase(), searchQuery.toLowerCase())) {
                        return true;
                    }
                });
            }

        },

        methods: {

            /**
             * Is repository loading?
             *
             * @param repositoryUrl
             * @returns {boolean}
             */
            isLoading(repositoryUrl) {
                return this.loadingRepository === repositoryUrl;
            }

        }

    }
</script>
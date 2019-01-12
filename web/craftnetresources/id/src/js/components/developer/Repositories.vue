<template>
    <div>
        <text-field placeholder="Filter repositories" v-model="q" />

        <list-group v-if="filteredRepositories.length > 0">
            <list-group-item v-for="(repository, key) in filteredRepositories" :key="key">
                <div class="flex items-center">
                    <div class="flex-1">
                        {{ repository.full_name }}
                    </div>
                    <div>
                        <spinner v-if="isLoading(repository.html_url)"></spinner>
                        <a v-if="!repositoryIsInUse(repository.html_url)" href="#" class="btn btn-sm btn-primary" @click.prevent="$emit('selectRepository', repository)">Select</a>
                        <a v-else href="#" class="btn btn-sm btn-light disabled" :class="{ disabled: repositoryIsInUse(repository.html_url )}">Already in use</a>
                    </div>
                </div>
            </list-group-item>
        </list-group>

        <p v-else="">No repositories.</p>
    </div>
</template>

<script>
    import filter from 'lodash/filter';
    import includes from 'lodash/includes';
    import {mapState, mapGetters} from 'vuex';
    import ListGroup from '../ListGroup'
    import ListGroupItem from '../ListGroupItem'
    import Spinner from '../Spinner'

    export default {

        props: ['appHandle', 'loadingRepository'],

        data() {
            return {
                q: ''
            };
        },

        components: {
            ListGroup,
            ListGroupItem,
            Spinner,
        },

        computed: {

            ...mapState({
                apps: state => state.account.apps,
            }),

            ...mapGetters({
                repositoryIsInUse: 'developers/repositoryIsInUse',
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
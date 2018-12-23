<template>
    <div class="search" :class="{open: searchVisible}">
        <search-form ref="searchForm" @searchQueryBlur="searchQueryBlur()" />

        <a class="search-toggle" @click="showSearch()">
            <font-awesome-icon icon="search" />
        </a>
    </div>
</template>

<script>
    import SearchForm from '../components/SearchForm'

    export default {

        data() {
            return {
                searchVisible: false,
            }
        },

        components: {
            SearchForm,
        },

        methods: {

            showSearch() {
                this.searchVisible = true
                const searchQueryInput = this.$refs.searchForm.$refs.searchQuery

                this.$nextTick(() => {
                    searchQueryInput.focus()
                })
            },

            searchQueryBlur() {
                this.searchVisible = false
            }

        },

        created() {
            // console.log('env', process.env.NODE_ENV);

            if (this.$route.query.q) {
                this.$store.commit('app/updateSearchQuery', this.$route.query.q)
            }
        },

    }
</script>

<style lang="scss">
    .search {
        @apply .self-center .mt-2 .mb-4;

        .search-form {
            @apply .hidden;
        }

        &.open {
            @apply .absolute;
            top: 0.5rem;
            left: 1.5rem;
            right: 1.5rem;

            .search-form {
                @apply .block;
            }

            .search-toggle {
                @apply .hidden;
            }
        }
    }

    @media (min-width: 576px) {
        .search {
            @apply .flex-1;

            .search-form {
                @apply .block;
            }

            .search-toggle {
                @apply .hidden;
            }
        }
    }
</style>
<template>
    <form class="search-form" @submit.prevent="submitSearch">
        <input ref="searchQuery" class="text w-full" id="searchQuery" name="searchQuery" type="text" :placeholder="'Search plugins'" v-model="searchQuery" autocomplete="off" @blur="$emit('searchQueryBlur')">
        <div class="clear" :class="{ hidden: searchQuery.length == 0 }" @click="searchQuery = ''" title="Clear"></div>
        <div class="search-icon">
            <font-awesome-icon icon="search" />
        </div>
    </form>
</template>

<script>
    export default {

        computed: {

            searchQuery: {
                get () {
                    return this.$store.state.app.searchQuery
                },
                set (value) {
                    this.$store.commit('app/updateSearchQuery', value)
                }
            }

        },

        methods: {

            submitSearch() {
                this.$router.push({path: '/search', query: { q: this.searchQuery }})
                this.$refs.searchQuery.blur()
            }

        }
    }
</script>

<style lang="scss">
    .search-form {
        @apply .flex-1 .relative .mt-2 mb-6;

        input.text {
            padding-left: 32px;

            &::placeholder {
                @apply .text-grey-dark;
            }
        }

        svg[data-icon="search"] {
            @apply .absolute .text-grey-dark;
            top: 10px;
            left: 12px;
            max-width: 16px;
        }
    }
</style>
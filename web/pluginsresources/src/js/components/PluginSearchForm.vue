<template>
    <div class="search-form">
        <input class="text w-full" id="searchQuery" name="searchQuery" type="text" :placeholder="'Search plugins'" v-model="searchQuery">
        <div class="clear" :class="{ hidden: searchQuery.length == 0 }" @click="searchQuery = ''" title="Clear"></div>
    </div>
</template>

<script>
    import * as types from '../store/mutation-types'

    export default {

        computed: {
            searchQuery: {
                get () {
                    return this.$store.state.pluginStore.searchQuery
                },
                set (value) {
                    this.$store.commit(types.UPDATE_SEARCH_QUERY, value)
                }
            }

        }
    }
</script>
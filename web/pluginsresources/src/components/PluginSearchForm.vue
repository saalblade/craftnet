<template>
    <form class="search-form" @submit.prevent="submitSearch">
        <input class="text w-full" id="searchQuery" name="searchQuery" type="text" :placeholder="'Search plugins'" v-model="searchQuery">
        <div class="clear" :class="{ hidden: searchQuery.length == 0 }" @click="searchQuery = ''" title="Clear"></div>
    </form>
</template>

<script>
    export default {

        data() {
            return {
                searchQuery: '',
            }
        },

        methods: {

            submitSearch() {
                this.$router.push({path: '/search', query: { q: this.searchQuery }});
            }

        }
    }
</script>
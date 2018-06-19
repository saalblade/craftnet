<template>
    <form class="search-form" @submit.prevent="submitSearch">
        <input ref="searchQuery" class="text w-full" id="searchQuery" name="searchQuery" type="text" :placeholder="'Search plugins'" v-model="searchQuery">
        <div class="clear" :class="{ hidden: searchQuery.length == 0 }" @click="searchQuery = ''" title="Clear"></div>
        <div class="search-icon">
            <font-awesome-icon :icon="icon" />
        </div>
    </form>
</template>

<script>
    import FontAwesomeIcon from '@fortawesome/vue-fontawesome'
    import faSearch from '@fortawesome/fontawesome-free-solid/faSearch'

    export default {

        components: {
            FontAwesomeIcon,
        },

        computed: {

            icon () {
                return faSearch
            },

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
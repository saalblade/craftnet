<template>
    <div v-if="category">
        <h1>{{category.title}}</h1>

        <plugin-index :plugins="plugins" :columns="4"></plugin-index>
    </div>
</template>

<script>
    import {mapGetters} from 'vuex'
    import PluginIndex from '../../../components/PluginIndex'
    import Navigation from '../../../components/Navigation'

    export default {

        layout: 'site',

        components: {
            PluginIndex,
            Navigation,
        },

        head () {
            return {
                title: 'Category',
                meta: [
                    { hid: 'description', name: 'description', content: 'My category description' }
                ]
            }
        },

        computed: {

            ...mapGetters({
                getCategoryById: 'pluginStore/getCategoryById',
                getPluginsByCategory: 'pluginStore/getPluginsByCategory',
            }),

            categoryId() {
                return this.$route.params.id
            },

            category() {
                let category = this.getCategoryById(this.categoryId)

                if (category) {
                    this.$root.pageTitle = category.title
                }

                return category
            },

            plugins() {
                return this.getPluginsByCategory(this.categoryId)
            }

        },

    }
</script>
<template>
    <div v-if="category">
        <h1>{{category.title}}</h1>
        <plugin-index :plugins="plugins" :columns="4"></plugin-index>
    </div>
</template>

<script>
    import {mapState, mapGetters} from 'vuex'
    import PluginIndex from '../../../components/PluginIndex'

    export default {

        async fetch ({ store, params }) {
            let category = store.getters['pluginStore/getCategoryById'](params.id)

            await store.commit('app/updatePageMeta', {
                title: category.title,
                description: category.title + ' category.'
            })
        },

        head () {
            return {
                title: this.pageMeta.title,
                meta: [
                    { hid: 'description', name: 'description', content: this.pageMeta.description }
                ]
            };
        },

        layout: 'site',

        components: {
            PluginIndex,
        },

        computed: {

            ...mapState({
                pageMeta: state => state.app.pageMeta,
            }),

            ...mapGetters({
                getCategoryById: 'pluginStore/getCategoryById',
                getPluginsByCategory: 'pluginStore/getPluginsByCategory',
            }),

            categoryId() {
                return this.$route.params.id
            },

            category() {
                return this.getCategoryById(this.categoryId)
            },

            plugins() {
                return this.getPluginsByCategory(this.categoryId)
            }

        },

    }
</script>
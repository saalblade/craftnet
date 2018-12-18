<template>
    <div v-if="category" class="xcontainer py-6">
        <h1>{{category.title}}</h1>
        <plugin-index :plugins="plugins"></plugin-index>
    </div>
</template>

<script>
    import {mapState, mapGetters} from 'vuex'
    import PluginIndex from '../../../components/PluginIndex'

    export default {

        async fetch ({ store, params }) {
            let category = store.getters['pluginStore/getCategoryBySlug'](params.slug)

            await store.commit('app/updatePageMeta', {
                title: category.title,
                description: category.description,
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
                getCategoryBySlug: 'pluginStore/getCategoryBySlug',
                getPluginsByCategorySlug: 'pluginStore/getPluginsByCategorySlug',
            }),

            categorySlug() {
                return this.$route.params.slug
            },

            category() {
                return this.getCategoryBySlug(this.categorySlug)
            },

            plugins() {
                return this.getPluginsByCategorySlug(this.categorySlug)
            }

        },

    }
</script>
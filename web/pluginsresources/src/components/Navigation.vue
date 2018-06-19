<template>
    <div class="navigation">
        <template v-if="featuredPlugins">
            <h3>{{ "Staff Picks" }}</h3>
            <ul>
                <template v-for="featuredPlugin in featuredPlugins">
                    <li><nuxt-link :to="'/featured/'+featuredPlugin.id">{{ featuredPlugin.title }}</nuxt-link></li>
                </template>
            </ul>
        </template>

        <h3>{{ "Categories" }}</h3>
        <ul class="categories">
            <li v-for="category in categories">
                <nuxt-link :to="'/categories/'+category.id">
                    <img :src="category.iconUrl" height="24" />
                    {{ category.title }}
                </nuxt-link>
            </li>
        </ul>
    </div>
</template>

<script>
    import {mapState} from 'vuex'

    export default {

        computed: {

            ...mapState({
                categories: state => state.pluginStore.categories,
                featuredPlugins: state => state.pluginStore.featuredPlugins,
            }),

        }

    }
</script>

<template>
    <div v-if="pageMeta && showSeoMeta" class="seo-meta">
        <ul>
            <li><strong>Title:</strong> {{pageMeta.title}}</li>
            <li><strong>Description:</strong> {{pageMeta.description}}</li>
        </ul>
    </div>
</template>

<script>
    import {mapState} from 'vuex'

    export default {

        computed: {

            ...mapState({
                pageMeta: state => state.app.pageMeta,
            }),

            showSeoMeta() {
                return process.env.showSeoMeta
            },

        }

    }
</script>

<style lang="scss">
    .seo-meta {
        @apply .bg-blue-lighter .text-blue-darker .p-4 .rounded .mb-4;

        ul {
            @apply .list-reset;

            li {
                &:not(:last-child) {
                    @apply .mb-2;
                }
            }
        }
    }
</style>
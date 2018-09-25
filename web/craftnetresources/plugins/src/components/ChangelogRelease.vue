<template>
    <div class="changelog-release">
        <div class="version">
            <a :href="'#' + version" class="anchor"><font-awesome-icon :icon="anchorIcon" /></a>
            <h2 :id="version">Version {{ version }}</h2>
            <div class="date">{{date}}</div>
        </div>

        <div class="details">
            <slot></slot>
        </div>
    </div>
</template>

<script>
    import FontAwesomeIcon from '@fortawesome/vue-fontawesome'
    import faLink from '@fortawesome/fontawesome-free-solid/faLink'

    export default {

        props: ['version', 'date'],

        components: {
            FontAwesomeIcon,
        },

        computed: {
            anchorIcon () {
                return faLink
            },
        }

    }
</script>
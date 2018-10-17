<template>
    <div class="changelog-release">
        <div class="version">
            <a :href="'#' + version" class="anchor"><font-awesome-icon icon="link" /></a>
            <h2 :id="version">Version {{ version }}</h2>
            <div class="date">{{date}}</div>
        </div>

        <div class="details">
            <slot></slot>
        </div>
    </div>
</template>

<script>
    export default {

        props: ['version', 'date'],

    }
</script>
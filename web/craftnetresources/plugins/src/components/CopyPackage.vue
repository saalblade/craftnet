<template>
    <div class="copy-package">
        <input ref="input" type="text" class="text w-full" :value="plugin.packageName" @focus="select" readonly="readonly" />
        <a @click="copy" class="btn submit"><font-awesome-icon :icon="copyIcon" /></a>
    </div>
</template>

<script>
    import FontAwesomeIcon from '@fortawesome/vue-fontawesome'
    import faCopy from '@fortawesome/fontawesome-free-solid/faCopy'

    export default {

        props: ['plugin'],

        components: {
            FontAwesomeIcon,
        },

        computed: {

            copyIcon () {
                return faCopy
            },

        },

        methods: {

            select() {
                this.$refs.input.select()
            },

            copy() {
                this.select();

                window.document.execCommand('copy');
            },

        }

    }
</script>
<template>
    <div class="copy-package">
        <input ref="input" type="text" class="text w-full" :value="plugin.packageName" @focus="select" readonly="readonly" />
        <a @click="copy" class="btn submit"><font-awesome-icon icon="copy" /></a>
    </div>
</template>

<script>
    export default {

        props: ['plugin'],

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
<template>
	<input ref="input" class="form-control" type="url" :id="id" :placeholder="placeholder" autocomplete="off" :autofocus="autofocus" :value="value" @input="$emit('input', $event.target.value)" :disabled="disabled">
</template>

<script>
    export default {

        props: ['id', 'placeholder', 'value', 'autofocus', 'disabled'],

        created() {
            this.$on('focus', function () {
                this.$refs.input.focus()
            })
        }

    }
</script>


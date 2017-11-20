<template>
	<input ref="input" class="form-control" type="password" :id="id" :placeholder="placeholder" autocomplete="off" :autofocus="autofocus" :value="value" @input="$emit('input', $event.target.value)">
</template>

<script>
    export default {

        props: ['id', 'placeholder', 'value', 'autofocus'],

        created() {
            this.$on('focus', function () {
                this.$refs.input.focus()
            })
        }

    }
</script>


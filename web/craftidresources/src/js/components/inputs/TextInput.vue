<template>
	<input
		:autofocus="autofocus"
		:disabled="disabled"
		:id="id"
		:placeholder="placeholder"
		:value="value"
		@input="$emit('input', $event.target.value)"
		autocomplete="off"
		class="form-control"
		ref="input"
		type="text"/>
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


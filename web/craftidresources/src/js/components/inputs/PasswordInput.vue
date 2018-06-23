<template>
	<input
		:autofocus="autofocus"
		:id="id"
		:placeholder="placeholder"
		:value="value"
		@input="$emit('input', $event.target.value)"
		autocomplete="off"
		class="form-control"
		ref="input"
		type="password"/>
</template>

<script>
    export default {

        props: ['id', 'placeholder', 'value', 'autofocus'],

        created() {
            this.$on('focus', function() {
                this.$refs.input.focus()
            })
        }

    }
</script>


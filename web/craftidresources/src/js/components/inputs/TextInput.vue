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
		v-mask="mask"
		:autocapitalize="autocapitalize"
		:spellcheck="spellcheck"
		type="text"/>
</template>

<script>
    export default {

        props: ['id', 'placeholder', 'value', 'autofocus', 'disabled', 'mask', 'autocapitalize', 'spellcheck'],

        created() {
            this.$on('focus', function () {
				this.$refs.input.focus()
            })
        }

    }
</script>


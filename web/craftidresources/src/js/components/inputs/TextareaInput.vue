<template>
	<textarea
			:cols="cols"
			:disabled="disabled"
			:id="id"
			:placeholder="placeholder"
			:value="value"
			@input="$emit('input', $event.target.value)"
			autocomplete="off"
			class="form-control"
			type="text">
	</textarea>
</template>

<script>
    export default {

        props: ['id', 'placeholder', 'value', 'cols', 'disabled'],

    }
</script>

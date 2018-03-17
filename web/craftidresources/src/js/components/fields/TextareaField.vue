<template>
	<div class="form-group">
		<label v-if="label" :id="id+'-label'" :for="id">{{ label }}</label>
		<textarea-input
			:autocapitalize="autocapitalize"
			:class="{'is-invalid': errors }"
			:cols="cols"
			:disabled="disabled"
			:id="id"
			:placeholder="placeholder"
			:rows="rows ? rows : 4"
			:spellcheck="spellcheck"
			:value="value"
			@input="$emit('input', $event)"/>
		<div class="invalid-feedback" v-for="error in errors">{{ error }}</div>
	</div>
</template>

<script>
    import TextareaInput from '../inputs/TextareaInput';

    export default {

        props: ['label', 'id', 'placeholder', 'value', 'cols', 'rows', 'errors', 'disabled', 'autocapitalize', 'spellcheck'],

        components: {
            TextareaInput,
        },

    }
</script>

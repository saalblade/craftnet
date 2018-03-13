<template>
	<div class="form-group">
		<label v-if="label" :id="id+'-label'" :for="id">{{ label }}</label>
		<textarea-input :id="id" :class="{'is-invalid': errors }"
						:placeholder="placeholder" :value="value" :cols="cols"
						:rows="rows ? rows : 4" @input="$emit('input', $event)"
						:disabled="disabled"/>
		<div class="invalid-feedback" v-for="error in errors">{{ error }}</div>
	</div>
</template>

<script>
    import TextareaInput from '../inputs/TextareaInput';

    export default {

        props: ['label', 'id', 'placeholder', 'value', 'cols', 'rows', 'errors', 'disabled'],

        components: {
            TextareaInput,
        },

    }
</script>

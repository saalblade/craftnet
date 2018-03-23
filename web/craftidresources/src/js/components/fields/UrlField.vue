<template>
	<div class="form-group">
		<label :id="id" v-if="label">{{ label }}</label>

		<div v-if="instructions" class="instructions">
			<p>{{ instructions }}</p>
		</div>

		<url-input
			:autofocus="autofocus"
			:class="{'is-invalid': errors }"
			:disabled="disabled"
			:id="id"
			:placeholder="placeholder" :value="value"
			@input="$emit('input', $event)"
			ref="input"/>

		<div class="invalid-feedback" v-for="error in errors">{{ error }}</div>
	</div>
</template>

<script>
    import UrlInput from '../inputs/UrlInput';

    export default {

        props: ['label', 'id', 'placeholder', 'value', 'autofocus', 'errors', 'disabled', 'instructions'],

        components: {
            UrlInput,
        },

        created() {
            this.$on('focus', function(msg) {
                this.$refs.input.$emit('focus');
            })
        }

    }
</script>

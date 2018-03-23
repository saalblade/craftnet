<template>
	<div class="form-group">
		<label :id="id" v-if="label">{{ label }}</label>
		<password-input ref="input" :id="id" :class="{'is-invalid': errors }"
						:placeholder="placeholder" :value="value"
						@input="$emit('input', $event)" :autofocus="autofocus"/>
		<div class="invalid-feedback" v-for="error in errors">{{ error }}</div>
	</div>
</template>

<script>
    import PasswordInput from '../inputs/PasswordInput';

    export default {

        props: ['label', 'id', 'placeholder', 'value', 'autofocus', 'errors'],

        components: {
            PasswordInput,
        },

        created() {
            this.$on('focus', function(msg) {
                this.$refs.input.$emit('focus');
            })
        }

    }
</script>

<template>
	<div class="form-group">
		<label :id="id" v-if="label">{{ label }}</label>

		<div v-if="instructions" class="instructions">
			<p>{{ instructions }}</p>
		</div>

		<div class="relative" :class="{'mt-4': max}">
			<div v-if="max"
				 class="text-xs text-right pr-1 absolute" style="right: 0; top: -1rem;"
				 :class="{
					'text-grey': remainingChars >= 10,
					'text-orange': remainingChars < 10 && remainingChars >= 0,
					'text-red': remainingChars < 0
				}">{{ remainingChars }}</div>

			<text-input
					:autofocus="autofocus"
					:class="{
						'is-invalid': errors,
						'text-red-dark': max && max < this.value.length
					}"
					:disabled="disabled"
					:id="id"
					:placeholder="placeholder"
					:value="value"
					:mask="mask"
					@input="$emit('input', $event)"
					:autocapitalize="autocapitalize"
					:spellcheck="spellcheck"
					:readonly="readonly"
					ref="input"/>
		</div>


		<div class="invalid-feedback" v-for="error in errors">{{ error }}</div>
	</div>
</template>

<script>
    import TextInput from '../inputs/TextInput';

    export default {

        props: ['label', 'id', 'placeholder', 'value', 'autofocus', 'errors', 'disabled', 'instructions', 'mask', 'autocapitalize', 'spellcheck', 'readonly', 'max'],

        components: {
            TextInput,
        },

        created() {
            this.$on('focus', function(msg) {
                this.$refs.input.$emit('focus');
            })
        },

		computed: {
            remainingChars() {
                if (this.max) {
                    return this.max - this.value.length
				}
			}
		}

    }
</script>

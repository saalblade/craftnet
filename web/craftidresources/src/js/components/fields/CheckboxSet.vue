<template>
	<div>
		<fieldset class="checkboxes">
			<legend>{{ label }}</legend>

			<ul class="list-reset pl-4 pt-2">
				<li v-for="option in this.options">
					<label>
						<input type="checkbox" class="mr-2" v-model="localValue" :value="option.value" @change="onChange">
						{{ option.label }}
					</label>
				</li>
			</ul>

			<div class="invalid-feedback" v-for="error in errors">{{ error }}</div>
		</fieldset>
	</div>
</template>

<script>
    export default {

		props: ['options', 'label', 'value', 'errors'],

		data() {
			return {
				localValue: []
			}
		},

		methods: {
			onChange(e) {
				this.$emit('input', this.localValue)
			}
		},

		mounted() {
			// clone not to manipulate prop
			this.localValue = (this.value || []).slice(0)
		}
    }
</script>

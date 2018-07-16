<template>
	<div>
		<fieldset class="checkboxes">
			<legend>{{ label }}</legend>

			<ul class="list-reset pl-4 pt-2">
				<li v-for="option in this.options">
					<label>
						<input type="checkbox" class="mr-2" v-model="localValue" :value="option.value" @change="$emit('input', localValue)">
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

		props: ['options', 'value', 'label', 'errors'],

		data() {
			return {
				localValue: []
			}
		},

		mounted() {
			this.$nextTick(() => {
				// clone the array
				this.localValue = this.value.slice(0)
			})
		},

		watch: {
			localValue: (val) => {
				this.value = val
			}
		}
    }
</script>

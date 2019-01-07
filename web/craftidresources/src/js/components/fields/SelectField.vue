<template>
	<div class="form-group">
		<label v-if="label" :id="id+'-label'" :for="id">{{ label }}</label>

        <div v-if="instructions" class="instructions">
			<p>{{ instructions }}</p>
		</div>

		<select-input :options="options" :value="value" @input="$emit('input', $event.target.value)"></select-input>
	</div>
</template>


<script>
    import SelectInput from '../inputs/SelectInput';

    export default {

        props: ['label', 'instructions', 'id', 'options', 'value'],

        components: {
            SelectInput,
        },

    }
</script>

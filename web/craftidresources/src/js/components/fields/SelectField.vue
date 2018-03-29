<template>
	<div class="form-group">
		<label v-if="label" :id="id+'-label'" :for="id">{{ label }}</label>

		<select-input :options="options" :value="value" @input="$emit('input', $event.target.value)"></select-input>
	</div>
</template>


<script>
    import SelectInput from '../inputs/SelectInput';

    export default {

        props: ['label', 'id', 'options', 'value'],

        components: {
            SelectInput,
        },

    }
</script>

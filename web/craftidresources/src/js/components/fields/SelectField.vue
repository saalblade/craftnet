<template>
	<div class="form-group">
		<label v-if="label" :id="id+'-label'" :for="id">{{ label }}</label>

		<select-input :fullwidth="fullwidth" :options="options" :value="value" @input="$emit('input', $event.target.value)"></select-input>
	</div>
</template>


<script>
    import SelectInput from '../inputs/SelectInput';

    export default {

        props: ['label', 'fullwidth', 'id', 'options', 'value'],

        components: {
            SelectInput,
        },

    }
</script>

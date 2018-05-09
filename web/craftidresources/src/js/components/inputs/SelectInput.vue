<template>
	<div>
		<div class="select">
			<select :value="value" :class="{ 'w-full': fullwidth }" @input="$emit('input', $event)">
				<option v-for="option in this.options" :value="option.value">{{ option.label }}</option>
			</select>
		</div>
	</div>
</template>


<script>
    export default {

        props: ['fullwidth', 'options', 'value'],

    }
</script>

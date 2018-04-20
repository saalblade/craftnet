<template>
	<div>
		<label :for="id" class="lightswitch">
			<input :id="id" type="checkbox" :checked="checked" @input="$emit('update:checked', $event.target.checked)" @change="$emit('change', $event)" />
			<div class="slider round"></div>
		</label>
	</div>
</template>

<script>
    export default {

        props: ['id', 'checked'],

    }

</script>

<template>
	<div>
		<label for="t1" class="lightswitch">
			<input id="t1" type="checkbox" :checked="value" @change="$emit('input', $event.target.checked)" />
			<div class="slider round"></div>
		</label>
	</div>
</template>

<script>
    export default {

        props: ['value'],

		methods: {
            turnOn() {
			}
		}

    }

</script>

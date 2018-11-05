<template>
	<div>
        <div class="form-group">
            <label>
                <input :id="id" type="checkbox" class="mr-2" :value="value" :checked="value" @input="$emit('input', $event.target.checked)">
                {{ label }}
            </label>
            <p v-if="instructions" class="text-grey-dark text-sm" v-html="instructions"></p>
            <div class="invalid-feedback" v-for="(error, index) in errors" :key="index">{{ error }}</div>
        </div>
	</div>
</template>

<script>
    export default {
        props: ['errors', 'id', 'label', 'value', 'instructions'],
    }
</script>

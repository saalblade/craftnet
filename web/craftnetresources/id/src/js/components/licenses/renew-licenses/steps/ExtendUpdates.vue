<template>
    <div>
        <select-field :value="renew" @input="$emit('update:renew', $event)" :options="renewOptions" />
        <btn @click="$emit('cancel')">Cancel</btn>
        <btn class="primary" @click="$emit('continue')">Continue</btn>
    </div>
</template>

<script>
    export default {
        props: ['license', 'renew'],

        computed: {
            renewOptions() {
                if (!this.license.expiryDateOptions) {
                    return []
                }

                let options = [];

                for (let i = 0; i < this.license.expiryDateOptions.length; i++) {
                    const expiryDateOption = this.license.expiryDateOptions[i]
                    const date = expiryDateOption[1]
                    const formattedDate = this.$moment(date).format('L')
                    const label = "Extend updates until " + formattedDate

                    options.push({
                        label: label,
                        value: i,
                    })
                }

                return options
            },
        },
    }
</script>
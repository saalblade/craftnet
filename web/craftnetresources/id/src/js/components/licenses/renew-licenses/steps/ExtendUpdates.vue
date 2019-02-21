<template>
    <div>
        <select-field :value="renew" @input="$emit('update:renew', $event)" :options="renewOptions" />
        <button @click="$emit('cancel')" class="btn btn-secondary">Cancel</button>
        <button @click="$emit('continue')" class="btn btn-primary">Continue</button>
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
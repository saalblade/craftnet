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
                let options = []

                for (let i = 1; i <= 5; i++) {
                    let date = new Date();

                    if (!this.license.expired) {
                        date = this.license.expiresOn.date
                    }

                    const renewalDate = this.$moment(date).add(i, 'year')
                    const label = "Extend updates until " + this.$moment(renewalDate).format('L')

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
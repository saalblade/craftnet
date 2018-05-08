<template>
    <div>
        <select-field :value="renew" @input="$emit('update:renew', $event)" :options="renewOptions" />
        <button @click="$emit('cancel')" class="btn btn-secondary">Cancel</button>
        <button @click="$emit('continue')" class="btn btn-primary">Continue</button>
    </div>
</template>

<script>
    import SelectField from '../../fields/SelectField'

    export default {

        props: ['license', 'renew'],

        components: {
            SelectField,
        },

        computed: {

            renewOptions() {
                let options = []
                const edition = this.license.editionDetails
                const renewalPrice = edition.renewalPrice

                for (let i = 1; i <= 5; i++) {
                    const date = this.$moment(this.license.expiresOn.date).add(i, 'year')
                    const formattedDate = this.$moment(date).format('L')
                    const price = renewalPrice * i
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
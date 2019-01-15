<template>
    <div>
        <select-field :value="renew" @input="$emit('update:renew', $event)" :options="renewOptions" />
        <button @click="$emit('cancel')" class="btn btn-secondary">Cancel</button>
        <button @click="$emit('continue')" class="btn btn-primary">Continue</button>
    </div>

</template>

<script>
    import {mapState, mapActions} from 'vuex'

    export default {

        props: ['license', 'renew'],

        computed: {

            ...mapState({
                licenseExpiryDateOptions: state => state.pluginStore.licenseExpiryDateOptions,
            }),

            expiryDateOptions() {
                return this.licenseExpiryDateOptions.cmsLicenses[this.license.id]
            },

            renewOptions() {
                if (!this.expiryDateOptions) {
                    return []
                }

                let options = [];

                for (let i = 0; i < this.expiryDateOptions.length; i++) {
                    const expiryDateOption = this.expiryDateOptions[i]
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
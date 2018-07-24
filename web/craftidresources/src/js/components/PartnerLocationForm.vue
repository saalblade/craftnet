<template>
    <modal
        :show="modalShouldShow"
        :transition="transitionName"
        modal-type="wide"
    >
        <div slot="body" class="p-4">
            <text-field id="title" label="Location Title" v-model="locationDraft.title" :errors="errors.title" />
            <text-field id="addressLine1" label="Address" v-model="locationDraft.addressLine1" :errors="errors.addressLine1" />
            <text-field id="addressLine2" v-model="locationDraft.addressLine2" :errors="errors.addressLine2" />
            <text-field id="city" label="City" v-model="locationDraft.city" :errors="errors.city" />
            <text-field id="state" label="State" v-model="locationDraft.state" :errors="errors.state" />
            <text-field id="zip" label="Zip" v-model="locationDraft.zip" :errors="errors.zip" />
            <text-field id="country" label="Country" v-model="locationDraft.country" :errors="errors.country" />
            <text-field id="phone" label="Phone" v-model="locationDraft.phone" :errors="errors.phone" />
            <text-field id="email" label="Email" v-model="locationDraft.email" :errors="errors.email" />

            <div class="mt-4 flex">
                <div class="flex-1">
                    <button
                        class="btn btn-primary"
                        :class="{disabled: requestPending}"
                        :disabled="requestPending"
                        @click="onSaveClick"
                    >Save</button>
                    <button
                        class="btn btn-default"
                        :class="{disabled: requestPending}"
                        :disabled="requestPending"
                        @click="$emit('cancel')"
                    >Cancel</button>
                    <div class="spinner" :class="{'invisible': !requestPending}"></div>
                </div>
                <div>
                    <button
                        v-if="!locationDraft.isNew"
                        class="btn btn-danger"
                        :class="{disabled: requestPending}"
                        :disabled="requestPending"
                        @click="onRemoveClick"
                    >Remove</button>
                </div>
            </div>
        </div>
    </modal>
</template>

<script>
    import helpers from '../mixins/helpers'
    import Modal from './Modal'
    import TextField from './fields/TextField'

    export default {
        props: {
            locations: { default: () => [{}] },
            show: { default: false },
            editIndex: { default: null },
        },

        components: {
            Modal,
            TextField,
        },

        data() {
            return {
                errors: {},
                fields: ['title','address1','address2','city','state','zip','country','phone','email'],
                requestPending: false,
                transitionName: 'fade',
                locationDraft: {}
            }
        },

        methods: {
            onCancelClick() {
                this.$emit('cancel', {isNew: locationDraft.isNew})
            },
            onRemoveClick() {
                let locations = helpers.simpleClone(this.locations)

                locations.splice(this.editIndex, 1)

                this.saveLocations(
                    locations,
                    'Location removed',
                    'Unable to remove location'
                )
            },
            onSaveClick() {
                let location = helpers.simpleClone(this.locationDraft)
                let locations = helpers.simpleClone(this.locations)

                locations.splice(this.editIndex, 1, location)

                this.saveLocations(
                    locations,
                    'Location saved',
                    'Unable to save location'
                )
            },
            saveLocations(locations, successMessage, errorMessage) {
                this.requestPending = true

                this.$store.dispatch('patchPartnerProfile', {locations})
                    .then(() => {
                        this.requestPending = false
                        this.$root.displayNotice(successMessage)
                        this.$emit('done')
                    })
                    .catch(() => {
                        this.requestPending = false
                        this.$root.displayError(errorMessage)
                    })
            },
        },

        computed: {
            modalShouldShow() {
                return this.editIndex !== null
            }
        },

        watch: {
            editIndex() {
                let location = {}

                if (this.editIndex !== null) {
                    let location = this.locations[this.editIndex] || {}
                    this.locationDraft = helpers.simpleClone(location)
                }
            }
        }
    }
</script>

<template>
    <div>
        <div v-if="!isEditing" class="card mb-4">
            <div class="card-body">
                <div class="flex">
                    <ul class="flex-1 list-reset">
                        <li v-if="location.title"><strong>{{ location.title }}</strong></li>
                        <li v-if="location.addressLine1">{{ location.addressLine1 }}</li>
                        <li v-if="location.addressLine2">{{ location.addressLine2 }}</li>
                        <li v-if="cityStateZip">{{ cityStateZip }}</li>
                        <li v-if="location.phone">{{ location.phone }}</li>
                        <li v-if="location.email">{{ location.email }}</li>
                    </ul>
                    <div>
                        <button class="btn btn-secondary" @click="$emit('edit', index)"><i class="fa fa-pencil-alt"></i> Edit</button>
                    </div>
                </div>
            </div>
        </div>
        <modal :show="isEditing" transition="fade" modal-type="wide" >
            <div slot="body" class="p-4">
                <text-field id="title" label="Location Title" v-model="location.title" :errors="errors.title" placeholder="Main Office" />
                <text-field id="addressLine1" label="Address" v-model="location.addressLine1" :errors="errors.addressLine1" />
                <text-field id="addressLine2" v-model="location.addressLine2" :errors="errors.addressLine2" />
                <text-field id="city" label="City" v-model="location.city" :errors="errors.city" />
                <text-field id="state" label="State" v-model="location.state" :errors="errors.state" />
                <text-field id="zip" label="Zip" v-model="location.zip" :errors="errors.zip" />
                <text-field id="country" label="Country" v-model="location.country" :errors="errors.country" />
                <text-field id="phone" label="Phone" v-model="location.phone" :errors="errors.phone" />
                <text-field id="email" label="Email" v-model="location.email" :errors="errors.email" />

                <div class="mt-4 flex">
                    <div class="flex-1">
                        <button
                            class="btn btn-secondary"
                            :class="{disabled: requestPending}"
                            :disabled="requestPending"
                            @click="$emit('cancel', index)">Cancel</button>

                        <button
                            class="btn btn-primary"
                            :class="{disabled: requestPending}"
                            :disabled="requestPending"
                            @click="$emit('save')">Save</button>

                        <div class="spinner" :class="{'invisible': !requestPending}"></div>
                    </div>
                    <div>
                        <button
                            v-if="location.id !== 'new'"
                            class="btn btn-danger"
                            :class="{disabled: requestPending}"
                            :disabled="requestPending"
                            @click="$emit('delete', index)">Delete</button>
                    </div>
                </div>
            </div>
        </modal>
    </div>
</template>

<script>
    import Modal from '../components/Modal'
    import TextField from '../components/fields/TextField'

    export default {
        props: ['index', 'location', 'editIndex', 'requestPending'],

        components: {
            Modal,
            TextField,
        },

        data() {
            return {
                errors: {},
            }
        },

        computed: {
            cityStateZip() {
                let city = (this.location.businessCity || '')
                let state = (this.location.businessState || '')
                let country = (this.location.businessCountry || '')
                let comma = city.length && state.length ? ',' : ''

                return `${city}${comma} ${state} ${country}`.trim()
            },
            isEditing() {
                return this.editIndex === this.index
            }
        },

        mounted() {
            // go straight to the modal form after clicking
            // "Add New Location" button
            if (this.location.id === 'new') {
                this.$emit('edit', this.index)
            }
        }
    }
</script>

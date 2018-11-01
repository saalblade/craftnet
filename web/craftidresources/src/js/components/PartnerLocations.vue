<template>
    <div>
        <h4>Locations</h4>

        <p class="text-grey-darker">Location and sales contact information for potential clients.</p>

        <partner-location
            v-for="(location, index) in draftLocations"
            :location="location"
            :key="index"
            :index="index"
            :edit-index="editIndex"
            :request-pending="requestPending"
            @edit="onEdit"
            @cancel="onCancel"
        ></partner-location>

        <div class="pl-4">
            <button class="btn btn-secondary btn-sm" @click="onAddLocationClick"><i class="fa fa-plus"></i> Add a Location</button>
        </div>

    </div>
</template>

<script>
    import {mapState} from 'vuex'
    import helpers from '../mixins/helpers'
    import PartnerLocation from './PartnerLocation'

    export default {
        props: ['partner'],
        mixins: [helpers],

        components: {
            PartnerLocation,
        },

        data() {
            return {
                draftLocations: [],
                draftLocationProps: [
                    'id',
                    'title',
                    'addressLine1',
                    'addressLine2',
                    'city',
                    'state',
                    'zip',
                    'country',
                    'phone',
                    'email',
                ],
                editIndex: null,
                requestPending: false,
            }
        },

        methods: {
            onAddLocationClick() {
                this.draftLocations.push({
                    id: 'new',
                    title: '',
                    addressLine1: '',
                    addressLine2: '',
                    city: '',
                    state: '',
                    zip: '',
                    country: '',
                    phone: '',
                    email: '',
                })
            },

            cloneLocations() {
                let locations = []

                for (let i = 0; i < this.partner.locations.length; i++) {
                    const location = this.partner.locations[i]
                    locations.push(this.simpleClone(location, this.draftLocationProps))
                }

                this.draftLocations = locations
            },

            onCancel() {
                // reset
                this.cloneLocations()
                this.editIndex = null
            },

            onDelete(index) {
                this.editIndex = null
            },

            onEdit(index) {
                this.editIndex = index
            },
        },

        mounted() {
            this.cloneLocations()
        },

        watch: {
            partner() {
                this.cloneLocations()
            }
        }
    }
</script>

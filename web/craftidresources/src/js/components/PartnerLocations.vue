<template>
    <div class="card mb-4">
        <div class="card-body">
            <div class="flex">
                <div class="flex-1">
                    <h4>Locations</h4>
                </div>
                <div class="pl-4">
                    <button class="btn btn-secondary btn-sm" @click="onAddLocationClick"><i class="fa fa-plus"></i> Add a Location</button>
                </div>
            </div>

            <partner-location
                v-for="(location, key) in partner.locations"
                :location="location"
                :key="key"
                :index="key"
                @editClick="onLocationEditClick"
            ></partner-location>

            <partner-location-form
                :locations="draftLocations"
                :edit-index="editIndex"
                @cancel="onFormCancel"
                @done="onFormDone"
            ></partner-location-form>
        </div>
    </div>
</template>

<script>
    import {mapState} from 'vuex'
    import helpers from '../mixins/helpers'
    import PartnerLocation from './PartnerLocation'
    import PartnerLocationForm from './PartnerLocationForm'

    export default {
        components: {
            PartnerLocation,
            PartnerLocationForm
        },

        data() {
            return {
                draftLocations: null,
                editIndex: null,
            }
        },

        computed: {
            ...mapState({
                partner: state => state.partner.partnerProfile,
            }),
        },

        methods: {
            onAddLocationClick() {
                this.draftLocations.push({isNew: true})
                this.editIndex = this.draftLocations.length - 1
            },
            onFormCancel() {
                this.editIndex = null
            },
            onFormDone() {
                this.editIndex = null
            },
            onLocationEditClick(index) {
                this.editIndex = index
            },
            cloneLocations() {
                let locations = this.partner.locations || []
                this.draftLocations = helpers.simpleClone(locations)
            },
        },

        mounted() {
            this.cloneLocations()
        },

        watch: {
            partner() {
                console.warn('partner updated')
                this.cloneLocations()
            }
        }
    }
</script>

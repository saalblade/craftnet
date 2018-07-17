<template>
	<div>
        <div class="card mb-4">
            <div class="card-body">
                <div class="flex">
                    <div class="flex-1"><h4>Business Info</h4></div>
                    <div class="pl-4" v-if="!isEditing">
                        <button class="btn btn-secondary" @click="onEditClick"><i class="fa fa-pencil-alt"></i> Edit</button>
                    </div>
                </div>

                <div v-if="!isEditing">
                    <ul class="list-reset">
                        <li v-if="partner.businessName">
                            <strong>{{ partner.businessName }}</strong>
                        </li>
                        <li v-if="partner.primaryContactName">
                            {{ partner.primaryContactName }}
                        </li>
                        <li v-if="partner.primaryContactEmail">
                            {{ partner.primaryContactEmail }}
                        </li>
                        <li v-if="partner.primaryContactPhone">
                            {{ partner.primaryContactPhone }}
                        </li>
                        <li v-if="partner.businessSummary">
                            Business Summary:<br>{{ partner.businessSummary }}
                        </li>
                        <li v-if="partner.minimumBudget">
                            {{ partner.minimumBudget }}
                        </li>
                        <li v-if="partner.agencySize">
                            {{ partner.agencySize }}
                        </li>
                        <li v-if="partner.msaLink">
                            MSA Link:<br><a :href="partner.msaLink" target="_blank">{{ partner.msaLink }}</a>
                        </li>
                        <li v-if="capabilitiesJoined">
                            Capabilities: {{ capabilitiesJoined }}
                        </li>
                    </ul>
                </div>

                <div v-else>
                    <text-field id="businessName" label="Business Name" v-model="draft.businessName" :errors="errors.businessName" />
                    <text-field id="primaryContactName" label="Primary Contact Name" v-model="draft.primaryContactName" :errors="errors.primaryContactName" />
                    <text-field id="primaryContactEmail" label="Primary Contact Email" v-model="draft.primaryContactEmail" :errors="errors.primaryContactEmail" />
                    <text-field id="primaryContactPhone" label="Primary Contact Phone" v-model="draft.primaryContactPhone" :errors="errors.primaryContactPhone" />
                    <textarea-field id="businessSummary" label="Business Summary/Description (one paragraph)" v-model="draft.businessSummary" :errors="errors.businessSummary" />
                    <text-field id="minimumBudget" label="Minimum Budget" v-model="draft.minimumBudget" :errors="errors.minimumBudget" />
                    <select-field id="agencySize" label="Agency Size" v-model="draft.agencySize" :options="options.agency" :errors="errors.agencySize" />
                    <url-field id="msaLink" label="Link to MSA or Equivalent paperwork" v-model="draft.msaLink" :errors="errors.msaLink" />
                    <checkbox-set id="capabilities" label="Capabilities" v-model="draft.capabilities" :options="options.capabilities" :errors="errors.capabilities" />

                    <div class="pt-4">
                        <button
                            class="btn btn-secondary"
                            :class="{'disabled': requestPending}"
                            :disabled="requestPending"
                            @click="isEditing = false">Cancel</button>

                        <button
                            class="btn btn-primary"
                            :class="{'disabled': requestPending}"
                            :disabled="requestPending"
                            @click="onSubmit">Submit</button>

                        <div class="spinner" :class="{'invisible': !requestPending}"></div>
                    </div>
                </div>
            </div>
        </div>
	</div>
</template>

<script>
    import {mapState} from 'vuex'
    import CheckboxSet from '../components/fields/CheckboxSet'
    import SelectField from '../components/fields/SelectField'
    import TextareaField from '../components/fields/TextareaField'
    import TextField from '../components/fields/TextField'
    import UrlField from '../components/fields/UrlField'

    export default {

        data() {
            return {
                draft: {},
                defaultDraft: {
                    businessName: '',
                    primaryContactName: '',
                    primaryContactEmail: '',
                    primaryContactPhone: '',
                    businessSummary: '',
                    minimumBudget: '',
                    agencySize: '',
                    msaLink: '',
                    capabilities: []
                },
                errors: {},
                isEditing: false,
                options: {
                    agency: [
                        {label: "Boutique", value: "Boutique"},
                        {label: "Agency (10-50)", value: "Agency"},
                        {label: "Large Agency (50+)", value: "Large Agency"}
                    ],
                    capabilities: [
                        {label: 'Commerce', value: 'Commerce'},
                        {label: 'Full Service', value: 'Full Service'},
                        {label: 'Custom Development', value: 'Custom Development'},
                        {label: 'Contract Work', value: 'Contract Work'},
                    ]
                },
                requestPending: false
            }
        },

        components: {
            CheckboxSet,
            SelectField,
            TextareaField,
            TextField,
            UrlField
        },

        computed: {
            capabilitiesJoined() {
                return this.partner.capabilities.join(', ').trim()
            },
            ...mapState({
                partner: state => state.partner.partnerProfile,
            }),
        },

        methods: {
            onEditClick() {
                // Set the draft object
                for (let key in this.defaultDraft) {
                    this.draft[key] = this.partner[key] || this.defaultDraft[key]
                }

                this.isEditing = true
            },

            onSubmit() {
                this.requestPending = true

                this.$store.dispatch('patchPartnerProfile', this.draft)
                    .then(() => {
                        this.requestPending = false
                        this.isEditing = false
                    })
                    .catch(() => {
                        this.requestPending = false
                    })
            }
        }
    }
</script>

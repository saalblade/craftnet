<template>
	<div>
        <div class="card mb-4">
            <div class="card-body">
                <div class="flex">
                    <div class="flex-1"><h4>Profile</h4></div>
                    <div class="pl-4" v-if="!isEditing">
                        <button class="btn btn-secondary" @click="onEditClick"><i class="fa fa-pencil-alt"></i> Edit</button>
                    </div>
                </div>

                <!-- <pre>{{ partner }}</pre> -->

                <div v-if="!isEditing">
                    <ul class="info-list list-reset">
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
                        <li v-if="partner.region">
                            {{ partner.region }}
                        </li>
                        <li v-if="basicRequirementsList.length" class="mt-2">
                            Basic Requirements:
                            <ul class="text-sm text-grey-darker mt-2">
                                <li v-for="(value, index) in basicRequirementsList" :key="index">{{ value }}</li>
                            </ul>
                        </li>
                        <li v-if="partner.capabilities" class="mt-2 mb-2">
                            Capabilities:
                            <ul class="text-sm text-grey-darker mt-2">
                                <li v-for="(value, index) in partner.capabilities" :key="index">
                                    {{ value }}
                                </li>
                            </ul>
                        </li>
                        <li v-if="expertiseList.length" class="mt-2 mb-2">
                            Areas of Expertise:
                            <ul class="text-sm text-grey-darker mt-2">
                                <li v-for="(value, index) in expertiseList" :key="index">
                                    {{ value }}
                                </li>
                            </ul>
                        </li>
                        <li v-if="partner.agencySize" class="mt-2">
                            Agency Size: <span class="text-grey-darker">{{ agencySizeDisplay }}</span>
                        </li>
                        <li v-if="partner.fullBio" class="mt-4">
                            Full Bio:
                            <pre class="text-grey-darker text-sm p-2 whitespace-pre-wrap">{{ partner.fullBio }}</pre>
                        </li>
                        <li v-if="partner.shortBio" class="mt-4">
                            Short Bio:
                            <pre class="text-grey-darker text-sm p-2 whitespace-pre-wrap">{{ partner.shortBio }}</pre>
                        </li>
                    </ul>
                </div>

                <div v-else>
                    <!-- <text-field id="businessName" label="Business Name" v-model="draft.businessName" :errors="errors.businessName" />
                    <text-field id="primaryContactName" label="Primary Contact Name" v-model="draft.primaryContactName" :errors="errors.primaryContactName" />
                    <text-field id="primaryContactEmail" label="Primary Contact Email" v-model="draft.primaryContactEmail" :errors="errors.primaryContactEmail" />
                    <text-field id="primaryContactPhone" label="Primary Contact Phone" v-model="draft.primaryContactPhone" :errors="errors.primaryContactPhone" />
                    <textarea-field id="businessSummary" label="Business Summary/Description (one paragraph)" v-model="draft.businessSummary" :errors="errors.businessSummary" />
                    <select-field id="agencySize" label="Agency Size" v-model="draft.agencySize" :options="options.agency" :errors="errors.agencySize" />
                    <url-field id="msaLink" label="Link to MSA or Equivalent paperwork" v-model="draft.msaLink" :errors="errors.msaLink" />
                    <checkbox-set id="capabilities" label="Capabilities" v-model="draft.capabilities" :options="options.capabilities" :errors="errors.capabilities" /> -->

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
        props: ['partner'],
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
                    agencySize: [
                        {label: "1-2", value: "XS"},
                        {label: "3-9", value: "S"},
                        {label: "10-29", value: "M"},
                        {label: "30+", value: "L"}
                    ],
                    capabilities: [
                        {label: 'Commerce', value: 'Commerce'},
                        {label: 'Full Service', value: 'Full Service'},
                        {label: 'Custom Development', value: 'Custom Development'},
                        {label: 'Contract Work', value: 'Contract Work'}
                    ],
                    region: [
                        {label: 'Asia Pacific', value: 'Asia Pacific'},
                        {label: 'Europe', value: 'Europe'},
                        {label: 'North America', value: 'North America'},
                        {label: 'South America', value: 'South America'}
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
            agencySizeDisplay() {
                for (let i = 0; i < this.options.agencySize.length; i++) {
                    const item = this.options.agencySize[i]
                    if (item.value === this.partner.agencySize) {
                        return item.label
                    }
                }

                return this.partner.agencySize
            },
            basicRequirementsList() {
                let list = []

                if (this.partner.isRegisteredBusiness) {
                    list.push('Is a registered business')
                }

                if (this.partner.hasFullTimeDev) {
                    list.push('Has a full-time Craft developer')
                }

                return list
            },
            expertiseList() {
                let list = this.partner.expertise.trim().split("\n")
                return list
            }
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
        },
    }
</script>

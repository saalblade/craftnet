<template>
	<div>
        <p class="text-grey-darker">Basic business information and adminstrative contact information for Pixel &amp; Tonic to reach you.</p>
        <div class="card mb-4">
            <div class="card-body">
                <div class="text-right" v-if="!isEditing">
                    <button class="btn btn-secondary" @click="onEditClick"><i class="fa fa-pencil-alt"></i> Edit</button>
                </div>

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
                        <li v-if="expertiseList.length" class="mt-2 mb-2 pt-2">
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
                    <text-field id="businessName" label="Business Name" instructions="The URL slug will be generated from this title." v-model="draft.businessName" :errors="errors.businessName" />
                    <text-field id="primaryContactName" label="Primary Contact Name" v-model="draft.primaryContactName" :errors="errors.primaryContactName" />
                    <text-field id="primaryContactEmail" label="Primary Contact Email" v-model="draft.primaryContactEmail" :errors="errors.primaryContactEmail" />
                    <text-field id="primaryContactPhone" label="Primary Contact Phone" v-model="draft.primaryContactPhone" :errors="errors.primaryContactPhone" />
                    <select-field id="region" label="Region" v-model="draft.region" :options="options.region" :errors="errors.region" />
                    <checkbox-field id="isRegisteredBusiness" label="This is a registered business" instructions="Required for consideration." v-model="draft.isRegisteredBusiness" :checked-value="1" :errors="errors.isRegisteredBusiness" />
                    <checkbox-field id="hasFullTimeDev" label="Buisiness has at least one full-time Craft developer" instructions="Required for consideration." v-model="draft.hasFullTimeDev" :checked-value="1" :errors="errors.hasFullTimeDev" />
                    <checkbox-set id="capabilities" label="Capabilities" v-model="draft.capabilities" :options="options.capabilities" :errors="errors.capabilities" />
                    <textarea-field id="expertise" label="Areas of Expertise" instructions="Tags for relevant expertise (e.g. SEO), each on a new line" v-model="draft.expertise" />
                    <select-field id="agencySize" label="Agency Size" v-model="draft.agencySize" :options="options.agencySize" :errors="errors.agencySize" />
                    <textarea-field id="fullBio" label="Full Bio" instructions="Markdown OK. Shown on your detail page." v-model="draft.fullBio" :errors="errors.fullBio" />
                    <textarea-field id="shortBio" label="Short Bio" instructions="Max 255 characters. Shown on your listing card." v-model="draft.shortBio" :errors="errors.shortBio" />

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
                            @click="onSubmit">Save</button>

                        <div class="spinner" :class="{'invisible': !requestPending}"></div>
                    </div>
                </div>
            </div>
        </div>
	</div>
</template>

<script>
    import CheckboxField from '../components/fields/CheckboxField'
    import CheckboxSet from '../components/fields/CheckboxSet'
    import SelectField from '../components/fields/SelectField'
    import TextareaField from '../components/fields/TextareaField'
    import TextField from '../components/fields/TextField'
    import UrlField from '../components/fields/UrlField'
    import helpers from '../mixins/helpers'

    export default {
        props: ['partner'],

        mixins: [helpers],

        components: {
            CheckboxField,
            CheckboxSet,
            SelectField,
            TextareaField,
            TextField,
            UrlField
        },

        data() {
            return {
                draft: {},
                draftProps: [
                    'id',
                    'businessName',
                    'primaryContactName',
                    'primaryContactEmail',
                    'primaryContactPhone',
                    'region',
                    'isRegisteredBusiness',
                    'hasFullTimeDev',
                    'capabilities',
                    'expertise',
                    'agencySize',
                    'fullBio',
                    'shortBio',
                ],
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
                this.draft = this.simpleClone(this.partner, this.draftProps)
                this.isEditing = true
            },

            onSubmit() {
                this.errors = {}
                this.errorMessage = ''
                this.requestPending = true

                this.$store.dispatch('patchPartner', this.draft)
                    .then(response => {
                        this.requestPending = false

                        if (!response.data.success) {
                            this.errors = response.data.errors
                            this.$root.displayError('Validation errors')
                        } else {
                            this.$root.displayNotice('Updated')
                            this.isEditing = false
                        }
                    })
                    .catch(errorMessage => {
                        this.$root.displayError(errorMessage)
                        this.requestPending = false
                    })
            }
        },
    }
</script>

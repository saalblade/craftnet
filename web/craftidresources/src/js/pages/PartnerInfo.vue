<template>
	<div>
		<h2>Partner Form</h2>

        <div class="card mb-4">
            <div class="card-body">
                <h4>General Info</h4>
                <text-field id="businessName" label="Business Name" v-model="partnerDraft.businessName" :errors="errors.businessName" />
                <text-field id="primaryContactName" label="Primary Contact Name" v-model="partnerDraft.primaryContactName" :errors="errors.primaryContactName" />
                <text-field id="primaryContactEmail" label="Primary Contact Email" v-model="partnerDraft.primaryContactEmail" :errors="errors.primaryContactEmail" />
                <text-field id="primaryContactPhone" label="Primary Contact Phone" v-model="partnerDraft.primaryContactPhone" :errors="errors.primaryContactPhone" />
                <textarea-field id="businessSummary" label="Business Summary/Description (one paragraph)" v-model="partnerDraft.businessSummary" :errors="errors.businessSummary" />
                <text-field id="minimumBudget" label="Minimum Budget" v-model="partnerDraft.minimumBudget" :errors="errors.minimumBudget" />
                <select-field id="agencySize" label="Agency Size" v-model="partnerDraft.agencySize" :options="formDefaults.agencyOptions" :errors="errors.agencySize" />
                <url-field id="msaLink" label="Link to MSA or Equivalent paperwork" v-model="partnerDraft.msaLink" :errors="errors.msaLink" />
            </div>
        </div>
	</div>
</template>

<script>
    import {mapState} from 'vuex'
    import SelectField from '../components/fields/SelectField'
    import TextareaField from '../components/fields/TextareaField'
    import TextField from '../components/fields/TextField'
    import UrlField from '../components/fields/UrlField'

    export default {

        data() {
            return {
                errors: {},
                partnerDraft: {},
                formDefaults: {
                    agencyOptions: [
                        {label: "Boutique", value: "Boutique"},
                        {label: "Agency (10-50)", value: "Agency"},
                        {label: "Large Agency (50+)", value: "Large Agency"}
                    ]
                }
            }
        },

        components: {
            SelectField,
            TextareaField,
            TextField,
            UrlField,
        },

        computed: {
            ...mapState({
                currentUser: state => state.account.currentUser,
            }),
        },

        mounted() {
            this.partnerDraft = {
                agencySize: "Boutique"
            }
        }
    }
</script>

<template>
	<div>
		<div class="card mb-3">
			<div class="card-body">
				<h4 class="mb-4">License Details</h4>
				<template v-if="license">
					<div class="md:flex -mx-4">
						<div class="md:w-1/2 px-4">
							<dl>
								<dt>Edition</dt>
								<dd>{{ license.edition }}</dd>

								<dt>Domain Name</dt>
								<dd>
									<template v-if="!editingDomain">
										<p>{{ license.domain }}</p>

										<div class="buttons">
											<button @click="editingDomain = true" type="button" class="btn btn-secondary btn-sm">
												<i class="fas fa-pencil-alt"></i>
												Change Domain
											</button>
										</div>
									</template>

									<form v-if="editingDomain" @submit.prevent="saveDomain()">
										<text-field id="domain" v-model="licenseDraft.domain"></text-field>

										<input type="submit" class="btn btn-primary" value="Save" />
										<input @click="cancelEditDomain()" type="button" class="btn btn-secondary" value="Cancel" />
									</form>
								</dd>

								<dt>Notes</dt>
								<dd>
									<template v-if="!editingNotes">
										<p>{{ license.notes }}</p>

										<div class="buttons">
											<button @click="editingNotes = true" type="button" class="btn btn-secondary btn-sm">
												<i class="fas fa-pencil-alt"></i>
												Edit
											</button>
										</div>
									</template>

									<form v-if="editingNotes" @submit.prevent="saveNotes()">
										<textarea-field id="notes" v-model="licenseDraft.notes"></textarea-field>

										<input type="submit" class="btn btn-primary" value="Save" />
										<input @click="cancelEditNotes()" type="button" class="btn btn-secondary" value="Cancel" />
									</form>
								</dd>
							</dl>
						</div>
						<div class="md:w-1/2 px-4">
							<dl>
								<dt>Email</dt>
								<dd>{{ license.email }}</dd>

								<template v-if="enableCommercialFeatures">
									<dt>Update Period</dt>
									<dd>2017/05/11 to 2018/05/11</dd>

									<dt>Auto Renew</dt>
									<dd>
										<lightswitch-input @input="saveAutoRenew()" v-model="licenseDraft.autoRenew"></lightswitch-input>
									</dd>
								</template>

								<dt>Created</dt>
								<dd>{{ license.dateCreated }}</dd>

								<dt>License Key</dt>
								<dd><a href="#" class="btn btn-secondary">Download License Key</a></dd>
							</dl>
						</div>
					</div>
				</template>
			</div>
		</div>

	</div>
</template>

<script>
    import { mapGetters } from 'vuex'
    import TextareaField from '../components/fields/TextareaField'
    import TextField from '../components/fields/TextField'
    import LightswitchInput from '../components/inputs/LightswitchInput'

    export default {

        props: ['license', 'type'],

        data() {
            return {
                errors: {},
                licenseDraft: {},
				editingDomain: false,
				editingNotes: false,
            }
        },

        components: {
            TextareaField,
            TextField,
            LightswitchInput,
        },

        computed: {

            ...mapGetters({
                enableCommercialFeatures: 'enableCommercialFeatures',
            }),

			canSave() {
                if(this.license.domain != this.licenseDraft.domain) {
                    return true;
				}

                if(this.license.notes != this.licenseDraft.notes) {
                    return true;
				}

				return false;
			}

        },

        methods: {

            /**
			 * Save auto renew.
             */
            saveAutoRenew() {
                this.$store.dispatch('saveCmsLicense', {
                    id: this.license.id,
                    type: this.type,
                    autoRenew: (this.licenseDraft.autoRenew ? 1 : 0),
                }).then((data) => {
                    if(this.licenseDraft.autoRenew) {
                        this.$root.displayNotice('Auto renew enabled.');
                    } else {
                        this.$root.displayNotice('Auto renew disabled.');
                    }

                }).catch((data) => {
                    this.$root.displayError('Couldn’t save license.');
                    this.errors = data.errors;
                });
            },

            /**
			 * Save domain.
             */
			saveDomain() {
                this.saveCmsLicense(() => {
                    this.editingDomain = false;
				});
			},

            /**
			 * Save notes.
             */
			saveNotes() {
                this.saveCmsLicense(() => {
                    this.editingNotes = false;
                });
			},

            /**
			 * Save CMS license.
			 *
             * @param cb
             */
            saveCmsLicense(cb) {
                this.$store.dispatch('saveCmsLicense', {
                    key: this.license.key,
                    domain: this.licenseDraft.domain,
                    notes: this.licenseDraft.notes,
                }).then((data) => {
                    cb();
                    this.$root.displayNotice('License saved.');
                }).catch((data) => {
                    this.$root.displayError('Couldn’t save license.');
                    this.errors = data.errors;
                });
            },

            /**
			 * Cancel edit domain.
             */
            cancelEditDomain() {
                this.licenseDraft.domain = this.license.domain;
                this.editingDomain = false;
            },

            /**
			 * Cancel edit notes.
             */
            cancelEditNotes() {
                this.licenseDraft.notes = this.license.notes;
                this.editingNotes = false;
            }

        },

        mounted() {
            this.licenseDraft = {
                autoRenew: (this.license.autoRenew == 1 ? true : false),
                domain: this.license.domain,
                notes: this.license.notes,
            };
        }

    }
</script>

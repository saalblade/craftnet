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

								<dt>License Key</dt>
								<dd><code>{{ license.key.slice(0, 10) }}…</code> <a href="#license-key">View license key</a></dd>

								<dt>Domain Name</dt>
								<dd>
									<template v-if="!domainEditing">
										<p>{{ license.domain }}</p>

										<div class="buttons">
											<button @click="domainEditing = true" type="button" class="btn btn-secondary btn-sm">
												<i class="fas fa-pencil-alt"></i>
												Change Domain
											</button>
										</div>
									</template>

									<form v-if="domainEditing" @submit.prevent="saveDomain()">
										<text-field id="domain" v-model="licenseDraft.domain" @input="domainChange"></text-field>
										<input type="submit" class="btn btn-primary" value="Save" :class="{disabled: !domainValidates}" :disabled="!domainValidates" />
										<input @click="cancelEditDomain()" type="button" class="btn btn-secondary" value="Cancel" />
										<div class="spinner" v-if="domainLoading"></div>
									</form>
								</dd>
							</dl>
						</div>
						<div class="md:w-1/2 px-4">
							<dl>
								<dt>Email</dt>
								<dd>{{ license.email }}</dd>

								<dt>Created</dt>
								<dd>{{ license.dateCreated.date|moment("L") }}</dd>

								<dt>Notes</dt>
								<dd>
									<template v-if="!notesEditing">
										<p>{{ license.notes }}</p>

										<div class="buttons">
											<button @click="notesEditing = true" type="button" class="btn btn-secondary btn-sm">
												<i class="fas fa-pencil-alt"></i>
												Edit
											</button>
										</div>
									</template>

									<form v-if="notesEditing" @submit.prevent="saveNotes()">
										<textarea-field id="notes" v-model="licenseDraft.notes" @input="notesChange"></textarea-field>
										<input type="submit" class="btn btn-primary" value="Save" :class="{disabled: !notesValidates}" :disabled="!notesValidates" />
										<input @click="cancelEditNotes()" type="button" class="btn btn-secondary" value="Cancel" />
										<div class="spinner" v-if="notesLoading"></div>
									</form>
								</dd>
							</dl>
						</div>
					</div>
				</template>
			</div>
		</div>

		<div id="license-key" class="card mb-3">
			<div class="card-body">
				<h4>License Key</h4>
				<div v-if="license">
					<textarea-field id="notes" class="mono" v-model="formattedLicense" :disabled="true" rows="6"></textarea-field>
					<a :href="downloadLicenseUrl" class="btn btn-secondary" target="_blank">Download License Key</a>
				</div>
			</div>
		</div>

		<div v-if="license.expirable && license.expiresOn" class="card mb-3">
			<div class="card-body">
				<h4>Auto-Renew</h4>

				<template v-if="licenseDraft.autoRenew">
					<p>Auto-renew is <strong>enabled</strong> for this license.</p>
				</template>

				<template v-else>
					<p>Auto-renew is <strong>disabled</strong> for this license.</p>
				</template>

				<lightswitch-field
						id="auto-renew"
						@change="saveAutoRenew"
						:checked.sync="licenseDraft.autoRenew"
				/>
			</div>
		</div>

		<div class="card mb-3">
			<div class="card-body">
				<h4>Updates</h4>
				<license-update-message :license="license" />

				<button v-if="!renewLicenses" @click="renewLicenses = true" class="btn btn-primary">Renew your license now</button>

				<template v-else>
					<hr>
					<renew-licenses-form :license="license" @cancel="renewLicenses = false" />
				</template>
			</div>
		</div>
	</div>
</template>

<script>
    import TextareaField from '../components/fields/TextareaField'
    import TextField from '../components/fields/TextField'
    import LightswitchField from '../components/fields/LightswitchField'
    import LicenseUpdateMessage from '../components/LicenseUpdateMessage'
    import RenewLicensesForm from '../components/RenewLicensesForm'

    export default {

        props: ['license'],

        data() {
            return {
				errors: {},
				licenseDraft: {},
				domainEditing: false,
				domainLoading: false,
				domainValidates: false,
				notesEditing: false,
				notesLoading: false,
				notesValidates: false,
				renewLicenses: false,
            }
        },

        components: {
            TextareaField,
            TextField,
            LightswitchField,
            LicenseUpdateMessage,
            RenewLicensesForm,
        },

        computed: {

            canSave() {
                if (this.license.domain != this.licenseDraft.domain) {
                    return true;
                }

                if (this.license.notes != this.licenseDraft.notes) {
                    return true;
                }

                return false;
            },

			formattedLicense() {
                let value = this.license.key;
                let formattedValue = this.$options.filters.formatCmsLicense(value);
                return formattedValue;
			},

			downloadLicenseUrl() {
                return Craft.actionUrl + '/craftnet/id/cms-licenses/download&id=' + this.license.id;
			},

        },

        methods: {

            /**
             * Save auto renew.
             */
            saveAutoRenew() {
                this.$store.dispatch('saveCmsLicense', {
                    key: this.license.key,
                    autoRenew: (this.licenseDraft.autoRenew ? 1 : 0),
                }).then(response => {
                    if (this.licenseDraft.autoRenew) {
                        this.$root.displayNotice('Auto renew enabled.')
                    } else {
                        this.$root.displayNotice('Auto renew disabled.')
                    }
                }).catch(response => {
                    this.$root.displayError('Couldn’t save license.')
                    this.errors = response.data.errors
                })
            },

            /**
             * Save domain.
             */
            saveDomain() {
                this.domainLoading = true;
				const oldDomain = this.licenseDraft.domain;

                this.saveCmsLicense(response => {
                    const newDomain = response.data.license.domain

                    if (oldDomain && oldDomain !== newDomain) {
                        this.licenseDraft.domain = newDomain

						if(!newDomain) {
                            this.$root.displayNotice(oldDomain + ' is not a public domain.');
						} else {
                            this.$root.displayNotice('Domain changed to ' + newDomain + '.')
                        }
					} else {
                        this.$root.displayNotice('Domain saved.');
					}

                    this.domainLoading = false;
                    this.domainEditing = false;
                }, () => {
                    this.domainLoading = false;
                });
            },

            /**
             * Save notes.
             */
            saveNotes() {
                this.notesLoading = true;

                this.saveCmsLicense(() => {
                    this.notesLoading = false;
                    this.notesEditing = false;
                    this.$root.displayNotice('Notes saved.');
                }, () => {
                    this.notesLoading = false;
				});
            },

            /**
             * Save CMS license.
             *
             * @param cb
             * @param cbError
             */
            saveCmsLicense(cb, cbError) {
                this.$store.dispatch('saveCmsLicense', {
                    key: this.license.key,
                    domain: this.licenseDraft.domain,
                    notes: this.licenseDraft.notes,
                }).then(response => {
                    cb(response);
                }).catch(response => {
                    cbError();
                    const errorMessage = response.data && response.data.error ? response.data.error : 'Couldn’t save license.'
                    this.$root.displayError(errorMessage)
                });
            },

            /**
             * Cancel edit domain.
             */
            cancelEditDomain() {
                this.licenseDraft.domain = this.license.domain;
                this.domainEditing = false;
                this.domainValidates = false;
            },

            /**
             * Cancel edit notes.
             */
            cancelEditNotes() {
                this.licenseDraft.notes = this.license.notes;
                this.notesEditing = false;
                this.notesValidates = false;
            },

            /**
             * Notes change.
             */
            notesChange() {
                this.notesValidates = false;
                if(this.licenseDraft.notes !== this.license.notes) {
                    this.notesValidates = true;
                }
			},

            /**
             * Domain changes.
             */
            domainChange() {
                this.domainValidates = false;
                if(this.licenseDraft.domain !== this.license.domain) {
                    this.domainValidates = true;
                }
			},

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

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
                                <dd>{{ license.edition|capitalize }}</dd>

                                <dt>License Key</dt>
                                <dd>
                                    <code>{{ license.key.slice(0, 10) }}…</code>
                                    <a href="#copy" class="ml-2" @click="copyLicense">Copy</a>
                                    <a :href="downloadLicenseUrl" class="ml-2" target="_blank">Download</a>
                                </dd>

                                <dt>Domain Name</dt>
                                <dd>
                                    <template v-if="!domainEditing">
                                        <p>{{ license.domain }}</p>

                                        <div class="buttons">
                                            <button @click="domainEditing = true" type="button" class="btn btn-secondary btn-sm">
                                                <font-awesome-icon icon="pencil-alt" />
                                                Change Domain
                                            </button>
                                        </div>
                                    </template>

                                    <form v-if="domainEditing" @submit.prevent="saveDomain()">
                                        <text-field id="domain" v-model="licenseDraft.domain" @input="domainChange"></text-field>
                                        <input type="submit" class="btn btn-primary" value="Save" :class="{disabled: !domainValidates}" :disabled="!domainValidates" />
                                        <input @click="cancelEditDomain()" type="button" class="btn btn-secondary" value="Cancel" />
                                        <spinner v-if="domainLoading"></spinner>
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

                                <dt>Updates Until</dt>
                                <dd>
                                    <template v-if="license.expirable && license.expiresOn">
                                        {{ license.expiresOn.date|moment("L") }}
                                    </template>
                                    <template v-else>
                                        Forever
                                    </template>
                                </dd>

                                <dt>Notes</dt>
                                <dd>
                                    <template v-if="!notesEditing">
                                        <p>{{ license.notes }}</p>

                                        <div class="buttons">
                                            <button @click="notesEditing = true" type="button" class="btn btn-secondary btn-sm">
                                                <font-awesome-icon icon="pencil-alt" />
                                                Edit
                                            </button>
                                        </div>
                                    </template>

                                    <form v-if="notesEditing" @submit.prevent="saveNotes()">
                                        <textarea-field id="notes" v-model="licenseDraft.notes" @input="notesChange"></textarea-field>
                                        <input type="submit" class="btn btn-primary" value="Save" :class="{disabled: !notesValidates}" :disabled="!notesValidates" />
                                        <input @click="cancelEditNotes()" type="button" class="btn btn-secondary" value="Cancel" />
                                        <spinner v-if="notesLoading"></spinner>
                                    </form>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </template>
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

                <button @click="showRenewLicensesModal('extend-updates')" class="btn btn-secondary">Renew your license…</button>
            </div>
        </div>
    </div>
</template>

<script>
    import {mapActions} from 'vuex'
    import LicenseUpdateMessage from './LicenseUpdateMessage'
    import Spinner from '../Spinner'

    export default {

        props: ['license'],

        components: {
            Spinner,
        },

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
            LicenseUpdateMessage,
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

            ...mapActions({
                showRenewLicensesModal: 'app/showRenewLicensesModal',
            }),

            /**
             * Save auto renew.
             */
            saveAutoRenew() {
                this.$store.dispatch('licenses/saveCmsLicense', {
                    key: this.license.key,
                    autoRenew: (this.licenseDraft.autoRenew ? 1 : 0),
                }).then(response => {
                    if (this.licenseDraft.autoRenew) {
                        this.$store.dispatch('app/displayNotice', 'Auto renew enabled.')
                    } else {
                        this.$store.dispatch('app/displayNotice', 'Auto renew disabled.')
                    }
                }).catch(response => {
                    this.$store.dispatch('app/displayError', 'Couldn’t save license.')
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
                            this.$store.dispatch('app/displayNotice', oldDomain + ' is not a public domain.');
                        } else {
                            this.$store.dispatch('app/displayNotice', 'Domain changed to ' + newDomain + '.')
                        }
                    } else {
                        this.$store.dispatch('app/displayNotice', 'Domain saved.');
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
                    this.$store.dispatch('app/displayNotice', 'Notes saved.');
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
                this.$store.dispatch('licenses/saveCmsLicense', {
                    key: this.license.key,
                    domain: this.licenseDraft.domain,
                    notes: this.licenseDraft.notes,
                }).then(response => {
                    cb(response);
                }).catch(response => {
                    cbError();
                    const errorMessage = response.data && response.data.error ? response.data.error : 'Couldn’t save license.'
                    this.$store.dispatch('app/displayError', errorMessage)
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

            /**
             * Copy license
             */
            copyLicense() {
                let $temp = document.createElement('input');
                document.body.appendChild($temp);
                $temp.value = this.license.key;
                $temp.select();
                document.execCommand("copy");
                $temp.remove();

                this.$store.dispatch('app/displayNotice', 'License key copied.');
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

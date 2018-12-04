<template>
    <div>
        <div class="card mb-3">
            <div class="card-body">
                <h4 class="mb-4">License Details</h4>
                <template v-if="license">
                    <div class="md:flex -mx-4">
                        <div class="md:w-1/2 px-4">
                            <dl>
                                <template v-if="license.plugin">
                                    <dt>Plugin</dt>
                                    <dd>{{ license.plugin.name }}</dd>
                                </template>

                                <dt>License Key</dt>
                                <dd><code>{{ license.key|formatPluginLicense }}</code></dd>

                                <dt>Craft License</dt>
                                <dd>
                                    <template v-if="license.cmsLicense">
                                        <p>
                                            <code>
                                                <router-link v-if="license.cmsLicense.key" :to="'/licenses/cms/'+license.cmsLicenseId">{{ license.cmsLicense.key.substr(0, 10) }}</router-link>
                                                <template v-else>{{ license.cmsLicense.shortKey }}</template>
                                            </code>
                                            <span v-if="license.cmsLicense.edition" class="text-secondary">(Craft {{ license.cmsLicense.edition }})</span>
                                        </p>
                                        <div class="buttons">
                                            <button @click="detachCmsLicense()" type="button" class="btn btn-secondary btn-sm">
                                                Detach from this Craft license
                                            </button>
                                            <div class="spinner" v-if="detaching"></div>
                                        </div>
                                    </template>
                                    <template v-else>
                                        <span class="text-secondary">Not attached to a CMS license.</span>
                                        <a v-if="originalCmsLicenseId" @click.prevent="reattachCmsLicense()" href="#">Undo</a>
                                    </template>
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
                                                <font-awesome-icon icon="pencil-alt" />
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
                <license-update-message :license="license"></license-update-message>

                <h5>Renew License</h5>

                <select-field v-model="renew" :options="renewOptions" />

                <table class="table mb-2">
                    <thead>
                    <tr>
                        <th>Item</th>
                        <th>Renewal Date</th>
                        <th>New Renewal Date</th>
                        <th>Renewal Price</th>
                        <th>Subtotal</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td>{{ license.plugin.name }}</td>
                        <td>{{ license.expiresOn.date|moment('L') }}</td>
                        <td>{{ newExpiresOn|moment('L') }}</td>
                        <td>{{ license.edition.renewalPrice|currency }} <span class="text-grey-dark">&times;</span> {{ Math.round(newExpiresOn.diff(license.expiresOn.date, 'years', true) * 100) / 100 }} year(s)</td>
                        <td>{{ newExpiresOn.diff(license.expiresOn.date, 'years', true) * license.edition.renewalPrice|currency }}</td>
                    </tr>
                    </tbody>
                </table>

                <input type="button" class="btn btn-primary" @click="addToCart()" value="Add to cart" />
            </div>
        </div>
    </div>
</template>

<script>
    import LicenseUpdateMessage from './LicenseUpdateMessage'

    export default {

        props: ['license', 'type'],

        data() {
            return {
                errors: {},
                licenseDraft: {},
                originalCmsLicenseId: this.license.cmsLicenseId,
                originalCmsLicense: this.license.cmsLicense,
                detaching: false,
                reattaching: false,
                notesEditing: false,
                notesLoading: false,
                notesValidates: false,
                renew: 1,
            }
        },

        components: {
            LicenseUpdateMessage,
        },

        computed: {

            renewOptions() {
                let options = [];

                for (let i = 1; i <= 5; i++) {
                    const date = this.$moment(this.license.expiresOn.date).add(i, 'year')
                    const formattedDate = this.$moment(date).format('L')
                    const label = "Extend updates until " + formattedDate

                    options.push({
                        label: label,
                        value: i,
                    })
                }

                return options;
            },

            newExpiresOn() {
                const expiresOn = this.$moment(this.license.expiresOn.date)
                return expiresOn.add(this.renew, 'years')
            },
        },

        methods: {

            /**
             * Detach the Craft license.
             */
            detachCmsLicense() {
                this.detaching = true;
                this.licenseDraft.cmsLicenseId = null;
                this.licenseDraft.cmsLicense = null;

                this.savePluginLicense(() => {
                    this.detaching = false;
                    this.$store.dispatch('licenses/getCmsLicenses')
                    this.$store.dispatch('licenses/getPluginLicenses')
                }, () => {
                    this.detaching = false;
                });
            },

            /**
             * Reattach the Craft license.
             */
            reattachCmsLicense() {
                this.reattaching = true;
                this.licenseDraft.cmsLicenseId = this.originalCmsLicenseId;
                this.licenseDraft.cmsLicense = this.originalCmsLicense;

                this.savePluginLicense(() => {
                    this.reattaching = false;
                }, () => {
                    this.reattaching = false;
                });
            },

            /**
             * Can save
             */
            canSave() {
                if (this.license.notes !== this.licenseDraft.notes) {
                    return true;
                }

                return false;
            },

            /**
             * Save notes.
             */
            saveNotes() {
                this.notesLoading = true;

                this.savePluginLicense(() => {
                    this.notesLoading = false;
                    this.notesEditing = false;
                }, () => {
                    this.notesLoading = false;
                });
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
             * Save plugin license.
             *
             * @param cb
             * @param cbError
             */
            savePluginLicense(cb, cbError) {
                this.$store.dispatch('licenses/savePluginLicense', {
                    pluginHandle: this.license.plugin.handle,
                    key: this.license.key,
                    cmsLicenseId: this.licenseDraft.cmsLicenseId,
                    cmsLicense: this.licenseDraft.cmsLicense,
                    notes: this.licenseDraft.notes,
                }).then(data => {
                    cb();
                    this.$store.dispatch('app/displayNotice', 'License saved.');
                }).catch(response => {
                    cbError();
                    const errorMessage = response.data && response.data.error ? response.data.error : 'Couldn’t save license.'
                    this.$store.dispatch('app/displayError', errorMessage)
                });
            },

            /**
             * Save auto renew
             */
            saveAutoRenew() {
                this.$store.dispatch('licenses/savePluginLicense', {
                    pluginHandle: this.license.plugin.handle,
                    key: this.license.key,
                    autoRenew: (this.licenseDraft.autoRenew ? 1 : 0),
                }).then((data) => {
                    if (this.licenseDraft.autoRenew) {
                        this.$store.dispatch('app/displayNotice', 'Auto renew enabled.');
                    } else {
                        this.$store.dispatch('app/displayNotice', 'Auto renew disabled.');
                    }

                    this.$store.dispatch('licenses/getCmsLicenses');
                }).catch((data) => {
                    this.$store.dispatch('app/displayError', 'Couldn’t save license.');
                    this.errors = data.errors;
                });
            },

            addToCart() {
                const item = {
                    type: 'renewal',
                    pluginLicenses: [
                        this.license.key,
                    ],
                    lineItem: {
                        total: this.newExpiresOn.diff(this.license.expiresOn.date, 'years', true) * this.license.edition.renewalPrice
                    }
                }

                this.$store.dispatch('cart/addToCartMock', {item})
                    .then(response => {
                        this.$router.push({path: '/cart'})
                    })
            }
        },

        mounted() {
            this.licenseDraft = {
                cmsLicenseId: this.license.cmsLicenseId,
                cmsLicense: this.license.cmsLicense,
                autoRenew: (this.license.autoRenew == 1 ? true : false),
                notes: this.license.notes,
            };
        }

    }
</script>

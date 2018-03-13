<template>
    <div class="card mb-3">
        <div class="card-header">License Details</div>
        <div class="card-body">
            <template v-if="license">

                <div class="flex">
                    <div class="w-1/2">

                        <dl>
                            <dt>License ID</dt>
                            <dd><template v-if="type == 'plugin'">PLU</template><template v-else-if="type == 'cms'">CMS</template>000{{ license.id }}</dd>

                            <template v-if="type === 'cms'">
                                <dt>Edition</dt>
                                <dd>{{ license.edition }}</dd>

                                <dt>Domain</dt>
                                <dd>{{ license.domain }}</dd>
                            </template>

                            <template v-if="type === 'plugin'">
                                <dt>Plugin</dt>
                                <dd>{{ license.pluginId }}</dd>
                            </template>
                        </dl>
                    </div>
                    <div class="w-1/2">
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
                        </dl>
                    </div>
                </div>

                <hr>

                <form @submit.prevent="saveNotes()">
                    <textarea-field id="notes" label="Notes" v-model="licenseDraft.notes"></textarea-field>

                    <template v-if="license.notes != licenseDraft.notes">
                        <input type="submit" class="btn btn-primary" value="Save" />
                        <input @click="cancel()" type="button" class="btn btn-secondary" value="Cancel" />
                    </template>
                </form>

            </template>

        </div>
    </div>

</template>

<script>
    import {mapGetters} from 'vuex'
    import TextareaField from '../components/fields/TextareaField'
    import LightswitchInput from '../components/inputs/LightswitchInput'

    export default {

        props: ['license', 'type'],

        data() {
            return {
                errors: {},
                licenseDraft: {},
            }
        },

        components: {
            TextareaField,
            LightswitchInput,
        },

        computed: {

            ...mapGetters({
                enableCommercialFeatures: 'enableCommercialFeatures',
            }),

        },

        methods: {

            /**
             * Save auto renew
             */
            saveAutoRenew() {
                this.$store.dispatch('saveLicense', {
                    id: this.license.id,
                    type: this.type,
                    autoRenew: (this.licenseDraft.autoRenew ? 1 : 0),
                }).then((data) => {
                    if (this.licenseDraft.autoRenew) {
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
             * Save notes.
             */
            saveNotes() {
                this.$store.dispatch('saveLicense', {
                    id: this.license.id,
                    type: this.type,
                    notes: this.licenseDraft.notes,
                }).then((data) => {
                    this.$root.displayNotice('License saved.');
                }).catch((data) => {
                    this.$root.displayError('Couldn’t save license.');
                    this.errors = data.errors;
                });
            },

            /**
             * Cancel.
             */
            cancel() {
                this.licenseDraft.notes = this.license.notes;
            }

        },

        mounted() {
            this.licenseDraft = {
                autoRenew: (this.license.autoRenew == 1 ? true : false),
                notes: this.license.notes,
            };
        }

    }
</script>

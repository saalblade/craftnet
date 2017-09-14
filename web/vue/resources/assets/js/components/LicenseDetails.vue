<template>
    <div class="card mb-3">
        <div class="card-header">License Details</div>
        <div class="card-body">
            <template v-if="license">

                <div class="row">
                    <div class="col-sm-6">

                        <dl>
                            <dt>License ID</dt>
                            <dd>000000{{ license.id }}</dd>

                            <template v-if="license.type == 'craftLicenses'">
                                <dt>Edition</dt>
                                <dd>Craft {{ license.craftEdition.label }}</dd>

                                <dt>Domain</dt>
                                <dd>{{ license.domain }}</dd>
                            </template>

                            <template v-if="license.type == 'pluginLicenses'">
                                <dt>Plugin</dt>
                                <dd>{{ license.plugin.title }}</dd>
                            </template>

                            <dt>Email</dt>
                            <dd>{{ license.author.email }}</dd>
                        </dl>
                    </div>
                    <div class="col-sm-6">
                        <dl>

                            <dt>Update Period</dt>
                            <dd>2017/05/11 to 2018/05/11</dd>

                            <dt>Auto Renew</dt>
                            <dd>
                                <label><input @change="saveAutoRenew()" type="checkbox" v-model="licenseDraft.autoRenew"> Auto renew license</label>
                            </dd>

                            <dt>Created</dt>
                            <dd>{{license.dateCreated}}</dd>

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
    import TextareaField from '../components/fields/TextareaField'

    export default {
        props: ['license'],

        data() {
            return {
                errors: {},
                licenseDraft: {},
            }
        },

        components: {
            TextareaField,
        },

        methods: {
            saveAutoRenew() {
                this.$store.dispatch('saveLicense', {
                    id: this.license.id,
                    type: this.license.type,
                    autoRenew: (this.licenseDraft.autoRenew ? 1 : 0),
                }).then((data) => {
                    this.$root.displayNotice('License saved.');
                }).catch((data) => {
                    this.$root.displayError('Couldn’t save license.');
                    this.errors = data.errors;
                });
            },
            saveNotes() {
                this.$store.dispatch('saveLicense', {
                    id: this.license.id,
                    type: this.license.type,
                    notes: this.licenseDraft.notes,
                }).then((data) => {
                    this.$root.displayNotice('License saved.');
                }).catch((data) => {
                    this.$root.displayError('Couldn’t save license.');
                    this.errors = data.errors;
                });
            },
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

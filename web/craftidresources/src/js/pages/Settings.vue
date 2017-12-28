<template>
    <form v-if="userDraft" @submit.prevent="save()">
        <connected-apps title="Connected Apps"></connected-apps>

        <div class="card mb-3">
            <div class="card-body">
                <h4>Email &amp; password</h4>
                <password-field id="password" label="Current Password" v-model="password" :errors="errors.currentPassword" />
                <text-field id="email" label="Email" v-model="userDraft.email" :errors="errors.email" />
                <password-field id="newPassword" label="New Password" v-model="newPassword" :errors="errors.newPassword" />
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <h4>Account Settings</h4>

                <text-field id="username" label="Username" v-model="userDraft.username" :errors="errors.username" />

                <p>
                    <input id="enablePluginDeveloperFeatures" :disabled="userIsInGroup('developers')" type="checkbox" name="fields[enablePluginDeveloperFeatures]" v-model="userDraft.enablePluginDeveloperFeatures">
                    <label for="enablePluginDeveloperFeatures" :class="userIsInGroup('developers') ? 'disabled' : ''">Enable plugin developer features</label>
                </p>

                <p v-if="userIsInGroup('staff')">
                    <input id="enableShowcaseFeatures" type="checkbox" name="fields[enableShowcaseFeatures]" v-model="userDraft.enableShowcaseFeatures">
                    <label for="enableShowcaseFeatures">Enable showcase features</label>
                </p>

                <input v-else type="hidden" name="fields[enableShowcaseFeatures]" v-model="userDraft.enableShowcaseFeatures" />
            </div>
        </div>

        <input type="submit" class="btn btn-primary" value="Save" :disabled="loading" />
        <div v-if="loading" class="spinner"></div>
    </form>
</template>

<script>
    import { mapGetters } from 'vuex'
    import TextField from '../components/fields/TextField'
    import PasswordField from '../components/fields/PasswordField'
    import ConnectedApps from '../components/ConnectedApps'

    export default {

        components: {
            TextField,
            PasswordField,
            ConnectedApps,
        },

        data() {
            return {
                loading: false,
                photoLoading: false,
                userDraft: {},
                password: '',
                newPassword: '',
                errors: {},
            }
        },

        computed: {

            ...mapGetters({
                currentUser: 'currentUser',
                userIsInGroup: 'userIsInGroup',
            }),

        },

        methods: {

            save() {
                this.loading = true;

                let newEmail = false;

                if(this.currentUser.email !== this.userDraft.email) {
                    newEmail = true;
                }

                this.$store.dispatch('saveUser', {
                    id: this.userDraft.id,
                    email: this.userDraft.email,
                    username: this.userDraft.username,
                    enablePluginDeveloperFeatures: (this.userDraft.enablePluginDeveloperFeatures ? 1 : 0),
                    enableShowcaseFeatures: (this.userDraft.enableShowcaseFeatures ? 1 : 0),
                    password: this.password,
                    newPassword: this.newPassword,
                }).then(response => {
                    this.loading = false;

                    if(newEmail) {
                        this.userDraft.email = this.currentUser.email;
                        this.$root.displayNotice('You’ve been sent an email to verify your new email address.');
                    } else {
                        this.$root.displayNotice('Settings saved.');
                    }

                    this.password = '';
                    this.newPassword = '';
                    this.errors = {};
                }).catch(response => {
                    this.loading = false;

                    let errorMessage = 'Couldn’t save settings.';

                    if(response.data && response.data.error) {
                        errorMessage = response.data.error;
                    }

                    this.$root.displayError(errorMessage);

                    this.errors = {};

                    if(response.data && response.data.errors) {
                        this.errors = response.data.errors;
                    }
                });
            }

        },

        mounted() {
            this.userDraft = JSON.parse(JSON.stringify(this.currentUser));
        }

    }
</script>

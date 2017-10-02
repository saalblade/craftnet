<template>
    <form v-if="userDraft" @submit.prevent="save()">
        <div class="card mb-3">
            <div class="card-body">
                <h4>Informations</h4>

                <text-field id="developerName" label="Developer Name" v-model="userDraft.developerName" :errors="errors.developerName" />
                <text-field id="developerUrl" label="Developer URL" v-model="userDraft.developerUrl" :errors="errors.developerUrl" />
                <text-field id="location" label="Location" v-model="userDraft.location" :errors="errors.location" />
            </div>
        </div>


        <div class="card mb-3">
            <div class="card-body">

                <h4>Photo</h4>

                <input type="file" ref="photoFile" class="form-control" @change="changePhoto" />

                <img :src="userDraft.photoUrl" style="height: 150px;" class="img-thumbnail mr-3 mt-3" />

            </div>
        </div>


        <div class="card mb-3">
            <div class="card-body">

                <h4>Email &amp; password</h4>

                <password-field id="currentPassword" label="Current Password" v-model="currentPassword" :errors="errors.currentPassword" />
                <text-field id="email" label="Email" v-model="userDraft.email" :errors="errors.email" />
                <password-field id="newPassword" label="New Password" v-model="newPassword" :errors="errors.newPassword" />

            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body">

                <h4>Account</h4>

                <p v-if="!userIsInGroup('developers')">
                    <input id="enablePluginDeveloperFeatures" type="checkbox" name="fields[enablePluginDeveloperFeatures]" v-model="userDraft.enablePluginDeveloperFeatures">
                    <label for="enablePluginDeveloperFeatures">Enable plugin developer features</label>
                </p>

                <p>
                    <input id="enableShowcaseFeatures" type="checkbox" name="fields[enableShowcaseFeatures]" v-model="userDraft.enableShowcaseFeatures">
                    <label for="enableShowcaseFeatures">Enable showcase features</label>
                </p>
            </div>
        </div>

        <input type="submit" class="btn btn-primary" value="Save">
    </form>
</template>

<script>
    import { mapGetters } from 'vuex'
    import TextField from '../components/fields/TextField'
    import PasswordField from '../components/fields/PasswordField'

    export default {
        components: {
            TextField,
            PasswordField,
        },

        data() {
            return {
                userDraft: {},
                currentPassword: null,
                newPassword: null,
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
            changePhoto(ev) {
                let reader = new FileReader();

                reader.onload = function (e) {
                    this.userDraft.photoUrl = [e.target.result]
                }.bind(this);

                reader.readAsDataURL(this.$refs.photoFile.files[0]);
            },

            save() {
                this.$store.dispatch('saveUser', {
                    id: this.userDraft.id,
                    // email: this.userDraft.email,
                    developerName: this.userDraft.developerName,
                    developerUrl: this.userDraft.developerUrl,
                    location: this.userDraft.location,
                    enablePluginDeveloperFeatures: (this.userDraft.enablePluginDeveloperFeatures ? 1 : 0),
                    enableShowcaseFeatures: (this.userDraft.enableShowcaseFeatures ? 1 : 0),
                    // currentPassword: this.currentPassword,
                    // newPassword: this.newPassword,
                    photo: this.$refs.photoFile.files[0],
                    photoUrl: this.userDraft.photoUrl,
                }).then((data) => {
                    this.$root.displayNotice('Settings saved.');
                    this.showForm = false;
                    this.errors = {};
                }).catch((data) => {
                    this.$root.displayError('Couldn’t save settings.');
                    this.errors = data.errors;
                });
            }
        },

        mounted() {
            this.userDraft = JSON.parse(JSON.stringify(this.currentUser));
        }
    }
</script>

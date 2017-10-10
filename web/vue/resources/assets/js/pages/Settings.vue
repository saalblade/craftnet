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
                <h4>Email &amp; password</h4>
                <password-field id="password" label="Current Password" v-model="password" :errors="errors.currentPassword" />
                <text-field id="email" label="Email" v-model="userDraft.email" :errors="errors.email" />
                <password-field id="newPassword" label="New Password" v-model="newPassword" :errors="errors.newPassword" />
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <h4>Photo</h4>

                <div class="d-flex">
                    <div class="">
                        <img ref="photo" :src="userDraft.photoUrl" style="width: 150px; height: 150px;" class="img-thumbnail mr-3" />
                    </div>
                    <div>
                        <template v-if="userDraft.photoId">
                            <div class="form-group">
                                <input type="button" class="btn btn-secondary" value="Change Photo" @click="changePhoto" :disabled="photoLoading" />
                            </div>
                            <div class="form-group">
                                <input type="button" class="btn btn-danger" value="Delete" @click="deletePhoto" :disabled="photoLoading" />
                            </div>
                        </template>
                        <template v-else>
                            <div class="form-group">
                                <input type="button" class="btn btn-secondary" value="Upload a photo" @click="changePhoto" :disabled="photoLoading" />
                            </div>
                        </template>
                        <div v-if="photoLoading" class="spinner"></div>
                        <input type="file" ref="photoFile" class="d-none" @change="onChangePhoto" />
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <h4>GitHub Account</h4>
                <p>Connect to your GitHub account.</p>
                <input type="button" class="btn btn-primary" value="Connect" @click="connectGithub()" />
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <h4>Account</h4>

                <p>
                    <input id="enablePluginDeveloperFeatures" :disabled="userIsInGroup('developers')" type="checkbox" name="fields[enablePluginDeveloperFeatures]" v-model="userDraft.enablePluginDeveloperFeatures">
                    <label for="enablePluginDeveloperFeatures" :class="userIsInGroup('developers') ? 'disabled' : ''">Enable plugin developer features</label>
                </p>

                <p>
                    <input id="enableShowcaseFeatures" type="checkbox" name="fields[enableShowcaseFeatures]" v-model="userDraft.enableShowcaseFeatures">
                    <label for="enableShowcaseFeatures">Enable showcase features</label>
                </p>
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

    export default {
        components: {
            TextField,
            PasswordField,
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

            connectGithub() {
                let width = 800;
                let height = 600;

                let winWidth = window.innerWidth ? window.innerWidth : document.documentElement.clientWidth ? document.documentElement.clientWidth : screen.width;
                let winHeight = window.innerHeight ? window.innerHeight : document.documentElement.clientHeight ? document.documentElement.clientHeight : screen.height;

                let left = ((winWidth / 2) - (width / 2));
                let top = ((winHeight / 2) - (height / 2));

                // let url = Craft.getActionUrl('plugin-store/connect', {redirect: Craft.getActionUrl('plugin-store/modal-callback') });
                let url = '/test/developer/connect';

                let name = 'ConnectWithOauth';
                let specs = 'location=0,status=0,width=' + width + ',height=' + height + ',left=' + left + ',top=' + top;

                window.open(url, name, specs);
            },

            deletePhoto(ev) {
                if (confirm("Are you sure you want to delete this image?")) {
                    this.photoLoading = true;

                    let formData = new FormData();
                    formData.append('userId', this.userDraft.id);

                    this.$store.dispatch('deleteUserPhoto', formData).then(data => {
                        this.$root.displayNotice('Photo deleted.');
                        this.userDraft.photoId = data.photoId;
                        this.userDraft.photoUrl = data.photoUrl;
                        this.photoLoading = false;
                    }).catch(response => {
                        this.$root.displayError('Couldn’t delete photo.');
                        this.photoLoading = false;
                    })
                }
            },

            changePhoto() {
                this.$refs.photoFile.click();
            },

            onChangePhoto(ev) {
                /*let reader = new FileReader();

                reader.onload = function (e) {
                    this.userDraft.photoUrl = [e.target.result]
                }.bind(this);

                reader.readAsDataURL(this.$refs.photoFile.files[0]);*/

                this.photoLoading = true;

                let formData = new FormData();
                formData.append('userId', this.userDraft.id);
                formData.append('photo', this.$refs.photoFile.files[0]);
                formData.append('photoUrl', this.userDraft.photoUrl);

                this.$store.dispatch('uploadUserPhoto', formData)
                    .then(data => {
                        this.$root.displayNotice('Photo uploaded.');

                        this.userDraft.photoId = data.photoId;
                        this.userDraft.photoUrl = data.photoUrl + '&'+Math.floor(Math.random() * 1000000);

                        this.errors = {};

                        this.photoLoading = false;
                    }).catch(data => {
                        this.$root.displayError('Couldn’t upload photo.');
                        this.errors = data.errors;
                    });
            },

            save() {
                this.loading = true;

                this.$store.dispatch('saveUser', {
                    id: this.userDraft.id,
                    email: this.userDraft.email,
                    developerName: this.userDraft.developerName,
                    developerUrl: this.userDraft.developerUrl,
                    location: this.userDraft.location,
                    enablePluginDeveloperFeatures: (this.userDraft.enablePluginDeveloperFeatures ? 1 : 0),
                    enableShowcaseFeatures: (this.userDraft.enableShowcaseFeatures ? 1 : 0),
                    password: this.password,
                    newPassword: this.newPassword,
                    photo: this.$refs.photoFile.files[0],
                    photoUrl: this.userDraft.photoUrl,
                }).then(data => {
                    this.$root.displayNotice('Settings saved.');
                    this.password = '';
                    this.newPassword = '';
                    this.errors = {};
                    this.loading = false;
                }).catch(data => {
                    this.$root.displayError('Couldn’t save settings.');
                    this.errors = {};
                    if(data.errors) {
                        this.errors = data.errors;
                    }
                    this.loading = false;
                });
            }
        },

        mounted() {
            this.userDraft = JSON.parse(JSON.stringify(this.currentUser));
        }
    }
</script>

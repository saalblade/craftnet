<template>
    <form v-if="userDraft" @submit.prevent="save()">
        <h1>Profile</h1>

        <text-field id="developerName" label="Developer Name" v-model="userDraft.developerName" :errors="errors.developerName" />
        <url-field id="developerUrl" label="Developer URL" v-model="userDraft.developerUrl" :errors="errors.developerUrl" />
        <text-field id="location" label="Location" v-model="userDraft.location" :errors="errors.location" />

        <hr />

        <h2>Photo</h2>

        <div class="flex">
            <div class="">
                <img ref="photo" :src="userDraft.photoUrl" style="width: 150px; height: 150px;" class="img-thumbnail mr-3" />
            </div>
            <div>
                <template v-if="userDraft.photoId">
                    <div class="field">
                        <input type="button" class="btn btn-secondary" value="Change Photo" @click="changePhoto" :disabled="photoLoading" />
                    </div>
                    <div class="field">
                        <a href="#" class="btn btn-danger" @click.prevent="deletePhoto" :disabled="photoLoading">
                            <font-awesome-icon icon="times" />
                            Delete
                        </a>
                    </div>
                </template>
                <template v-else>
                    <div class="field">
                        <input type="button" class="btn btn-secondary" value="Upload a photo" @click="changePhoto" :disabled="photoLoading" />
                    </div>
                </template>
                <div v-if="photoLoading" class="spinner"></div>
                <input type="file" ref="photoFile" class="hidden" @change="onChangePhoto" />
            </div>
        </div>

        <hr />

        <p class="text-secondary"><em>Your profile data is being used for your developer page on the Plugin Store.</em></p>

        <input type="submit" class="btn btn-primary" value="Save" :disabled="loading" />
        <div v-if="loading" class="spinner"></div>
    </form>
</template>

<script>
    import {mapState, mapGetters} from 'vuex'
    import ConnectedApps from '../../components/developer/connected-apps/ConnectedApps'

    export default {

        components: {
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

            ...mapState({
                currentUser: state => state.account.currentUser,
            }),

            ...mapGetters({
                userIsInGroup: 'account/userIsInGroup',
            }),

        },

        methods: {

            /**
             * Delete photo.
             *
             * @param ev
             */
            deletePhoto(ev) {
                if (confirm("Are you sure you want to delete this image?")) {
                    this.photoLoading = true;

                    this.$store.dispatch('account/deleteUserPhoto')
                        .then(response => {
                            this.$store.dispatch('app/displayNotice', 'Photo deleted.');
                            this.userDraft.photoId = response.data.photoId;
                            this.userDraft.photoUrl = response.data.photoUrl;
                            this.photoLoading = false;
                        })
                        .catch(response => {
                            this.photoLoading = false;

                            const errorMessage = response.data && response.data.error ? response.data.error : 'Couldn’t delete photo.';
                            this.$store.dispatch('app/displayError', errorMessage);

                            this.errors = response.data && response.data.errors ? response.data.errors : {};
                        })
                }
            },

            /**
             * Change photo.
             */
            changePhoto() {
                this.$refs.photoFile.click();
            },

            /**
             * On change photo.
             *
             * @param ev
             */
            onChangePhoto(ev) {
                /*let reader = new FileReader();

                reader.onload = function (e) {
                    this.userDraft.photoUrl = [e.target.result]
                }.bind(this);

                reader.readAsDataURL(this.$refs.photoFile.files[0]);*/

                this.photoLoading = true;

                let data = {
                    photo: this.$refs.photoFile.files[0],
                    photoUrl: this.userDraft.photoUrl,
                };

                this.$store.dispatch('account/uploadUserPhoto', data)
                    .then(response => {
                        this.$store.dispatch('app/displayNotice', 'Photo uploaded.');
                        let photoUrl = response.data.photoUrl
                        this.userDraft.photoId = response.data.photoId;
                        this.userDraft.photoUrl = photoUrl + (photoUrl.match(/\?/g) ? '&' : '?') + Math.floor(Math.random() * 1000000);
                        this.errors = {};

                        this.photoLoading = false;
                    })
                    .catch(response => {
                        this.photoLoading = false;

                        const errorMessage = response.data && response.data.error ? response.data.error : 'Couldn’t upload photo.';
                        this.$store.dispatch('app/displayError', errorMessage);

                        this.errors = response.data && response.data.errors ? response.data.errors : {};
                    });
            },

            /**
             * Save the profile.
             */
            save() {
                this.loading = true;

                this.$store.dispatch('account/saveUser', {
                        id: this.userDraft.id,
                        developerName: this.userDraft.developerName,
                        developerUrl: this.userDraft.developerUrl,
                        location: this.userDraft.location,
                        photoUrl: this.userDraft.photoUrl,
                    })
                    .then(response => {
                        this.$store.dispatch('app/displayNotice', 'Settings saved.');
                        this.errors = {};
                        this.loading = false;
                    })
                    .catch(response => {
                        this.loading = false;

                        const errorMessage = response.data && response.data.error ? response.data.error : 'Couldn’t save profile.';
                        this.$store.dispatch('app/displayError', errorMessage);

                        this.errors = response.data && response.data.errors ? response.data.errors : {};
                    });
            }
        },

        mounted() {
            this.userDraft = JSON.parse(JSON.stringify(this.currentUser));
        }

    }
</script>

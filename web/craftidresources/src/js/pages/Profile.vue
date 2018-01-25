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

				<div class="flex">
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
						<input type="file" ref="photoFile" class="hidden" @change="onChangePhoto" />
					</div>
				</div>
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

            deletePhoto(ev) {
                if (confirm("Are you sure you want to delete this image?")) {
                    this.photoLoading = true;

                    let data = {
						userId: this.userDraft.id,
					};

                    this.$store.dispatch('deleteUserPhoto', data).then(response => {
                        this.$root.displayNotice('Photo deleted.');
                        this.userDraft.photoId = response.data.photoId;
                        this.userDraft.photoUrl = response.data.photoUrl;
                        this.photoLoading = false;
                    }).catch(response => {
                        this.photoLoading = false;

                        const errorMessage = response.data && response.data.error ? response.data.error : 'Couldn’t delete photo.';
                        this.$root.displayError(errorMessage);

                        this.errors = response.data && response.data.errors ? response.data.errors : {};
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

                let data = {
                    userId: this.userDraft.id,
                    photo: this.$refs.photoFile.files[0],
                    photoUrl: this.userDraft.photoUrl,
				};

                this.$store.dispatch('uploadUserPhoto', data)
                    .then(response => {
                        this.$root.displayNotice('Photo uploaded.');

                        this.userDraft.photoId = response.data.photoId;
                        this.userDraft.photoUrl = response.data.photoUrl + '&'+Math.floor(Math.random() * 1000000);

                        this.errors = {};

                        this.photoLoading = false;
                    }).catch(response => {
						this.photoLoading = false;

						const errorMessage = response.data && response.data.error ? response.data.error : 'Couldn’t upload photo.';
						this.$root.displayError(errorMessage);

						this.errors = response.data && response.data.errors ? response.data.errors : {};
					});
            },

            save() {
                this.loading = true;

                this.$store.dispatch('saveUser', {
                    id: this.userDraft.id,
                    developerName: this.userDraft.developerName,
                    developerUrl: this.userDraft.developerUrl,
                    location: this.userDraft.location,
                    photoUrl: this.userDraft.photoUrl,
                }).then(response => {
                    this.$root.displayNotice('Settings saved.');
                    this.errors = {};
                    this.loading = false;
                }).catch(response => {
                    this.loading = false;

                    const errorMessage = response.data && response.data.error ? response.data.error : 'Couldn’t save profile.';
                    this.$root.displayError(errorMessage);

                    this.errors = response.data && response.data.errors ? response.data.errors : {};
                });
            }
        },

        mounted() {
            this.userDraft = JSON.parse(JSON.stringify(this.currentUser));
        }

    }
</script>

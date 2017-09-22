<template>
    <form v-if="userDraft" @submit.prevent="save()">
        <div class="card">
            <div class="card-body">
                <h4>Personal details</h4>

                <text-field id="firstName" label="First Name" v-model="userDraft.firstName" :errors="errors.firstName" />
                <text-field id="lastName" label="Last Name" v-model="userDraft.lastName" :errors="errors.lastName" />

                <h4>Developer profile</h4>

                <text-field id="developerName" label="Developer Name" v-model="userDraft.developerName" :errors="errors.developerName" />
                <text-field id="developerUrl" label="Developer URL" v-model="userDraft.developerUrl" :errors="errors.developerUrl" />
                <text-field id="location" label="Location" v-model="userDraft.location" :errors="errors.location" />

                <template v-if="userIsInGroup('staff')">

                    <h4>Account</h4>

                    <p>
                        <input id="enablePluginDeveloperFeatures" type="checkbox" name="fields[enablePluginDeveloperFeatures]" v-model="userDraft.enablePluginDeveloperFeatures">
                        <label for="enablePluginDeveloperFeatures">Enable plugin developer features</label>
                    </p>

                    <p>
                        <input id="enableShowcaseFeatures" type="checkbox" name="fields[enableShowcaseFeatures]" v-model="userDraft.enableShowcaseFeatures">
                        <label for="enableShowcaseFeatures">Enable showcase features</label>
                    </p>
                </template>

                <input type="submit" class="btn btn-primary" value="Save">
            </div>
        </div>
    </form>
</template>

<script>
    import { mapGetters } from 'vuex'
    import TextField from '../components/fields/TextField'

    export default {
        components: {
            TextField,
        },

        data() {
            return {
                errors: {},
                userDraft: {},
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
                this.$store.dispatch('saveUser', {
                    id: this.userDraft.id,
                    firstName: this.userDraft.firstName,
                    lastName: this.userDraft.lastName,
                    developerName: this.userDraft.developerName,
                    developerUrl: this.userDraft.developerUrl,
                    location: this.userDraft.location,
                    enablePluginDeveloperFeatures: (this.userDraft.enablePluginDeveloperFeatures ? 1 : 0),
                    enableShowcaseFeatures: (this.userDraft.enableShowcaseFeatures ? 1 : 0),
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

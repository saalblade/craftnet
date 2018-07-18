<template>
    <div>
        <div class="card mb-4">
            <div class="card-body">
                <p>Links to privacy policy, terms, etc. here</p>
                <form @submit.prevent="onSubmit">
                    <div class="form-group">
                        <label><input type="checkbox" v-model="userDraft.acceptsPartnerPolicy"> I have read
                            and all policies and consent to terms</label>
                    </div>
                    <input type="submit" class="btn btn-primary" :disabled="!userDraft.acceptsPartnerPolicy" value="Continue">
                    <div v-if="loading" class="spinner"></div>
                </form>
            </div>
        </div>
    </div>
</template>

<script>
    import {mapState} from 'vuex'

    export default {
        data() {
            return {
                loading: false,
                userDraft: {},
            }
        },

        computed: {
            ...mapState({
                currentUser: state => state.account.currentUser,
            }),
        },

        methods: {
            onSubmit() {
                if (!this.userDraft.acceptsPartnerPolicy) {
                    return
                }

                this.loading = true

                this.$store.dispatch('saveUser', this.userDraft).then(response => {
                    this.loading = false;
                }).catch(response => {
                    this.loading = false;

                    const errorMessage = response.data && response.data.error ? response.data.error : 'Couldn’t save settings.';
                    this.$root.displayError(errorMessage);
                });
            }
        },

        mounted() {
            this.userDraft = JSON.parse(JSON.stringify(this.currentUser));
        }
    }
</script>

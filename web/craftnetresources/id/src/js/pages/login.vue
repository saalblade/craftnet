<template>
    <div class="mx-auto max-w-sm">
        <div class="pt-8 mb-6 text-center">
            <h1 class="mb-0">Sign In</h1>
            <p class="lead">Manage your Craft ID.</p>
        </div>

        <div class="card">
            <div class="card-body">
                <form method="post" accept-charset="UTF-8" @submit.prevent="submit()">
                    <input type="hidden" :name="csrfTokenName" :value="csrfTokenValue">
                    <input type="hidden" name="action" value="users/login">

                    <text-field label="Username or email" id="loginName" v-model="loginName" ref="loginNameField" />

                    <password-field label="Password" id="password" v-model="password" ref="passwordField" />

                    <div class="form-check mb-2">
                        <label class="form-check-label">
                            <input type="checkbox" class="form-check-input" v-model="rememberMe" />
                            Remember me
                        </label>
                    </div>

                    <div class="action">
                        <btn class="primary" type="submit" :loading="loading" :disabled="!formValidates() || loading" block>Login</btn>
                    </div>
                </form>

                <p class="mt-4"><router-link to="/site/forgot-password">Forgot your password?</router-link></p>
            </div>
        </div>

        <div class="mt-4 text-center">
            <p>Don’t have an account? <router-link to="/site/register">Sign up</router-link>.</p>
        </div>
    </div>
</template>

<script>
    /* global Craft */

    import {mapState} from 'vuex'
    import usersApi from '../api/users'
    import helpers from '../mixins/helpers'
    import FormDataHelper from '../helpers/form-data'

    export default {
        mixins: [helpers],

        data() {
            return {
                loading: false,
                loginName: '',
                password: '',
                rememberMe: false,
            };
        },

        computed: {
            ...mapState({
                currentUser: state => state.users.currentUser,
            }),

            csrfTokenName() {
                return Craft.csrfTokenName;
            },

            csrfTokenValue() {
                return Craft.csrfTokenValue;
            },

            rememberedUsername() {
                return window.rememberedUsername
            }
        },

        methods: {
            submit() {
                if (this.loading) {
                    return false
                }

                if (!this.formValidates()) {
                    this.$store.dispatch('app/displayError', 'Couldn’t login.')
                    return false
                }

                this.loading = true


                // Send login request

                let formData = new FormData()

                FormDataHelper.append(formData, 'loginName', this.loginName)
                FormDataHelper.append(formData, 'password', this.password)
                FormDataHelper.append(formData, 'rememberMe', (this.rememberMe ? '1' : '0'))

                usersApi.login(formData)
                    .then(() => {
                        // Set `remainingSessionTime` to something different than 0 to give the auth manager a chance to get the real remaining session time
                        // todo: Take Craft’s userSessionDuration config into account
                        Craft.remainingSessionTime = 3600

                        this.loadAuthenticatedUserData(() => {
                            this.loading = false
                            this.$store.dispatch('app/displayNotice', 'Logged in.')
                            this.$router.push({path: '/'})
                        }, () => {
                            this.loading = false
                            this.$store.dispatch('app/displayError', 'Couldn’t login.')
                        })
                    })
                    .catch(() => {
                        this.loading = false
                        this.$store.dispatch('app/displayError', 'Couldn’t login.')
                    });
            },

            /**
             * Password validates.
             *
             * @returns {boolean}
             */
            passwordValidates() {
                if (this.password.length >= 6) {
                    return true;
                }
            },

            /**
             * Form validates.
             *
             * @returns {boolean}
             */
            formValidates() {
                if (this.loginName.length && this.passwordValidates()) {
                    return true;
                }

                return false;
            },
        },

        mounted() {
            if (this.currentUser) {
                this.$router.push({path: '/'})
            } else {
                if (this.rememberedUsername) {
                    this.loginName = this.rememberedUsername;
                }

                if (this.loginName.length === 0) {
                    this.$refs.loginNameField.$children[0].$el.focus();
                } else {
                    this.$refs.passwordField.$children[0].$el.focus();
                }
            }
        }
    }
</script>

<style lang="scss" scoped>
    .action {
        @apply .relative;

        .spinner {
            @apply .absolute;
            bottom: -32px;
            right: 0;
        }
    }
</style>
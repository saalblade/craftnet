<template>
    <div>
        <div class="mb-6">
            <h1 class="mb-0">Sign in</h1>
            <p class="lead">or <router-link to="/site/register">create an account</router-link></p>
        </div>

        <form method="post" accept-charset="UTF-8" @submit.prevent="submit()">
            <input type="hidden" :name="csrfTokenName" :value="csrfTokenValue">
            <input type="hidden" name="action" value="users/login">

            <textbox label="Username or email" id="loginName" v-model="loginName" ref="loginNameField" />

            <textbox type="password" label="Password" id="password" v-model="password" ref="passwordField" />

            <div class="form-check mb-2">
                <label class="form-check-label">
                    <input type="checkbox" v-model="rememberMe" />
                    Remember me
                </label>
            </div>

            <div class="action">
                <btn kind="primary" type="submit" :loading="loading" :disabled="!formValidates() || loading" block>Login</btn>
            </div>

            <p class="mt-4"><router-link to="/site/forgot-password">Forgot your password?</router-link></p>
        </form>
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
                currentUser: state => state.account.currentUser,
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
                    .then((response) => {
                        if (response.data.error) {
                            this.loading = false
                            this.$store.dispatch('app/displayError', response.data.error)
                            return
                        }

                        // Set `remainingSessionTime` to something different than 0 to give the auth manager a chance to get the real remaining session time
                        // todo: Take Craft’s userSessionDuration config into account
                        Craft.remainingSessionTime = 3600
                        
                        if (response.data.returnUrl) {
                            window.location.replace(response.data.returnUrl)
                            return
                        }

                        this.loadAccount(
                            // success
                            () => {
                                this.loading = false
                                this.$store.dispatch('app/displayNotice', 'Logged in.')
                                this.$router.push({path: '/'})
                            },
                            // error
                            () => {
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
                    this.$refs.loginNameField.$refs.input.focus();
                } else {
                    this.$refs.passwordField.$refs.input.focus();
                }
            }
        }
    }
</script>

<style lang="scss">
    .action {
        @apply .relative;

        .spinner {
            @apply .absolute;
            bottom: -32px;
            right: 0;
        }
    }


    ul.features {
        @apply .text-xl;

        li {
            @apply .py-6;

            .c-icon {
                @apply .text-white .mr-2;
            }
        }
    }
</style>

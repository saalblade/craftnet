<template>
    <div class="mx-auto max-w-sm">
        <div class="pt-8 mb-6 text-center">
            <h1 class="mb-0">Forgot your password?</h1>
            <p class="lead">Enter your email below to receive your password reset instructions.</p>
        </div>

        <div class="col-6 ml-auto mr-auto">
            <div class="card">
                <div class="card-body">
                    <form @submit.prevent="submit()">
                        <textbox id="loginName" label="Username or email" v-model="loginName" ref="loginName" />

                        <div class="action">
                            <btn kind="primary" type="submit" :disabled="loading || $v.$invalid" block large>Send reset email</btn>
                            <spinner v-if="loading" />
                        </div>
                    </form>
                </div>
            </div>

            <p class="mt-3 text-center"><router-link to="/site/login">Sign in instead</router-link></p>
        </div>
    </div>
</template>

<script>
    import {required} from 'vuelidate/lib/validators'
    import usersApi from '../api/users'
    import FormDataHelper from '../helpers/form-data'
    import helpers from '../mixins/helpers'

    export default {
        mixins: [helpers],

        data() {
            return {
                loading: false,
                loginName: '',
            }
        },

        validations: {
            loginName: {
                required,
            },
        },

        methods: {
            submit() {
                this.loading = true

                let formData = new FormData()

                FormDataHelper.append(formData, 'loginName', this.loginName)

                usersApi.sendPasswordResetEmail(formData)
                    .then(response => {
                        this.loading = false

                        if (response.data.error) {
                            this.$store.dispatch('app/displayError', response.data.error)
                        } else {
                            this.loginName = ''
                            const loginNameInput = this.$refs.loginName.$children[0].$children[0].$el
                            loginNameInput.blur()
                            this.$store.dispatch('app/displayNotice', 'Password reset email sent.')
                        }
                    })
                    .catch(() => {
                        this.loading = false
                        this.$store.dispatch('app/displayError', 'Couldn’t send reset email.')
                    });
            }
        }
    }
</script>

<style lang="scss" scoped>
    .action {
        @apply .relative;

        .spinner {
            @apply .absolute;
            margin-left: -12px;
            bottom: -26px;
            left: 50%;
        }
    }
</style>

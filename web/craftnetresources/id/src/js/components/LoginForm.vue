<template>
    <div class="mx-auto max-w-sm">
        <div class="pt-8 mb-6 text-center">
            <div class="pt-8 pb-3">
                <img src="staticImageUrl('craftid.svg')" width="80" height="80" />
            </div>
            <h1 class="mb-0">Craft ID</h1>
            <p class="lead">Manage your Craft ID.</p>
        </div>

        <div class="card">
            <div class="card-body">
                <form method="post" accept-charset="UTF-8">
                    <input type="hidden" :name="csrfTokenName" :value="csrfTokenValue">
                    <input type="hidden" name="action" value="users/login">

                    <text-field label="Username or email" id="loginName" name="loginName" v-model="loginName" ref="loginNameField" />

                    <password-field label="Password" id="password" name="password" v-model="password" ref="passwordField" />

                    <div class="form-check mb-2">
                        <label class="form-check-label">
                            <input type="checkbox" class="form-check-input" name="rememberMe" value="1">
                            Remember me
                        </label>
                    </div>

                    <input type="submit" class="btn btn-primary w-full" :disabled="!formValidates()" value="Login">
                </form>

                <p class="mt-4"><a href="/forgotpassword">Forgot your password?</a></p>
            </div>
        </div>

        <div class="mt-4 text-center">
            <p>Don’t have an account? <a href="/register">Sign up</a>.</p>
        </div>
    </div>
</template>

<script>
    import helpers from '../mixins/helpers'

    export default {

        mixins: [helpers],

        props: ['rememberedUsername'],

        data() {
            return {
                loginName: '',
                password: '',
            };
        },

        computed: {

            csrfTokenName() {
                return Craft.csrfTokenName;
            },

            csrfTokenValue() {
                return Craft.csrfTokenValue;
            },

        },

        methods: {

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
</script>

<style lang="scss">
    @import './../../sass/app.scss';
</style>
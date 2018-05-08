<template>
	<div>
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
	</div>
</template>

<script>
    import PasswordField from '../components/fields/PasswordField'
    import TextField from '../components/fields/TextField'

    export default {

        props: ['rememberedUsername'],

		components: {
            PasswordField,
            TextField,
		},

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
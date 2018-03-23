<template>
	<div>
		<form method="post" accept-charset="UTF-8">
			<input type="hidden" :name="csrfTokenName" :value="csrfTokenValue">
			<input type="hidden" name="action" value="users/login">

			<div class="form-group">
				<label for="loginName">Username or email</label>
				<input class="form-control" id="loginName" type="text" name="loginName" value="" v-model="loginName" ref="loginNameInput" />
			</div>

			<div class="form-group">
				<label for="password">Password</label>
				<input class="form-control" id="password" type="password" name="password" v-model="password" ref="passwordInput" />
			</div>

			<div class="form-check">
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
    export default {

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
                this.$refs.loginNameInput.focus();
            } else {
                this.$refs.passwordInput.focus();
            }
        }

    }
</script>
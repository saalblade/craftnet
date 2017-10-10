<template>
	<div>
		<div class="card mb-3">
			<div class="card-body">
				<template v-if="apps.github">
					<div class="d-flex align-items-start">
						<img class="d-flex mr-3 rounded-circle" :src="apps.github.account.avatar_url" width="48" />
						<div class="media-body">
							<h4>GitHub</h4>
							<p>
								{{ apps.github.account.name }}<br />
								<span class="text-secondary">{{ apps.github.account.login }}</span>
							</p>
						</div>

						<div>
							<a href="#" class="btn btn-danger" @click.prevent="disconnectGithub()">Disconnect</a>

							<div v-if="loading" class="mt-2 text-right">
								<div class="spinner"></div>
							</div>
						</div>
					</div>
				</template>

				<template v-else>
					<div class="flex-column">
						<div class="d-flex justify-content-between">
							<div>
								<h4>GitHub</h4>
								<p>Connect to your GitHub account.</p>
							</div>
							<div>
								<a href="#" class="btn btn-primary" @click.prevent="connectGithub()">Connect</a>
							</div>
						</div>
					</div>
				</template>
			</div>
		</div>
	</div>
</template>

<script>
    import { mapGetters } from 'vuex'

    export default {

        data() {
          	return {
          	  	loading: false,
			};
		},

        computed: {
            ...mapGetters({
                apps: 'apps',
                currentUser: 'currentUser',
                userIsInGroup: 'userIsInGroup',
            }),
        },

        methods: {

            disconnectGithub() {
                this.loading = true;
                this.$store.dispatch('disconnectApp', 'github')
					.then(data => {
                        this.loading = false;
					}).catch(data => {
                    	this.loading = false;
					});
			},

            connectGithub() {
                let width = 800;
                let height = 770;

                let winWidth = window.innerWidth ? window.innerWidth : document.documentElement.clientWidth ? document.documentElement.clientWidth : screen.width;
                let winHeight = window.innerHeight ? window.innerHeight : document.documentElement.clientHeight ? document.documentElement.clientHeight : screen.height;

                let left = ((winWidth / 2) - (width / 2));
                let top = ((winHeight / 2) - (height / 2));

                let url = '/apps/connect';

                let name = 'ConnectWithOauth';
                let specs = 'location=0,status=0,width=' + width + ',height=' + height + ',left=' + left + ',top=' + top;

                window.open(url, name, specs);
            },
        }
    }
</script>

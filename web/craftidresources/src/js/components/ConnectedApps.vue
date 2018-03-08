<template>
	<div>
		<div class="card mb-3">
			<div class="card-body">
				<template v-if="title">
					<h4>{{ title }}</h4>
				</template>

				<div class="list-group">
					<div v-for="appType, index in appTypes" class="list-group-item">
						<div class="flex items-start">
							<img class="flex mr-3" :src="'/craftidresources/dist/images/' + appType.handle + '.svg'" height="48" />
							<div class="flex-1">
								<template v-if="apps[appType.handle]">
									<h5>{{ accountName(appType.handle) }}</h5>
									<p class="mb-0">
										<span class="text-secondary">{{ appType.name }}</span>
									</p>
								</template>

								<template v-else>
									<h5>{{ appType.name }}</h5>
									<p class="mb-0">Connect to your {{ appType.name }} account.</p>
								</template>
							</div>
							<div>
								<a v-if="apps[appType.handle]" href="#" class="btn btn-danger" @click.prevent="disconnect(appType.handle)">Disconnect</a>
								<a v-else="" href="#" class="btn btn-primary" @click.prevent="connect(appType.handle)">Connect</a>

								<div v-if="loading && loading[appType.handle]" class="mt-2 text-right">
									<div class="spinner"></div>
								</div>
							</div>
						</div>

						<hr v-if="index != (appTypes.length - 1)">
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
    import { mapGetters } from 'vuex'

    export default {

        props: ['title'],

        data() {
          	return {
				appTypes: [
					{
					    handle: 'github',
						name: 'GitHub',
					},
					//{
					//	handle: 'bitbucket',
					//	name: 'BitBucket',
					//}
				],
          	  	loading: {
          	  	    bitbucket: false,
          	  	    github: false,
				},
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

            /**
			 * Account name.
			 *
             * @param appType
             * @returns {*}
             */
            accountName(appType) {
				if(this.apps[appType]) {
				    let app = this.apps[appType];
				    switch(appType) {
						case 'github':
							return app.account.name;
						    break;
						case 'bitbucket':
							return app.account.display_name;
						    break;
					}
				}
			},

            /**
			 * Connect.
			 *
             * @param provider
             */
            connect(provider) {
                let width = 800;
                let height = 830;

                switch(provider) {
					case 'bitbucket':
                        width = 1024;
                        height = 570;
					    break;
				}

                let winWidth = window.innerWidth ? window.innerWidth : document.documentElement.clientWidth ? document.documentElement.clientWidth : screen.width;
                let winHeight = window.innerHeight ? window.innerHeight : document.documentElement.clientHeight ? document.documentElement.clientHeight : screen.height;

                let left = ((winWidth / 2) - (width / 2));
                let top = ((winHeight / 2) - (height / 2));

                let url = '/apps/connect/' + provider;

                let name = 'ConnectWithOauth';
                let specs = 'location=0,status=0,width=' + width + ',height=' + height + ',left=' + left + ',top=' + top;

                window.open(url, name, specs);
            },

            /**
			 * Disconnect.
			 *
             * @param provider
             */
            disconnect(provider) {
                this.loading[provider] = true;
                this.$store.dispatch('disconnectApp', provider)
                    .then(response => {
                        this.loading[provider] = false;
                        this.$root.displayNotice('App disconnected.');
                    }).catch(response => {
						this.loading[provider] = false;

						const errorMessage = response.data && response.data.error ? response.data.error : 'Couldn’t disconnect app.';
						this.$root.displayError(errorMessage);

						this.errors = response.data && response.data.errors ? response.data.errors : {};
					});
            },

        }

    }
</script>

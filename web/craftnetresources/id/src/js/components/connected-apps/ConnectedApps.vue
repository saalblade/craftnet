<template>
    <div class="list-group">
        <stripe-app v-if="showStripe"></stripe-app>

        <template v-for="appType, index in appTypes">
            <connected-app
                    :name="appType.name"
                    :description="'Connect to your ' + appType.name + ' account.'"
                    :icon="'/craftnetresources/id/dist/images/' + appType.handle + '.svg'"
                    :account-name="accountName(appType.handle)"
                    :connected="apps[appType.handle]"
                    :buttonLoading="(loading && loading[appType.handle])"
                    @connect="connect(appType.handle)"
                    @disconnect="disconnect(appType.handle)"
            ></connected-app>

            <hr v-if="index != (appTypes.length - 1)">
        </template>
    </div>
</template>

<script>
    import {mapState, mapGetters} from 'vuex'
    import StripeApp from './StripeApp'
    import ConnectedApp from './ConnectedApp'

    export default {

        props: ['title', 'showStripe'],

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

        components: {
            StripeApp,
            ConnectedApp,
        },

        computed: {

            ...mapState({
                apps: state => state.account.apps,
                currentUser: state => state.account.currentUser,
            }),

            ...mapGetters({
                userIsInGroup: 'account/userIsInGroup',
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
                if (this.apps[appType]) {
                    let app = this.apps[appType];
                    switch (appType) {
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

                switch (provider) {
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
                this.$store.dispatch('account/disconnectApp', provider)
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

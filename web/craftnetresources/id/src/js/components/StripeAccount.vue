<template>
    <div class="list-group-item">
        <template v-if="loading">
            <div class="spinner"></div>
        </template>
        <template v-else>
            <div class="flex items-start">
                <img class="flex mr-3" :src="'/craftnetresources/id/dist/images/stripe.svg'" height="48" />
                <div class="flex-1">
                    <template v-if="stripeAccount">
                        <h5>{{ stripeAccount.display_name }}</h5>
                        <p class="mb-0">
                            <span class="text-secondary">Stripe</span>
                        </p>
                    </template>

                    <template v-else>
                        <h5>Stripe</h5>
                        <p class="mb-0">Connect your Stripe account so you can receive payouts for plugin purchases.</p>
                    </template>
                </div>
                <div>
                    <template v-if="stripeAccount">
                        <button type="button" class="btn btn-danger btn-sm" @click="disconnect()">Disconnect</button>
                    </template>
                    <a v-else class="btn btn-primary" :href="stripeConnectUrl">Connect</a>

                    <div v-if="disconnectLoading" class="mt-2 text-right">
                        <div class="spinner"></div>
                    </div>
                </div>
            </div>
        </template>
    </div>
</template>

<script>
    import {mapState} from 'vuex'

    export default {

        data() {
            return {
                disconnectLoading: false,
            }
        },

        computed: {

            ...mapState({
                stripeAccount: state => state.account.stripeAccount,
            }),

            loading() {
                return this.$root.stripeAccountLoading;
            },

            stripeConnectUrl() {
                return window.stripeConnectUrl;
            }

        },

        methods: {

            /**
             * Disconnect Stripe account.
             */
            disconnect() {
                this.disconnectLoading = true;

                this.$store.dispatch('disconnectStripeAccount').then(() => {
                    this.disconnectLoading = false;
                    this.$root.displayNotice('Stripe account removed.');
                });
            }

        },

    }
</script>

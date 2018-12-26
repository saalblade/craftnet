<template>
    <list-group-item>
        <template v-if="loading">
            <div class="spinner"></div>
        </template>
        <template v-else>
            <div class="flex items-start">
                <img class="flex mr-3" :src="icon" height="48" width="48" />
                <div class="flex-1">
                    <template v-if="connected">
                        <h5>{{ accountName }}</h5>
                        <p class="mb-0">
                            <span class="text-secondary">{{ name }}</span>
                        </p>
                    </template>

                    <template v-else>
                        <h5>{{ name }}</h5>
                        <p class="mb-0">{{ description }}</p>
                    </template>
                </div>
                <div class="ml-4">
                    <template v-if="connected">
                        <button class="btn btn-danger btn-sm" @click="$emit('disconnect')">Disconnect</button>
                    </template>
                    <template v-else>
                        <button class="btn btn-primary" @click.prevent="$emit('connect')">Connect</button>
                    </template>

                    <div v-if="buttonLoading" class="mt-2 text-right">
                        <div class="spinner"></div>
                    </div>
                </div>
            </div>
        </template>
    </list-group-item>
</template>

<script>
    import ListGroupItem from '../../ListGroupItem'

    export default {

        props: ['name', 'description', 'icon', 'accountName', 'connected', 'buttonLoading', 'loading'],

        data() {
            return {
                // name: "Stripe",
                // description: "Connect your Stripe account so you can receive payouts for plugin purchases.",
                // icon: '/craftnetresources/id/dist/images/stripe.svg',
                // accountName: "My account name",
                // connected: false,
                // buttonLoading: true,
                // loading: false,
            }
        },

        components: {
            ListGroupItem
        }

    }
</script>

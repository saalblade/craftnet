<template>
    <div>
        <h1>Buy License</h1>
        <div class="card mb-4">
            <div class="card-body">
                <ol>
                    <li v-if="!handle">Select a plugin edition</li>
                    <li>Add the <code v-if="handle">{{handle}}</code> plugin edition to the cart</li>
                    <li>Redirect to the cart</li>
                </ol>
            </div>
        </div>
        <div class="card mb-4">
            <div class="card-body">
                <h2>Select a plugin edition</h2>
                <p>Dropdown with add to cart button.</p>
                <ul class="mb-4">
                    <li>Categories: {{categories.length}}</li>
                    <li>Plugins: {{plugins.length}}</li>
                </ul>

                <div class="buttons mb-4">
                    <input type="button" class="btn btn-secondary" value="Get Plugin Store Data" @click="getPluginStoreData" />
                </div>

                <select v-model="selectedPlugin" class="mb-4">
                    <option value="">Select a plugin</option>
                    <option v-for="plugin in plugins" :value="plugin.handle">{{ plugin.name }}</option>
                </select>

                <p>Selected Plugin: {{ selectedPlugin }}</p>

                <div class="buttons">
                    <input type="button" class="btn btn-primary" value="Add to cart" />
                </div>
            </div>
        </div>
        <div class="card mb-4">
            <div class="card-body">
                <h2>Cart</h2>
                <p>Cart with checkout button.</p>
                <cart></cart>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <h2>Checkout</h2>

                <ul>
                    <li>Identity = Craft ID Account</li>
                    <li>Payment Method</li>
                </ul>
            </div>
        </div>
    </div>
</template>

<script>
    import {mapState, mapActions} from 'vuex'
    import Cart from '../components/Cart'

    export default {

        data() {
            return {
                selectedPlugin: '',
            }
        },

        components: {
            Cart
        },

        computed: {

            ...mapState({
                categories: state => state.pluginstore.categories,
                plugins: state => state.pluginstore.plugins,
            }),

            handle() {
                return this.$route.params.handle
            }
        },

        methods: {
            ...mapActions({
                getPluginStoreData: 'getPluginStoreData',
            })
        }

    }
</script>

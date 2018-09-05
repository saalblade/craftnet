<template>
    <div>
        <h1>Buy License</h1>

        <div class="card mb-4">
            <div class="card-body">
                <h2>Plugin License</h2>

                <div class="flex items-center mb-4">
                    <div>
                        <select v-model="selectedPluginHandle" placeholder="Select a plugin" class="w-full">
                            <option value="">Select a plugin</option>
                            <option v-for="(plugin, key) in commercialPlugins" :key="key" :value="plugin.handle">
                                {{ plugin.name }} (Starting at ${{plugin.editions[0].price}})
                            </option>
                        </select>
                        <!--<el-select v-model="selectedPluginHandle" placeholder="Select a plugin" class="w-full" filterable>
                            <el-option value="">Select a plugin</el-option>
                            <el-option v-for="(plugin, key) in commercialPlugins" :key="key" :value="plugin.handle">
                                    {{ plugin.name }} (Starting at ${{plugin.editions[0].price}})
                            </el-option>
                        </el-select>-->
                    </div>

                    <div>
                        <div class="spinner" v-if="loading"></div>
                    </div>
                </div>

                <ul v-if="selectedPlugin" class="list-reset mb-4">
                   <li v-for="edition in selectedPlugin.editions" class="flex">
                       <input type="radio" checked="checked" :value="edition.handle" class="mt-1" v-model="pluginEditionHandle">
                       <div class="ml-2">
                           <h3>{{edition.name}}</h3>
                           <p>
                               ${{edition.price}} (License + 1 year of updates)<br />
                               <em>Then ${{edition.renewalPrice}}/year</em>
                           </p>
                       </div>
                   </li>
                </ul>

                <div class="buttons mb-4">
                    <input type="button" class="btn btn-primary"
                           :class="{disabled: !selectedPluginHandle}"
                           @click="addToCart(selectedPluginHandle)"
                           :disabled="!selectedPluginHandle" value="Add to cart"/>
                </div>

                <p>(Redirects to /buy-plugin/{{selectedPluginHandle}}/{{pluginEditionHandle}})</p>
            </div>
        </div>
    </div>
</template>

<script>
    import {mapState, mapGetters, mapActions} from 'vuex'

    export default {

        data() {
            return {
                loading: false,
                selectedPluginHandle: '',
                pluginEditionHandle: 'standard',
            }
        },

        computed: {

            ...mapState({
                plugins: state => state.pluginStore.plugins,
            }),

            ...mapGetters({
                getPluginByHandle: 'pluginStore/getPluginByHandle',
            }),

            handle() {
                return this.$route.params.handle
            },

            selectedPlugin() {
                return this.getPluginByHandle(this.selectedPluginHandle)
            },

            commercialPlugins() {
                return this.plugins.filter(plugin => {
                    if(plugin.editions[0].price > 0) {
                        return true
                    }

                    return false
                })
            }
        },

        methods: {

            ...mapActions({
                getPluginStoreData: 'pluginStore/getPluginStoreData',
            }),

            addToCart() {
                this.$router.push({path: '/buy-plugin/' + this.selectedPluginHandle + '/' + this.pluginEditionHandle})

                // const plugin = this.getPluginByHandle(this.selectedPluginHandle)
                // const pluginEditionHandle = this.pluginEditionHandle
                // this.$store.dispatch('cart/addToCart', {plugin, pluginEditionHandle})
                //     .then(response => {
                //         this.$router.push({path: '/cart'})
                //     })
            }

        },

        mounted() {
            if (this.plugins.length === 0) {
                this.loading = true

                this.getPluginStoreData()
                    .then(response => {
                        this.loading = false
                    })
                    .catch(response => {
                        this.loading = false
                    })
            }
        }

    }
</script>

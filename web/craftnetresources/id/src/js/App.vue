<template>
    <div id="app">
        <auth-manager ref="authManager"></auth-manager>
        <renew-licenses-modal v-if="$root.showRenewLicensesModal" :license="$root.renewLicense" @cancel="$root.showRenewLicensesModal = false" />

        <template v-if="$root.notification">
            <div id="notifications-wrapper" :class="{'hide': !$root.notification }">
                <div id="notifications">
                    <div class="notification" :class="$root.notification.type">{{ $root.notification.message }}</div>
                </div>
            </div>
        </template>

        <template v-if="$root.loading">
            <div class="text-center">
                <div class="spinner big mt-8"></div>
            </div>
        </template>

        <template v-else>
            <div class="app">
                <div class="header">
                    <div class="header-left">
                        <a id="sidebar-toggle" href="#" @click.prevent="toggleSidebar()">
                            <font-awesome-icon :icon="showingSidebar ? 'times' : 'bars'" />
                        </a>

                        <div class="header-brand">
                            <router-link to="/">Craft ID</router-link>
                        </div>
                    </div>

                    <div class="header-right ml-4">
                        <ul class="list-reset flex items-center">
                            <li class="block ml-6 cart-menu">
                                <router-link class="block" to="/cart">
                                    <font-awesome-icon icon="shopping-cart" />
                                    <div class="badge" :class="{invisible: !cartTotalItems}">{{cartTotalItems}}</div>
                                </router-link>
                            </li>
                            <li class="block ml-6 global-menu" v-on-clickaway="awayGlobalMenu">
                                <a class="block toggle" @click="globalMenuToggle">
                                    <font-awesome-icon icon="th" />
                                </a>

                                <div class="popover" :class="{hidden: !showingGlobalMenu}">
                                    <div>
                                        <p><router-link @click.native="showingGlobalMenu = false" to="/">Craft ID</router-link></p>
                                        <p><a :href="craftPluginsUrl" target="_blank">Craft Plugins</a></p>
                                    </div>

                                    <div class="popover-arrow"></div>
                                </div>
                            </li>
                            <li class="block ml-6 user-menu" v-on-clickaway="awayUserMenu">
                                <a class="block toggle" @click="userMenuToggle">
                                    <img :src="currentUser.photoUrl" />
                                </a>

                                <div class="popover" :class="{hidden: !showingUserMenu}">
                                    <div>
                                        {{currentUser.email}}
                                    </div>

                                    <hr>

                                    <ul class="list-reset">
                                        <li><router-link class="block py-1" @click.native="showingUserMenu = false" to="/account/billing">Billing</router-link></li>
                                        <li><router-link class="block py-1" @click.native="showingUserMenu = false" to="/account/settings">Account Settings</router-link></li>
                                    </ul>

                                    <hr>

                                    <div>
                                        <a href="/logout">Logout</a>
                                    </div>

                                    <div class="popover-arrow"></div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="flex-container">
                    <div class="sidebar" :class="{ 'showing-sidebar': showingSidebar }">
                        <h5>
                            <router-link @click.native="closeSidebar()" to="/account/licenses">
                                <font-awesome-icon icon="key" />
                                Licenses
                            </router-link>
                        </h5>
                        <ul>
                            <li><router-link @click.native="closeSidebar()" to="/account/licenses/cms">Craft CMS</router-link></li>
                            <li><router-link @click.native="closeSidebar()" to="/account/licenses/plugins">Plugins</router-link></li>
                            <li><router-link @click.native="closeSidebar()" to="/account/licenses/claim">Claim License</router-link></li>
                            <li><router-link @click.native="closeSidebar()" to="/buy">Buy License</router-link></li>
                        </ul>

                        <template v-if="userIsInGroup('developers')">
                            <h5>
                                <router-link @click.native="closeSidebar()" to="/developer">
                                    <font-awesome-icon icon="plug" />
                                    Developer
                                </router-link>
                            </h5>
                            <ul>
                                <li><router-link @click.native="closeSidebar()" to="/developer/plugins">Plugins</router-link></li>
                                <li><router-link @click.native="closeSidebar()" to="/developer/sales">Sales</router-link></li>
                                <li><router-link @click.native="closeSidebar()" to="/developer/profile">Profile</router-link></li>
                                <li><router-link @click.native="closeSidebar()" to="/developer/settings">Settings</router-link></li>
                            </ul>
                        </template>

                        <h5>
                            <router-link @click.native="closeSidebar()" to="/account">
                                <font-awesome-icon icon="user" />
                                Account
                            </router-link>
                        </h5>
                        <ul>
                            <li><router-link @click.native="closeSidebar()" to="/account/billing">Billing</router-link></li>
                            <li><router-link @click.native="closeSidebar()" to="/account/settings">Settings</router-link></li>
                        </ul>

                        <template v-if="userIsInGroup('staff') && currentUser.enableShowcaseFeatures">
                            <h5>
                                <a class="disabled" href="#">
                                    <font-awesome-icon icon="image" />
                                    Showcase
                                </a>
                            </h5>
                            <ul>
                                <li><a class="disabled" href="#">Activity</a></li>
                                <li><a class="disabled" href="#">Projects</a></li>
                                <li><a class="disabled" href="#">Agency Profile</a></li>
                            </ul>
                        </template>

                    </div>

                    <div class="main">
                        <div class="main-content">
                            <router-view :key="$route.path"></router-view>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>
</template>

<script>
    import {mapState, mapGetters} from 'vuex'
    import router from './router';
    import AuthManager from './components/AuthManager';
    import RenewLicensesModal from './components/renew-licenses/RenewLicensesModal';
    import { directive as onClickaway } from 'vue-clickaway';


    export default {

        directives: {
            onClickaway: onClickaway,
        },

        router,

        components: {
            AuthManager,
            RenewLicensesModal,
        },

        data() {
            return {
                showingSidebar: false,
                showingUserMenu: false,
                showingGlobalMenu: false,
            }
        },

        computed: {

            ...mapState({
                currentUser: state => state.account.currentUser,
            }),

            ...mapGetters({
                userIsInGroup: 'account/userIsInGroup',
                cartTotalItems: 'cart/cartTotalItems',
            }),

            craftPluginsUrl() {
                return process.env.VUE_APP_CRAFT_PLUGINS_URL;
            }

        },

        methods: {

            /**
             * Toggles the sidebar.
             */
            toggleSidebar() {
                this.showingSidebar = !this.showingSidebar;
            },

            /**
             * Closes the sidebar.
             */
            closeSidebar() {
                this.showingSidebar = false;
            },

            /**
             * Click away from the user menu.
             */
            awayUserMenu: function() {
                if(this.showingUserMenu === true) {
                    this.showingUserMenu = false
                }
            },

            /**
             * Click away from the global menu.
             */
            awayGlobalMenu: function() {
                if(this.showingGlobalMenu === true) {
                    this.showingGlobalMenu = false
                }
            },

            /**
             * User menu toggle.
             */
            userMenuToggle() {
                this.showingUserMenu = !this.showingUserMenu
            },

            /**
             * Global menu toggle.
             */
            globalMenuToggle() {
                this.showingGlobalMenu = !this.showingGlobalMenu
            }

        },

        created() {
            this.$store.dispatch('craftId/getCraftIdData').then(() => {
                this.$root.loading = false;
            });

            if (window.stripeAccessToken) {
                this.$store.dispatch('account/getStripeAccount')
                    .then(() => {
                        this.$root.stripeAccountLoading = false;
                    }, () => {
                        this.$root.stripeAccountLoading = false;
                    });
            } else {
                this.$root.stripeAccountLoading = false;
            }

            this.$store.dispatch('account/getInvoices')
                .then(() => {
                    this.$root.invoicesLoading = false;
                })
                .catch(() => {
                    this.$root.invoicesLoading = false;
                });

            if(window.sessionNotice) {
                this.$root.displayNotice(window.sessionNotice);
            }

            if(window.sessionError) {
                this.$root.displayError(window.sessionError);
            }
        }
    }
</script>

<style lang="scss">
    @import './../sass/app.scss';
</style>
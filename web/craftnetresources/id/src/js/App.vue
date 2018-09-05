<template>
    <div>
        <auth-manager ref="authManager"></auth-manager>
        <renew-licenses-modal v-if="$root.showRenewLicensesModal" :license="$root.renewLicense" @cancel="$root.showRenewLicensesModal = false" />

        <template v-if="notification">
            <div id="notifications-wrapper" :class="{'hide': !notification }">
                <div id="notifications">
                    <div class="notification" :class="notification.type">{{ notification.message }}</div>
                </div>
            </div>
        </template>

        <template v-if="loading">
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

                    <div class="header-right">
                        <ul>
                            <li class="global-menu" v-on-clickaway="awayGlobalMenu">
                                <a class="toggle" @click="globalMenuToggle">
                                    <font-awesome-icon icon="th" />
                                </a>

                                <div class="popover" :class="{hidden: !showingGlobalMenu}">
                                    <div>
                                        <p><a href="http://localhost:3000/" target="_blank">Craft Plugins</a></p>
                                        <p><router-link @click.native="showingGlobalMenu = false" to="/">Craft ID</router-link></p>
                                    </div>

                                    <div class="popover-arrow"></div>
                                </div>
                            </li>
                            <li class="user-menu" v-on-clickaway="awayUserMenu">
                                <a class="toggle" @click="userMenuToggle">
                                    <img :src="currentUser.photoUrl" />
                                </a>

                                <div class="popover" :class="{hidden: !showingUserMenu}">
                                    <div>
                                        {{currentUser.email}}
                                    </div>

                                    <div>
                                        <router-link @click.native="showingUserMenu = false" to="/account/settings">Account Settings</router-link>
                                    </div>

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
                                <li><router-link @click.native="closeSidebar()" to="/account/profile">Profile</router-link></li>
                                <li><router-link @click.native="closeSidebar()" to="/developer/settings">Settings</router-link></li>
                            </ul>
                        </template>

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
                        <h5>
                            <router-link @click.native="closeSidebar()" to="/buy">
                                <font-awesome-icon icon="bug" />
                                Tests
                            </router-link>
                        </h5>
                        <ul>
                            <li><router-link @click.native="closeSidebar()" to="/buy">Buy License</router-link></li>
                            <li><router-link @click.native="closeSidebar()" to="/cart">Cart</router-link></li>
                        </ul>
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

        props: ['notification', 'loading'],

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
            }),

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

            awayUserMenu: function(event) {
                if(this.showingUserMenu === true) {
                    console.log('away', this.showingUserMenu, event.target)

                    this.showingUserMenu = false
                }
            },

            awayGlobalMenu: function(event) {
                if(this.showingGlobalMenu === true) {
                    console.log('away', this.showingGlobalMenu, event.target)

                    this.showingGlobalMenu = false
                }
            },

            userMenuToggle() {
                console.log('userMenuToggle');
                this.showingUserMenu = !this.showingUserMenu
            },

            globalMenuToggle() {
                console.log('userMenuToggle');
                this.showingGlobalMenu = !this.showingGlobalMenu
            }

        },

    }
</script>

<style lang="scss">
    @import './../sass/app.scss';
</style>
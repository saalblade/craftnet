<template>
    <div>
        <auth-manager ref="authManager"></auth-manager>

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
                <div class="header-container">
                    <div class="header">
                        <div class="actions-left">
                            <a id="sidebar-toggle" href="#" @click.prevent="toggleSidebar()"><i class="fas fa-bars"></i></a>
                        </div>
                        <router-link class="navbar-brand" to="/">Craft ID</router-link>

                        <div class="actions-right">
                            <ul>
                                <li><a href="/logout">Logout</a></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="content-container">
                    <div class="content">
                        <div id="sidebar" :class="{ 'showing-sidebar': showingSidebar }">
                            <div class="sidenav">
                                <h5><router-link @click.native="closeSidebar()" to="/account/licenses"><i class="fa fa-key"></i> Licenses</router-link></h5>
                                <ul>
                                    <li><router-link @click.native="closeSidebar()" to="/account/licenses/craft">Craft CMS</router-link></li>
                                    <li><router-link @click.native="closeSidebar()" to="/account/licenses/plugins">Plugins</router-link></li>
                                    <li><router-link @click.native="closeSidebar()" to="/account/licenses/claim">Claim License</router-link></li>
                                    <li v-if="enableCommercialFeatures"><router-link @click.native="closeSidebar()" to="/account/licenses/renew">Renew Licenses ({{licenses.length}})</router-link></li>
                                </ul>

                                <template v-if="userIsInGroup('developers')">
                                    <h5><router-link @click.native="closeSidebar()" to="/developer"><i class="fa fa-plug"></i> Developer</router-link></h5>
                                    <ul>
                                        <li><router-link @click.native="closeSidebar()" to="/developer/plugins">Plugins</router-link></li>

                                        <template v-if="userIsInGroup('staff') && enableCommercialFeatures">
                                            <li><router-link @click.native="closeSidebar()" to="/developer/sales">Sales</router-link></li>
                                            <li><router-link @click.native="closeSidebar()" to="/developer/settings">Settings</router-link></li>
                                        </template>
                                    </ul>
                                </template>

                                <template v-if="userIsInGroup('staff') && currentUser.enableShowcaseFeatures">
                                    <h5><a class="disabled" href="#"><i class="fa fa-image"></i> Showcase</a></h5>
                                    <ul>
                                        <li><a class="disabled" href="#">Activity</a></li>
                                        <li><a class="disabled" href="#">Projects</a></li>
                                        <li><a class="disabled" href="#">Agency Profile</a></li>
                                    </ul>
                                </template>

                                <h5><router-link @click.native="closeSidebar()" to="/account"><i class="fas fa-user"></i> Account</router-link></h5>
                                <ul>
                                    <li><router-link @click.native="closeSidebar()" to="/account/billing">Billing</router-link></li>
                                    <li><router-link @click.native="closeSidebar()" to="/account/profile">Profile</router-link></li>
                                    <li><router-link @click.native="closeSidebar()" to="/account/settings">Settings</router-link></li>
                                </ul>
                            </div>
                        </div>

                        <div class="main">
                            <router-view :key="$route.path"></router-view>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>
</template>

<script>
    import AuthManager from './components/AuthManager';
    import router from './router';
    import {mapGetters} from 'vuex'

    export default {

        router,

        components: {
            AuthManager
        },

        props: ['notification', 'loading'],

        data() {
            return {
                showingSidebar: false,
            }
        },

        computed: {

            ...mapGetters({
                currentUser: 'currentUser',
                userIsInGroup: 'userIsInGroup',
                licenses: 'licenses',
                enableCommercialFeatures: 'enableCommercialFeatures',
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
            }

        },

    }
</script>

<style lang="scss">
    @import './../sass/app.scss';
</style>
<template>
    <div>
        <auth-manager ref="authManager"></auth-manager>

        <template v-if="notification">
            <div id="notifications-wrapper" :class="{'hide': !notification }">
                <div id="notifications">
                    <div class="notification bg-green" :class="'bg-'+notification.type">{{ notification.message }}</div>

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
                    <router-link class="navbar-brand" to="/">Craft ID</router-link>

                    <ul>
                        <li><a href="/logout">Logout</a></li>
                    </ul>
                </div>

                <div class="content">
                    <div id="sidebar">

                        <div class="sidenav">
                            <template v-if="userIsInGroup('staff')">
                                <h5>Account</h5>
                                <ul>
                                    <li><router-link to="/account/licenses"><i class="fa fa-key"></i> Licenses</router-link></li>
                                    <li><router-link to="/account/billing"><i class="fa fa-file-alt"></i> Billing</router-link></li>
                                    <li><router-link to="/account/profile"><i class="fa fa-link"></i> Profile</router-link></li>
                                    <li><router-link to="/account/settings"><i class="fa fa-cog"></i> Settings</router-link></li>
                                </ul>

                                <template v-if="userIsInGroup('developers')">
                                    <h5>Developer</h5>
                                    <ul>
                                        <li><router-link to="/developer/plugins"><i class="fa fa-plug"></i> Plugins</router-link></li>
                                        <li><router-link to="/developer/sales"><i class="fa fa-dollar-sign"></i> Sales</router-link></li>
                                    </ul>
                                </template>

                                <template v-if="currentUser.enableShowcaseFeatures">
                                    <h5>Showcase</h5>
                                    <ul>
                                        <li><a class="disabled" href="#"><i class="fa fa-heart"></i> Activity</a></li>
                                        <li><a class="disabled" href="#"><i class="fa fa-image"></i> Projects</a></li>
                                        <li><a class="disabled" href="#"><i class="fa fa-industry"></i> Agency Profile</a></li>
                                    </ul>
                                </template>
                            </template>

                            <template v-else>
                                <h5>Account</h5>
                                <ul>
                                    <li v-if="userIsInGroup('developers')"><router-link to="/developer/plugins"><i class="fa fa-plug"></i> Plugins</router-link></li>
                                    <li v-if="userIsInGroup('developers')"><router-link to="/account/profile"><i class="fa fa-link"></i> Profile</router-link></li>
                                    <li><router-link to="/account/settings"><i class="fa fa-cog"></i> Settings</router-link></li>
                                </ul>
                            </template>
                        </div>
                    </div>

                    <div class="main">
                        <router-view></router-view>
                    </div>
                </div>
            </div>
        </template>
    </div>
</template>

<script>
    import AuthManager from './components/AuthManager';
    import router from './router';
    import { mapGetters } from 'vuex'

    export default {

        router,

        components: {
            AuthManager
        },

        props: ['notification', 'loading'],

        computed: {

            ...mapGetters({
                currentUser: 'currentUser',
                userIsInGroup: 'userIsInGroup',
            }),

        }

    }
</script>

<style lang="scss">
    @import './../sass/app.scss';
</style>
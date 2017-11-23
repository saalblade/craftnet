<template>
    <div>
        <template v-if="notification">
            <div id="notifications-wrapper" :class="{'hide': !notification }">
                <div id="notifications">
                    <div class="notification bg-success" :class="'bg-'+notification.type">{{ notification.message }}</div>

                </div>
            </div>
        </template>

        <nav class="navbar navbar-expand navbar-light bg-light mb-5">
            <div class="container">
                <router-link class="navbar-brand ml-3" to="/">Craft ID</router-link>

                <div class="collapse navbar-collapse">
                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="/logout">Logout</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <template v-if="loading">
            <div class="text-center">
                <div class="spinner big mt-5"></div>
            </div>
        </template>

        <template v-else>
            <div class="container">
                <div class="row">
                    <div class="col-md-3">
                        <template v-if="userIsInGroup('staff')">
                            <h5>Account</h5>
                            <ul class="nav nav-pills flex-column">
                                <li class="nav-item"><router-link class="nav-link" to="/account/licenses"><i class="fa fa-key"></i> Licenses</router-link></li>
                                <li class="nav-item"><router-link class="nav-link" to="/account/billing"><i class="fa fa-file-text-o"></i> Billing</router-link></li>
                                <li class="nav-item"><router-link class="nav-link" to="/account/profile"><i class="fa fa-link"></i> Profile</router-link></li>
                                <li class="nav-item"><router-link class="nav-link" to="/account/settings"><i class="fa fa-cog"></i> Settings</router-link></li>
                            </ul>

                            <template v-if="userIsInGroup('developers')">
                                <h5 class="mt-3">Developer</h5>
                                <ul class="nav nav-pills flex-column">
                                    <li class="nav-item"><router-link class="nav-link" to="/developer/plugins"><i class="fa fa-plug"></i> Plugins</router-link></li>
                                    <li class="nav-item"><router-link class="nav-link" to="/developer/customers"><i class="fa fa-group"></i> Customers</router-link></li>
                                    <li class="nav-item"><router-link class="nav-link" to="/developer/payments"><i class="fa fa-credit-card"></i> Payments</router-link></li>
                                    <li class="nav-item"><router-link class="nav-link" to="/developer/payouts"><i class="fa fa-dollar"></i> Payouts</router-link></li>
                                </ul>
                            </template>

                            <template v-if="currentUser.enableShowcaseFeatures">
                                <h5 class="mt-3">Showcase</h5>
                                <ul class="nav nav-pills flex-column">
                                    <li class="nav-item"><a class="nav-link disabled" href="#"><i class="fa fa-heart"></i> Activity</a></li>
                                    <li class="nav-item"><a class="nav-link disabled" href="#"><i class="fa fa-image"></i> Projects</a></li>
                                    <li class="nav-item"><a class="nav-link disabled" href="#"><i class="fa fa-industry"></i> Agency Profile</a></li>
                                </ul>
                            </template>
                        </template>

                        <template v-else>
                            <h5>Account</h5>
                            <ul class="nav nav-pills flex-column">
                                <li v-if="userIsInGroup('developers')" class="nav-item"><router-link class="nav-link" to="/developer/plugins"><i class="fa fa-plug"></i> Plugins</router-link></li>
                                <li v-if="userIsInGroup('developers')" class="nav-item"><router-link class="nav-link" to="/account/profile"><i class="fa fa-link"></i> Profile</router-link></li>
                                <li class="nav-item"><router-link class="nav-link" to="/account/settings"><i class="fa fa-cog"></i> Settings</router-link></li>
                            </ul>
                        </template>
                    </div>

                    <div class="col-md-9">
                        <div class="content">
                            <router-view></router-view>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>
</template>

<script>
    import router from './router';
    import { mapGetters } from 'vuex'

    export default {

        router,

        props: ['notification', 'loading'],

        computed: {

            ...mapGetters({
                currentUser: 'currentUser',
                userIsInGroup: 'userIsInGroup',
            }),

        }

    }
</script>

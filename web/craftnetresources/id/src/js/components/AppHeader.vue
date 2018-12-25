<template>
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
                        <div class="cart-badge" :class="{invisible: !cartTotalItems}">{{cartTotalItems}}</div>
                    </router-link>
                </li>
                <li class="block ml-6 global-menu" v-on-clickaway="awayGlobalMenu">
                    <a class="block toggle" @click="globalMenuToggle">
                        <font-awesome-icon icon="th" />
                    </a>

                    <div class="popover" :class="{hidden: !showingGlobalMenu}">
                        <div>
                            <p><router-link @click.native="showingGlobalMenu = false" to="/">Craft ID</router-link></p>
                            <p><a :href="craftPluginsUrl()">Craft Plugins</a></p>
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
</template>

<script>
    import {mapState, mapGetters} from 'vuex'
    import helpers from '../mixins/helpers'
    import { directive as onClickaway } from 'vue-clickaway';

    export default {

        mixins: [helpers],

        directives: {
            onClickaway: onClickaway,
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

        },

        methods: {

            /**
             * Toggles the sidebar.
             */
            toggleSidebar() {
                this.showingSidebar = !this.showingSidebar;
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

        }

    }
</script>
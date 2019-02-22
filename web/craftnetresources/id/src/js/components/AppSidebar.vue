<template>
    <div class="sidebar" :class="{ 'showing-sidebar': showingSidebar }">
        <h5>
            <router-link @click.native="$emit('closeSidebar')" to="/licenses">
                <icon icon="key" />
                Licenses
            </router-link>
        </h5>
        <ul>
            <li><router-link @click.native="$emit('closeSidebar')" to="/licenses/cms">Craft CMS</router-link></li>
            <li><router-link @click.native="$emit('closeSidebar')" to="/licenses/plugins">Plugins</router-link></li>
            <li><router-link @click.native="$emit('closeSidebar')" to="/licenses/claim">Claim License</router-link></li>
        </ul>

        <template v-if="userIsInGroup('developers')">
            <h5>
                <router-link @click.native="$emit('closeSidebar')" to="/developer">
                    <icon icon="plug" />
                    Developer
                </router-link>
            </h5>
            <ul>
                <li><router-link @click.native="$emit('closeSidebar')" to="/developer/plugins">Plugins</router-link></li>
                <li><router-link @click.native="$emit('closeSidebar')" to="/developer/sales">Sales</router-link></li>
                <li><router-link @click.native="$emit('closeSidebar')" to="/developer/profile">Profile</router-link></li>
                <li><router-link @click.native="$emit('closeSidebar')" to="/developer/settings">Settings</router-link></li>
            </ul>
        </template>

        <template v-if="userIsInGroup('staff') && currentUser.enableShowcaseFeatures">
            <h5>
                <a class="disabled" href="#">
                    <icon icon="image" />
                    Showcase
                </a>
            </h5>
            <ul>
                <li><a class="disabled" href="#">Activity</a></li>
                <li><a class="disabled" href="#">Projects</a></li>
                <li><a class="disabled" href="#">Agency Profile</a></li>
            </ul>
        </template>

        <template v-if="currentUser.enablePartnerFeatures">
            <h5>
                <router-link @click.native="$emit('closeSidebar')" to="/partner">
                    <icon icon="handshake" />
                    Partner
                </router-link>
            </h5>
            <ul>
                <li><router-link @click.native="$emit('closeSidebar')" to="/partner/overview">Overview</router-link></li>
                <li><router-link @click.native="$emit('closeSidebar')" to="/partner/profile">Profile</router-link></li>
            </ul>
        </template>

        <h5>
            <router-link @click.native="$emit('closeSidebar')" to="/account">
                <icon icon="user" />
                Account
            </router-link>
        </h5>
        <ul>
            <li><router-link @click.native="$emit('closeSidebar')" to="/account/billing">Billing</router-link></li>
            <li><router-link @click.native="$emit('closeSidebar')" to="/account/settings">Settings</router-link></li>
        </ul>
    </div>
</template>

<script>
    import {mapState, mapGetters} from 'vuex'

    export default {
        props: ['showingSidebar'],

        computed: {
            ...mapState({
                currentUser: state => state.account.currentUser,
            }),

            ...mapGetters({
                userIsInGroup: 'account/userIsInGroup',
            }),
        },
    }
</script>
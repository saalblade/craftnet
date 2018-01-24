<template>
    <div>
        <ul class="nav-pills mb-4">
            <li><router-link to="/developer/plugins" exact>Your Plugins</router-link></li>
            <li><router-link to="/developer/plugins/submit">Add a plugin</router-link></li>
        </ul>

        <router-view :key="$route.fullPath"></router-view>
    </div>
</template>

<script>
    import { mapGetters } from 'vuex'

    export default {

        computed: {

            ...mapGetters({
                userIsInGroup: 'userIsInGroup',
            }),

        }

    }
</script>
<template>
    <div>
        <ul class="nav nav-pills mb-4">
            <li class="nav-item"><router-link class="nav-link" to="/developer/plugins" exact>Your Plugins</router-link></li>
            <li v-if="userIsInGroup('staff')" class="nav-item"><router-link class="nav-link" to="/developer/plugins/submit">Add a plugin</router-link></li>
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
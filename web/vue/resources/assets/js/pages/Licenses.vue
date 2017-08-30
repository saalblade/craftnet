<template>
    <div>

        <ul class="nav nav-pills mb-4">
            <li class="nav-item"><router-link class="nav-link" to="/licenses/craft" exact>Craft CMS</router-link></li>
            <li class="nav-item"><router-link class="nav-link" to="/licenses/plugins" exact>Plugins</router-link></li>
            <li class="nav-item"><router-link class="nav-link" to="/licenses/claim" exact>Claim License</router-link></li>
        </ul>

        <router-view></router-view>

    </div>
</template>

<script>
    export default {
    }
</script>

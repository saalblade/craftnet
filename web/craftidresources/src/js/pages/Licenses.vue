<template>
    <div>
        <ul class="nav-pills mb-4">
            <li><router-link to="/account/licenses/craft" exact>Craft CMS</router-link></li>
            <li><router-link to="/account/licenses/plugins" exact>Plugins</router-link></li>
            <li><router-link to="/account/licenses/claim" exact>Claim License</router-link></li>
        </ul>

        <router-view></router-view>
    </div>
</template>

<script>
    export default {
    }
</script>

<template>
    <div class="app">
        <app-header></app-header>

        <div class="flex-container">

            <template v-if="!$route.meta.layout || $route.meta.layout !== 'no-sidebar'">
                <app-sidebar></app-sidebar>
            </template>

            <div class="main">
                <div class="main-content">
                    <router-view :key="$route.path"></router-view>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import AppHeader from './AppHeader';
    import AppSidebar from './AppSidebar';

    export default {

        components: {
            AppHeader,
            AppSidebar,
        }

    }
</script>
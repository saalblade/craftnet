<template>
    <div class="app">
        <app-header></app-header>

        <div class="flex-container">
            <app-sidebar></app-sidebar>

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
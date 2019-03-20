<template>
    <div class="site">
        <app-header :showingSidebar="false"></app-header>

        <div class="site-main flex-1 flex">
            <div class="flex flex-1">
                <div class="flex-1 bg-blue text-white justify-center items-center flex p-8">
                    <ul class="features list-reset">
                        <li>
                            <icon icon="key" size="2xl" />
                            Manage your licenses
                        </li>

                        <li>
                            <icon icon="question-circle" size="2xl" />
                            Claim licenses
                        </li>
                        <li>
                            <icon icon="plug" size="2xl" />
                            Publish plugins to the Plugin Store
                        </li>

                        <li>
                            <icon icon="handshake" size="2xl" />
                            Manage your partner profile
                        </li>
                    </ul>
                </div>

                <div class="flex-1 bg-grey-lighter justify-center items-center flex p-8">
                    <div class="w-full max-w-sm">
                        <router-view :key="$route.path"></router-view>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import AppHeader from '../AppHeader'

    export default {
        components: {
            AppHeader
        }
    }
</script>

<style lang="scss">
    .site {
        @apply .fixed .pin .flex .flex-col;

        .header {
            @apply .absolute .w-full .border-b-0;
            position: absolute;
            border-bottom: 0;

            &-left {
                #sidebar-toggle {
                    @apply .hidden;
                }
            }
        }

        .header-brand {
            a {
                @apply .text-white;
                color: #fff !important;
            }
        }
    }
</style>
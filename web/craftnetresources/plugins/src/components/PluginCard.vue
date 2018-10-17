<template>
    <div>
        <router-link v-if="plugin" class="plugin-card" :to="'/'+plugin.handle">
            <div class="plugin-icon">
                <img v-if="plugin.iconUrl" :src="plugin.iconUrl" />
                <img v-else :src="defaultPluginSvg" />
            </div>

            <div class="details">
                <div class="name">
                    <div class="truncate">
                        <div class="truncate-in">{{ plugin.name }}</div>
                    </div>
                </div>
                <div class="description" v-shave="{ height: 45 }">{{ plugin.shortDescription }}</div>
                <div class="price">
                    <template v-if="plugin.editions[0].price != null && plugin.editions[0].price !== '0.00'">
                        {{ plugin.editions[0].price|currency }}
                    </template>
                    <template v-else>
                        Free
                    </template>
                </div>
            </div>
        </router-link>
    </div>
</template>

<script>
    export default {

        props: ['plugin'],

        computed: {
            defaultPluginSvg() {
                return null;
                // return window.defaultPluginSvg;
            }
        }

    }
</script>

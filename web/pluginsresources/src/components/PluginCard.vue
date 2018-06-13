<template>
    <div>
        <router-link v-if="plugin" class="plugin-card" :to="'/plugin/'+plugin.id">
            <div class="plugin-icon">
                <img v-if="plugin.iconUrl" :src="plugin.iconUrl" />
                <img v-else :src="defaultPluginSvg" />
            </div>

            <div class="details">
                <div class="name">{{ plugin.name }}</div>
                <div class="description">{{ plugin.shortDescription.substr(0, 60) }}…</div>
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

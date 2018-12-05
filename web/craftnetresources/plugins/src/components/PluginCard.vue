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
                    <template v-if="priceRange.min !== priceRange.max">
                        <template v-if="priceRange.min > 0">
                            {{priceRange.min|currency}}
                        </template>
                        <template v-else>
                            Free
                        </template>
                        -
                        {{priceRange.max|currency}}
                    </template>
                    <template v-else>
                        <template v-if="priceRange.min > 0">
                            {{priceRange.min|currency}}
                        </template>
                        <template v-else>
                            Free
                        </template>
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
            },

            priceRange() {
                return this.getPriceRange(this.plugin.editions)
            }
        },

        methods: {

            getPriceRange(editions) {
                let min = null;
                let max = null;

                for(let i = 0; i < editions.length; i++) {
                    const edition = editions[i];
                    const price = parseInt(edition.price)

                    if(min === null) {
                        min = price;
                    }

                    if(max === null) {
                        max = price;
                    }

                    if(price < min) {
                        min = price
                    }

                    if(price > max) {
                        max = price
                    }
                }

                return {
                    min,
                    max
                }
            }

        }

    }
</script>

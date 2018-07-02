<template>
    <div>
        <!--<div v-if="plugin.screenshotUrls.length > 0" class="plugin-carousel">
            <div class="image">
                <img :src="plugin.screenshotUrls[0]" />
            </div>
        </div>-->

        <div v-if="plugin.screenshotUrls.length > 0" class="carousel-wrapper">
            <div ref="screenshotModal" class="carousel">
                <div class="swiper-container swiper-container-horizontal">
                    <div v-swiper:mySwiper="swiperOption" ref="swiper">
                        <div class="swiper-wrapper">
                            <div class="swiper-slide" v-for="(imageUrl, key) in plugin.screenshotUrls" :key="key">
                                <div class="screenshot">
                                    <img :src="imageUrl" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="swiper-pagination" slot="pagination"></div>
            </div>
        </div>

        <!--<div v-if="plugin.screenshotUrls.length > 0" class="screenshots">
            <a v-for="(screenshotUrl, screenshotKey) in plugin.screenshotUrls" @click="zoomScreenshot(screenshotKey)">
                <img :src="screenshotUrl" />
            </a>
        </div>-->
    </div>
</template>

<script>
    export default {

        props: ['plugin'],

        computed: {

            swiperOption() {
                return {
                    initialSlide: 0,
                    loop: true,
                    // navigation: {
                    //     nextEl: '.swiper-button-next',
                    //     prevEl: '.swiper-button-prev'
                    // },
                    pagination: {
                        el: '.swiper-pagination',
                        clickable: true
                    },
                    keyboard: true
                }
            }

        }

    }
</script>
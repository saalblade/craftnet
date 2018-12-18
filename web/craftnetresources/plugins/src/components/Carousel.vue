<template>
        <div v-if="images.length > 0" ref="carousel" class="carousel">
            <div v-swiper="swiperOption" :instanceName="identifier" ref="swiper">
                <div class="swiper-wrapper">
                    <div class="swiper-slide" v-for="(imageUrl, key) in images" :key="key">
                        <a class="screenshot" @click="zoomScreenshot(key)">
                            <img :src="imageUrl" />
                        </a>
                    </div>
                </div>
            </div>

            <div :class="'swiper-pagination swiper-pagination-' + identifier" slot="pagination"></div>
        </div>
</template>

<script>
    export default {

        props: ['identifier', 'inline', 'images', 'initialSlide'],

        data() {
            return {
                ratio: '4:3'
            }
        },

        computed: {

            swiperOption() {
                return {
                    initialSlide: 0,
                    loop: true,
                    pagination: {
                        el: '.swiper-pagination-' + this.identifier,
                        clickable: true
                    },
                    keyboard: true
                }
            },

            swiper() {
                return this.$refs.swiper.swiper
            },

        },

        methods: {

            zoomScreenshot(key) {
                this.$store.commit('app/updateScreenshotModalImages', this.images)
                this.$store.commit('app/toggleScreenshotModal')
            },

            handleResize() {
                if (this.images.length === 0) {
                    return
                }

                const ratio = this.ratio.split(':')
                const ratioWidth = ratio[0]
                const ratioHeight = ratio[1]
                const $carousel = this.$refs.carousel
                const carouselWidth = $carousel.offsetWidth
                const carouselHeight = $carousel.offsetHeight
                let imageElements = $carousel.getElementsByTagName("img")
                let maxHeight

                if (this.inline) {
                    maxHeight = carouselWidth * ratioHeight / ratioWidth
                } else {
                    if (carouselWidth > carouselHeight) {
                        maxHeight = carouselWidth * ratioHeight / ratioWidth
                    } else {
                        maxHeight = carouselHeight * ratioWidth / ratioHeight
                    }

                    if (carouselHeight > 0 && maxHeight > carouselHeight) {
                        maxHeight = carouselHeight
                    }
                }

                for (let i = 0; i < imageElements.length; i++) {
                    let imageElement = imageElements[i]
                    imageElement.style.maxHeight = maxHeight + 'px'
                }
            }

        },

        mounted: function () {
            window.addEventListener('resize', this.handleResize)
            this.handleResize()

            this.swiper.slideTo(this.initialSlide, 0)
        },

        beforeDestroy: function () {
            window.removeEventListener('resize', this.handleResize)
        }

    }
</script>

<style lang="scss">
    .carousel {
        @apply .relative;

        .swiper-container {
            @apply .flex;

            .swiper-wrapper {
                @apply .flex-1 .flex .w-auto .h-auto;

                .swiper-slide {
                    @apply .flex-1 .flex .text-center .justify-center .items-center;

                    .screenshot {
                        @apply .flex .justify-center .items-center;
                        box-sizing: border-box;

                        img {
                            @apply .max-w-full .max-h-full;
                        }
                    }
                }
            }
        }

        .swiper-pagination {
            @apply .w-full .relative .py-4 .mb-4;

            .swiper-pagination-bullet {
                @apply .mx-2;

                &.swiper-pagination-bullet-active {
                    @apply .bg-grey-darker;
                }
            }
        }
    }
</style>
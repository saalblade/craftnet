<template>
    <div class="screenshot-modal">
        <a class="close" @click="close">&times;</a>

        <carousel identifier="screenshot-modal-carousel" v-if="screenshotModalImages" :images="screenshotModalImages" :initial-slide="(screenshotModalImageKey)"></carousel>
    </div>
</template>

<script>
    import {mapState} from 'vuex'
    import Carousel from './Carousel'

    export default {

        components: {
            Carousel,
        },

        computed: {

            ...mapState({
                screenshotModalImages: state => state.app.screenshotModalImages,
                screenshotModalImageKey: state => state.app.screenshotModalImageKey,
            }),

            swiper() {
                return this.$refs.screenshotModalSwiper.swiper
            },

        },

        methods: {

            close() {
                this.$store.commit('app/toggleScreenshotModal')
            },

            handleEscapeKey(e) {
                if (e.keyCode === 27) {
                    this.close()
                }
            }

        },

        created() {
            window.addEventListener('keydown', this.handleEscapeKey)
        },

        beforeDestroy: function () {
            window.removeEventListener('keydown', this.handleEscapeKey)
        }


    }
</script>

<style lang="scss">
    .screenshot-modal {
        @apply .fixed .pin .bg-grey-lightest .z-50 .overflow-hidden;

        .close {
            @apply .inline-block .text-center .absolute .pin-t .pin-l .z-30;
            font-size: 30px;
            color: rgba(0, 0, 0, 0.6);
            padding: 14px 24px;
            line-height: 16px;

            &:hover {
                @apply .no-underline;
                color: rgba(0, 0, 0, 0.8);
            }
        }

        .carousel {
            @apply .absolute;
            top: 100px;
            right: 100px;
            bottom: 100px;
            left: 100px;
        }
    }
</style>
<template>
    <div class="screenshot-modal">
        <a class="close" @click="close">&times;</a>

        <carousel identifier="screenshot-modal-carousel" v-if="screenshotModalImages" :images="screenshotModalImages" :initial-slide="(screenshotModalImageKey + 1)"></carousel>
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
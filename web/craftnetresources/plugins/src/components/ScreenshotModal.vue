<template>
    <div class="screenshot-modal">
        <a class="close" @click="close">&times;</a>

        <carousel identifier="screenshot-modal-carousel" v-if="screenshotModalImages" :images="screenshotModalImages"></carousel>
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
            })

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
<template>
    <div class="spinner" :class="computedClass"></div>
</template>

<script>
    export default {
        props: ['big', 'cssClass'],

        computed: {
            computedClass() {
                let cssClass = {
                    big: typeof this.big !== 'undefined',
                }

                if (typeof this.cssClass === 'object') {
                    cssClass = {...cssClass, ...this.cssClass}
                } else if (typeof this.cssClass === 'string') {
                    let cssClassArray = this.cssClass.split(' ')

                    for (let i = 0; i < cssClassArray.length; i++) {
                        cssClass[cssClassArray[i]] = true
                    }
                }

                return cssClass;
            }
        }
    }
</script>

<style lang="scss">
    .spinner {
        @apply .inline-block .align-bottom;
        width: 24px;
        height:24px;
        background: url(~@/images/spinner.gif) no-repeat 50% 50%;
    }
    .spinner.big {
        width: 48px;
        height: 48px;
        background: url(~@/images/spinner_big.gif) no-repeat 50% 50%;
    }

    @media only screen and (-webkit-min-device-pixel-ratio: 1.5), only screen and (-moz-min-device-pixel-ratio: 1.5), only screen and (-o-min-device-pixel-ratio: 3 / 2), only screen and (min-device-pixel-ratio: 1.5), only screen and (min-resolution: 1.5dppx) {
        .spinner {
            background-image: url(~@/images/spinner_2x.gif);
            background-size: 20px;
        }
        .spinner.big {
            background-image: url(~@/images/spinner_big_2x.gif);
            background-size: 48px;
        }
    }
</style>

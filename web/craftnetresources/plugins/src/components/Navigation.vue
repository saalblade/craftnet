<template>
    <nav>
        <ul class="list-reset">
            <li class="global-menu" v-on-clickaway="globalMenuClickaway">
                <a class="block toggle" @click="globalMenuToggle">
                    <font-awesome-icon icon="th" />
                </a>
                <div class="navitation-popover" :class="{hidden: !showingGlobalMenu}">
                    <div>
                        <p><a :href="craftIdUrl">Craft ID</a></p>
                        <p><nuxt-link to="/" exact>Craft Plugins</nuxt-link></p>
                    </div>

                    <div class="navigation-popover-arrow"></div>
                </div>
            </li>
        </ul>
    </nav>
</template>

<script>
    import { directive as onClickaway } from 'vue-clickaway';
    import helpers from '../mixins/helpers'

    export default {

        mixins: [helpers],

        data() {
            return {
                showingGlobalMenu: false,
            }
        },

        directives: {
            onClickaway: onClickaway,
        },

        methods: {

            globalMenuToggle() {
                this.showingGlobalMenu = !this.showingGlobalMenu
            },

            globalMenuClickaway: function() {
                if(this.showingGlobalMenu === true) {
                    this.showingGlobalMenu = false
                }
            },
        }

    }
</script>

<style lang="scss">
    /* Navigation Popover */

    .navigation-popover {
        width: 200px;
        position: absolute;
        top: 0;
        right: 0;
        padding: 20px;
        background: white;
        box-shadow: 0px 5px 15px 0px rgba(0,0,0,0.3);
        z-index: 20;

        .navigation-popover-arrow {
            width: 50px;
            height: 16px;
            position: absolute;
            top: -16px;
            right: 5px;
            overflow: hidden;

            &::after {
                content: "";
                position: absolute;
                width: 16px;
                height: 16px;
                background: white;
                transform: translateX(-50%) translateY(-50%) rotate(45deg);
                top: 100%;
                left: 50%;
                box-shadow: 1px 1px 5px 0px rgba(0,0,0,0.3);
            }
        }
    }


    /* Global menu */

    .global-menu {
        @apply .relative;

        .toggle {
            @apply .text-lg .p-2 .rounded-full;
        }

        .navigation-popover {
            top: 48px;
            right: -13px;

            .navigation-popover-arrow {
                right: 5px;
            }
        }
    }
</style>
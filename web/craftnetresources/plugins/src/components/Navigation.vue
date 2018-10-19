<template>
    <nav>
        <ul class="list-reset">
            <li class="global-menu" v-on-clickaway="globalMenuClickaway">
                <a class="block toggle" @click="globalMenuToggle">
                    <font-awesome-icon icon="th" />
                </a>
                <div class="popover" :class="{hidden: !showingGlobalMenu}">
                    <div>
                        <p><a :href="craftIdUrl" target="_blank">Craft ID</a></p>
                        <p><nuxt-link to="/" exact>Craft Plugins</nuxt-link></p>
                    </div>

                    <div class="popover-arrow"></div>
                </div>
            </li>
        </ul>
    </nav>
</template>

<script>
    import { directive as onClickaway } from 'vue-clickaway';

    export default {

        data() {
            return {
                showingGlobalMenu: false,
            }
        },

        directives: {
            onClickaway: onClickaway,
        },

        computed: {

            craftIdUrl() {
                return process.env.craftIdUrl
            }

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

<template>
    <div>
        <h1>Payment</h1>
        <payment></payment>
    </div>
</template>

<script>
    import {mapState} from 'vuex'
    import Payment from '../components/Payment'

    export default {

        components: {
            Payment,
        },

        computed: {
            ...mapState({
                cart: state => state.cart.cart,
            }),
        },

        mounted() {
            // if(this.cart.items.length === 0) {
            //     this.$router.push({path: '/buy'});
            // }
        }
    }
</script>

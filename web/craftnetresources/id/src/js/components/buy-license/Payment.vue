<template>
    <div class="card">
        <div class="card-body">
            <h2>Payment</h2>

            <h3>Payment Method</h3>
            <h3>Coupon Code</h3>
            <h3>Billing</h3>

            <input type="button" class="btn btn-primary" :value="'Pay $'+cartTotal"/>
        </div>
    </div>
</template>


<script>
    import {mapGetters} from 'vuex'

    export default {

        computed: {

            ...mapGetters({
                cartTotal: 'cartTotal',
            }),

        },

    }
</script>

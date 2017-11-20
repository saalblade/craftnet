<template>
    <div>
        <div class="card mb-3">
            <div class="card-body">
                <billing-payment></billing-payment>
            </div>
        </div>
        <div class="card mb-3">
            <div class="card-body">
                <billing-infos></billing-infos>
            </div>
        </div>
        <div class="card mb-3">
            <div class="card-body">
                <billing-coupon></billing-coupon>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">Payment history</div>
            <div class="card-body">
                <table class="table">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Date</th>
                        <th>Payment Method</th>
                        <th>Amount</th>
                        <th>Receipt</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td>J5NZMPTA</td>
                        <td>2017/04/24</td>
                        <td>Visa ending in 7702</td>
                        <td>$299.00</td>
                        <td><a href="#">Receipt</a></td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script>
    import BillingPayment from '../components/BillingPayment'
    import BillingCoupon from '../components/BillingCoupon'
    import BillingInfos from '../components/BillingInfos'

    export default {

        components: {
            BillingPayment,
            BillingCoupon,
            BillingInfos,
        }

    }
</script>

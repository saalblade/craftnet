<template>
	<div class="row">
		<div class="col-sm-8">
			<h4>Coupon</h4>
			<p>You don’t have an active coupon.</p>
		</div>
		<div class="col-sm-4 text-right">
			<button @click="redeem()" class="btn btn-secondary btn-sm">
				<i class="fa fa-gift"></i>
				Redeem a coupon
			</button>
		</div>
	</div>
</template>

<script>
	export default {

	    methods: {

	        redeem() {
	            console.log('redeem');
			}

		}

	}
</script>
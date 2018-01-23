<template>
	<div class="flex">
		<div class="flex-1">
			<h4>Coupon</h4>
			<p>You don’t have an active coupon.</p>
		</div>
		<div>
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
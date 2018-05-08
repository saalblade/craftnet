<template>
    <div>
        <h6>Thank You!</h6>
        <p>Your order has been processed successfully.</p>
        <button @click="$emit('done')" class="btn btn-primary">Done</button>
    </div>
</template>

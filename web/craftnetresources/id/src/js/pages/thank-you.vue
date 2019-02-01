<template>
    <div class="thank-you">
        <div class="icon">
            <icon icon="check" />
        </div>
        <h1>Thank You!</h1>
        <p>Your order has been processed successfully.</p>
        <p><router-link class="nav-link" to="/">Continue to Craft ID</router-link></p>
    </div>
</template>

<style lang="scss">
    .thank-you {
        @apply .text-center .py-8;

        .icon {
            @apply .border .border-grey .border-solid .inline-block .rounded .rounded-full .relative .mb-8;
            width: 80px;
            height: 80px;

            svg {
                @apply text-5xl mt-8 mb-4 text-grey .absolute;
                top: calc(50% - 54px);
                left: calc(50% - 24px);
            }
        }

        h1 {
            @apply .mb-2;
        }
    }
</style>
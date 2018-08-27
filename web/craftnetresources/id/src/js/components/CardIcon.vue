<template>
    <div v-if="cardType" class="card-icon">
        <img :src="'/craftnetresources/id/dist/images/cards/'+cardType+'.svg'" height="20" />
    </div>
</template>

<script>
    export default {
        props: ['brand'],

        computed: {
            cardType() {
                switch(this.brand) {
                    case 'Visa':
                        return 'visa';
                    case 'American Express':
                        return 'amex';
                    case 'MasterCard':
                        return 'mastercard';
                    case 'Discover':
                        return 'discover';
                    case 'JCB':
                        return 'jcb';
                    case 'Diners Club':
                        return 'diners';
                }
            }
        }
    }
</script>

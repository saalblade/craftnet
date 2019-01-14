<template>
    <div>
        <select-field v-model="renew" :options="renewOptions" />

        <table class="table mb-2">
            <thead>
            <tr>
                <th>Item</th>
                <th>Renewal Date</th>
                <th>New Renewal Date</th>
                <th>Renewal Price</th>
                <th>Subtotal</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td>{{ license.plugin.name }}</td>
                <td>{{ license.expiresOn.date|moment('L') }}</td>
                <td>{{ newExpiresOn|moment('L') }}</td>
                <td>{{ license.edition.renewalPrice|currency }} <span class="text-grey-dark">&times;</span> {{ Math.round(newExpiresOn.diff(license.expiresOn.date, 'years', true) * 100) / 100 }} year(s)</td>
                <td>{{ newExpiresOn.diff(license.expiresOn.date, 'years', true) * license.edition.renewalPrice|currency }}</td>
            </tr>
            </tbody>
        </table>

        <input type="button" class="btn btn-secondary" @click="$emit('cancel')" value="Cancel" />
        <input type="button" class="btn btn-primary" @click="addToCart()" value="Add to cart" />
    </div>
</template>

<script>
    export default {

        props: ['license'],

        data() {
            return {
                renew: 1,
            }
        },

        computed: {

            renewOptions() {
                let options = [];

                for (let i = 1; i <= 5; i++) {
                    const date = this.$moment(this.license.expiresOn.date).add(i, 'year')
                    const formattedDate = this.$moment(date).format('L')
                    const label = "Extend updates until " + formattedDate

                    options.push({
                        label: label,
                        value: i,
                    })
                }

                return options;
            },

            newExpiresOn() {
                const expiresOn = this.$moment(this.license.expiresOn.date)
                return expiresOn.add(this.renew, 'years')
            },
        },

        methods: {

            addToCart() {
                const expiryDate = this.$moment(this.license.expiresOn.date).add(this.renew, 'year')
                const formattedExpiryDate = this.$moment(expiryDate).format('YYYY-MM-DD')

                const item = {
                    type: 'plugin-renewal',
                    licenseKey: this.license.key,
                    expiryDate: formattedExpiryDate,
                }

                this.$store.dispatch('cart/addToCart', [item])
                    .then(() => {
                        this.$router.push({path: '/cart'})
                        this.$emit('addToCart')
                    })
                    .catch(error => {
                        const errorMessage = error.response.data.errors && error.response.data.errors[0] && error.response.data.errors[0].message ? error.response.data.errors[0].message : 'Couldn’t add update to cart.';
                        this.$store.dispatch('app/displayError', errorMessage);
                    })
            },

        }

    }
</script>

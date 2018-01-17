<template>
    <div v-if="sale">
        <p><router-link class="nav-link" to="/developer/sales" exact>← Back to sales</router-link></p>

        <div class="card mb-3">
            <div class="card-body">
                <h3>{{ sale.amount|currency }}</h3>
                <p class="text-secondary">#SAL000{{sale.id}}</p>

                <hr>

                <div class="row">
                    <div class="col-6">
                        <dl>
                            <dt>Item</dt>
                            <dd>{{ sale.plugin.name }}</dd>
                            <dt>Type</dt>
                            <dd>{{ sale.type }}</dd>
                            <dt>Amount</dt>
                            <dd>{{ sale.amount|currency }}</dd>
                        </dl>
                    </div>
                    <div class="col-6">
                        <dl>
                            <dt>Sale ID</dt>
                            <dd>#SAL000{{ sale.id }}</dd>
                            <dt>Customer</dt>
                            <dd><router-link :to="'/developer/customers/'+sale.customer.id">{{ sale.customer.email }}</router-link></dd>
                            <dt>Date</dt>
                            <dd>{{ sale.date }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import { mapGetters } from 'vuex'

    export default {

        computed: {

            ...mapGetters({
                getSaleById: 'getSaleById',
            }),

            sale() {
                return this.getSaleById(this.$route.params.id);
            }

        }

    }
</script>

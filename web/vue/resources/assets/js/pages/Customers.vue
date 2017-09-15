<template>
    <div>

        <div class="form-group">
            <input class="form-control" id="searchQuery" name="searchQuery" type="text" placeholder="Search customers" v-model="searchQuery">
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Full Name</th>
                    <th>Email</th>
                </tr>
            </thead>
            <tbody>
            <tr v-for="customer in customersToRender">
                <td><router-link :to="'/developer/customers/'+customer.id">{{ customer.username }}</router-link></td>
                <td>{{ customer.fullName }}</td>
                <td>{{ customer.email }}</td>
            </tr>
            </tbody>
        </table>

    </div>
</template>

<script>
    import { mapGetters } from 'vuex'

    export default {
        name: 'customers',
        data () {
            return {
                searchQuery: '',
            }
        },

        computed: {
            ...mapGetters({
                customers: 'customers',
            }),

            customersToRender() {
                var searchQuery = this.searchQuery;
                return this.customers.filter(function(customer) {
                    if(customer) {
                        var searchQueryRegExp = new RegExp(searchQuery, 'gi');

                        if(customer.username.match(searchQueryRegExp)) {
                            return true;
                        }

                        if(customer.fullName.match(searchQueryRegExp)) {
                            return true;
                        }

                        if(customer.email.match(searchQueryRegExp)) {
                            return true;
                        }
                    }
                });
            },
        },
    }
</script>


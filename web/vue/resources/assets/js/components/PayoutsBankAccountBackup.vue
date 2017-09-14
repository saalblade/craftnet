<template>

    <div class="card mb-3">
        <div class="card-header"><i class="fa fa-institution"></i> Bank Account</div>
        <div class="card-body">


            <template v-if="stripeConnected">

                <table class="table" v-if="stripeBankAccounts.length > 0">
                    <tr v-for="stripeBankAccount,key in stripeBankAccounts">
                        <td><input type="radio" :value="key" v-model="stripeSelectedAccount"></td>
                        <td>{{ stripeBankAccount.name }}</td>
                        <td>{{ stripeBankAccount.accountNumber }}</td>
                        <td><a href="#" @click.prevent="editStripeBankAccount(key)">Edit</a></td>
                        <td><a href="#" @click.prevent="removeStripeBankAccount(key)">Remove</a></td>
                    </tr>
                </table>

                <form v-if="editingStripeBankAccount" @submit.prevent="saveBank()">
                    <text-field id="name" label="Bank Name" v-model="bankDraft.name" ref="bankname" />
                    <text-field id="accountNumber" label="Account Number" v-model="bankDraft.accountNumber" />
                    <input type="submit" class="btn btn-primary" value="Save">
                    <input type="button" class="btn btn-secondary" value="Cancel" @click="editingStripeBankAccount = false">
                </form>

                <template v-else>
                    <input type="button" class="btn btn-secondary btn-sm" value="Add Bank Account" @click="newBankAccount()">
                </template>

                <div class="float-right">
                    <input type="button" class="btn btn-light btn-sm" value="Disconnect from Stripe" @click="stripeConnected = false">
                </div>

            </template>


            <template v-else>
                <a class="btn btn-primary" href="https://id.craftcms.dev/index.php/stripe/connect">Connect to Stripe</a>
                <!--<a class="btn btn-primary" href="https://id.craftcms.dev/index.php/stripe/connect" value="Connect to Stripe" @click="stripeConnected = true">-->
            </template>


        </div>
    </div>

</template>

<script>
    import { mapGetters } from 'vuex'
    import TextField from '../components/fields/TextField'

    export default {
        components: {
            TextField
        },

        directives: {
            focus: {
                inserted (el) {
                    el.focus()
                }
            }
        },

        data() {
            return {
                stripeConnected: false,
                editingStripeBankAccount: false,
                editedStripeBankAccountId: null,
                stripeSelectedAccount: 0,
                stripeBankAccounts: [
                    {
                        name: 'BNP Parisbas',
                        accountNumber: '1111111111111111111'
                    },
                    {
                        name: 'Societe Generale',
                        accountNumber: '2222222222222222222'
                    },
                ],
                bankDraft: {
                },
            }
        },

        computed: {
            bank() {
                return this.stripeBankAccounts[this.stripeSelectedAccount];
            }
        },

        methods: {
            newBankAccount() {
                this.editingStripeBankAccount = true;
                this.editedStripeBankAccountId = null;
                this.bankDraft = {};
            },

            editStripeBankAccount(key) {
                this.editingStripeBankAccount = true;
                this.editedStripeBankAccountId = key;
                this.bankDraft = JSON.parse(JSON.stringify(this.stripeBankAccounts[key]));
            },

            removeStripeBankAccount(key) {
                this.stripeBankAccounts.splice(key, 1);

                if(key === this.stripeSelectedAccount) {
                    this.stripeSelectedAccount = 0;
                }

                this.$root.displayNotice('Bank account removed.');
            },

            saveBank() {
                this.editingStripeBankAccount = false;
                let bankAccount = JSON.parse(JSON.stringify(this.bankDraft));

                if(this.editedStripeBankAccountId) {
                    this.stripeBankAccounts[this.editedStripeBankAccountId] = bankAccount;
                } else {
                    this.stripeBankAccounts.push(bankAccount);
                }

                this.$root.displayNotice('Bank account saved.');
            }
        },

        watch: {
            stripeSelectedAccount() {
                this.$root.displayNotice('Bank account saved.');
            }
        },

        created() {
            this.stripeConnected = false;

            if(window.stripeAccessToken) {
                this.stripeConnected = true;
            }
        }
    }
</script>

<template>
	<div>
		<div class="card mb-3">
			<div class="card-header"><i class="fa fa-institution"></i> Bank Account</div>
			<div class="card-body">
				<template v-if="!editingBank">
					<button @click="editBank()" type="button" class="btn btn-secondary btn-sm float-right" data-facebox="#billing-contact-info-modal">
						<i class="fa fa-pencil"></i>
						Edit
					</button>

					<dl>
						<dt>Bank Name</dt>
						<dd>{{ bank.name }}</dd>
						<dt>Account Number</dt>
						<dd>{{ bank.accountNumber }}</dd>
					</dl>

				</template>

				<form v-if="editingBank" @submit.prevent="saveBank()">
					<text-field id="name" label="Bank Name" v-model="bankDraft.name" ref="bankname" />
					<text-field id="accountNumber" label="Account Number" v-model="bankDraft.accountNumber" />
					<input type="submit" class="btn btn-primary" value="Save">
					<input type="button" class="btn btn-secondary" value="Cancel" @click="editingBank = false">
				</form>

			</div>
		</div>
		<div class="card mb-3">
			<div class="card-header">Scheduled deposits</div>
			<div class="card-body">
				<table class="table">
					<thead>
					<tr>
						<th>Amount</th>
						<th>Date</th>
					</tr>
					</thead>
					<tbody>
					<tr v-for="payout in payoutsScheduled">
						<td>{{ payout.amount|currency }}</td>
						<td>{{ payout.date }}</td>
					</tr>
					</tbody>
				</table>
			</div>
		</div>
		<div class="card mb-3">
			<div class="card-header">Past deposits</div>
			<div class="card-body">
				<table class="table">
					<thead>
					<tr>
						<th>Amount</th>
						<th>Date</th>
					</tr>
					</thead>
					<tbody>
					<tr v-for="payout in payouts">
						<td><router-link :to="'/payouts/'+payout.id">{{ payout.amount|currency }}</router-link></td>
						<td>{{ payout.date }}</td>
					</tr>
					</tbody>
				</table>
			</div>
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
                editingBank: false,
                bank: {
					name: 'BNP Parisbas',
					accountNumber: '2345678923456783456'
				},
				bankDraft: {
				},
            }
        },


        computed: {
            ...mapGetters({
                payouts: 'payouts',
                payoutsScheduled: 'payoutsScheduled',
            }),
        },

		methods: {
            editBank() {
				this.editingBank = true;
				this.bankDraft = JSON.parse(JSON.stringify(this.bank));

                this.$nextTick(function() {
                    this.$refs.bankname.$emit('focus');
                });
			},
			saveBank() {
                this.editingBank = false;
                this.bank = JSON.parse(JSON.stringify(this.bankDraft));
			}
		},
    }
</script>
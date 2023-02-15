<template>
  <h5>Lignes de transaction</h5>
  <div class="row" v-if="Object.values(transactionsData).length">
    <div class="col-3">
      <label class="form-label required">Compte</label>
    </div>
    <div class="col-7">
      <label class="form-label required">Montant</label>
    </div>
  </div>

  <div class="row mb-3" v-for="[kTransaction, transaction] of Object.entries(transactionsData)">
    <div class="col-3">
      <select
          class="form-select"
          :name="'transaction[transactionLines]['+kTransaction+'][account]'"
          v-model="transaction.accountId"
          required
      >
        <option value="">Choisir un compte</option>
        <option :value="account.id" v-for="account of accounts">
          {{ account.name }} ({{ account.balance }}
          <template v-if="account.individual_price">
            x {{ account.individual_price }}
          </template>
          €)
        </option>
      </select>
    </div>
    <div class="col-7">
      <amount-input
        :error="errors[kTransaction]"
        :name="'transaction[transactionLines]['+kTransaction+'][amount]'"
        :price="(transaction.accountId) ? accounts.find((account) => account.id === transaction.accountId).individual_price : -1"
        :value="transaction.amount"
      ></amount-input>
    </div>
    <div class="d-grid col-2">
      <button type="button" class="btn btn-danger" @click="removeTransaction(kTransaction)">
        <i class="fas fa-trash"></i>
      </button>
    </div>
  </div>

  <div class="d-grid">
    <button class="btn btn-success" @click.prevent.self="addTransaction">
      <i class="fas fa-plus"></i>
      Ajouter une ligne
    </button>
  </div>
</template>

<script>
  export default {
    mounted() {
      this.transactions.forEach((transaction, index) => {
        this.transactionsData[index] = transaction;
      });
      this.transactionsIndex = this.transactions.length;
    },
    data() {
      return {
        transactionsIndex: undefined,
        transactionsData: {},
      };
    },
    methods: {
      addTransaction() {
        this.transactionsData[this.transactionsIndex] = {
          accountId: "",
          amount: null,
        };
        this.transactionsIndex++;
      },
      removeTransaction(kTransaction) {
        delete this.transactionsData[kTransaction];
      },
    },
    props: {
      accounts: {
        type: Array,
        required: true,
      },
      errors: {
        type: Object,
        default: {},
      },
      transactions: {
        type: Array,
        required: true,
      },
    },
  };
</script>

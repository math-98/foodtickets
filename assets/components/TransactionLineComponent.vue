<template>
  <div class="row">
    <div class="col-6">
      <select
          class="form-select"
          v-model="transaction.accountId"
          :name="'transaction[transactionLines]['+index+'][account]'"
          @change="$emit('update', transaction)"
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
    <div class="col-4">
      <input
        type="hidden"
        :name="'transaction[transactionLines]['+index+'][amount]'"
        :value="value"
      >
      <amount-input
        :error="errors"
        :price="(transaction.accountId) ? accounts.find((account) => account.id === transaction.accountId).individual_price : -1"
        v-model="transaction.amount"
        @update:model-value="$emit('update', transaction)"
      ></amount-input>
    </div>
    <div class="d-grid col-2">
      <button type="button" class="btn btn-danger" @click="$emit('delete')">
        <i class="fas fa-trash"></i>
      </button>
    </div>
  </div>
</template>

<script>
  export default {
    computed: {
      value() {
        const amount = this.transaction.amount;
        return (this.invert) ? -amount : amount;
      },
    },
    props: {
      accounts: {
        type: Array,
        required: true,
      },
      errors: {
        type: String
      },
      index: {
        type: Number,
        required: true,
      },
      invert: {
        type: Boolean,
        default: false,
      },
      transaction: {
        type: Object,
        required: true,
      },
    },
    emits: [
      'delete',
      'update'
    ]
  }
</script>
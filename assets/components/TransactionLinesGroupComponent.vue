<template>
  <h5>Lignes de transaction</h5>
  <div class="row mb-3">
    <div v-if="inboundEnabled" v-bind:class="{
      'col-6': outboundEnabled,
      'col-12': !outboundEnabled,
    }">
      <h5>Crédit</h5>
      <div class="row" v-if="transactionsData.filter((elm) => elm.type === 'inbound').length">
        <div class="col-6">
          <label class="form-label required">Compte</label>
        </div>
        <div class="col-4">
          <label class="form-label required">Montant</label>
        </div>
      </div>

      <transaction-line
        :accounts="accounts"
        :errors="errors[kTransaction]"
        :transaction="transaction"
        :key="kTransaction"
        :index="kTransaction"
        @delete="removeTransaction(kTransaction)"
        @update="transactionsData[kTransaction] = $event"
        v-for="(transaction, kTransaction) in transactionsData.filter((elm) => elm.type === 'inbound')"
      ></transaction-line>

      <div class="d-grid mt-3">
        <button class="btn btn-success" @click.prevent.self="addTransaction('inbound')">
          <i class="fas fa-plus"></i>
          Ajouter une ligne
        </button>
      </div>
    </div>

    <div v-if="outboundEnabled" v-bind:class="{
      'col-6': inboundEnabled,
      'col-12': !inboundEnabled,
    }">
      <h5>Débit</h5>
      <div class="row" v-if="transactionsData.filter((elm) => elm.type === 'outbound').length">
        <div class="col-6">
          <label class="form-label required">Compte</label>
        </div>
        <div class="col-4">
          <label class="form-label required">Montant</label>
        </div>
      </div>

      <transaction-line
        :accounts="accounts"
        :transaction="transaction"
        :key="kTransaction"
        :index="kTransaction"
        :invert="true"
        @delete="removeTransaction(kTransaction)"
        @update="transactionsData[kTransaction] = $event"
        v-for="(transaction, kTransaction) in transactionsData.filter((elm) => elm.type === 'outbound')"
      ></transaction-line>

      <div class="d-grid mt-3">
        <button class="btn btn-success" @click.prevent.self="addTransaction('outbound')">
          <i class="fas fa-plus"></i>
          Ajouter une ligne
        </button>
      </div>
    </div>
  </div>
</template>

<script>
  export default {
    mounted() {
      this.transactions.forEach((transaction) => {
        if (transaction.amount > 0) {
          transaction.type = 'inbound';
        } else {
          transaction.amount = Math.abs(transaction.amount);
          transaction.type = 'outbound';
        }
        this.transactionsData.push(transaction);
      });
    },
    data() {
      return {
        transactionsData: []
      };
    },
    methods: {
      addTransaction(category) {
        this.transactionsData.push({
          accountId: '',
          amount: '',
          type: category,
        });
      },
      removeTransaction(kTransaction) {
        this.transactionsData.splice(kTransaction, 1);
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
      inboundEnabled: {
        type: Boolean,
        default: true,
      },
      outboundEnabled: {
        type: Boolean,
        default: true,
      },
      transactions: {
        type: Array,
        required: true,
      },
    },
  };
</script>

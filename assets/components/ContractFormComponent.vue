<template>
  <div class="row mb-3">
    <div class="col-6">
      <label for="contract_name" class="form-label required">Nom</label>
      <input
          type="text"
          id="contract_name"
          class="form-control"
          name="contract[name]"
          v-model="contractData.name"
          required
      >
    </div>
    <div class="col-6">
      <label class="form-label required" for="contract_account">Compte</label>
      <select
          id="contract_account"
          class="form-select"
          name="contract[account]"
          v-model="contractData.accountId"
          required
      >
        <option value="">Choisissez un compte</option>
        <option :value="account.id" v-for="account of accounts">
          {{ account.name }}
        </option>
      </select>
    </div>
  </div>

  <div class="row mb-3">
    <div class="col-6">
      <label for="contract_start" class="form-label required">Début</label>
      <input
          type="date"
          id="contract_start"
          class="form-control"
          name="contract[start]"
          v-model="contractData.start"
          required
      >
    </div>
    <div class="col-6">
      <label for="contract_end" class="form-label">Fin</label>
      <input
          type="date"
          id="contract_end"
          class="form-control"
          name="contract[end]"
          v-model="contractData.end"
      >
    </div>
  </div>

  <div class="row mb-3">
    <div class="col-4">
      <label for="contract_amount" class="form-label">Montant</label>
      <amount-input
          name="contract[amount]"
          v-model="contractData.amount"
          :price="(account) ? account.individual_price : -1"
      ></amount-input>
      <div id="contract_amount_help" class="form-text mb-0 help-text">
        Laisser vide si le montant est variable (Forfait réel)
      </div>
    </div>
    <div class="col-4 pt-3">
      <div class="form-check">
        <input
            type="checkbox"
            id="contract_reception_delayed"
            class="form-check-input"
            name="contract[reception_delayed]"
            v-model="contractData.reception_delayed"
            aria-describedby="contract_reception_delayed_help"
        >
        <label class="form-check-label" for="contract_reception_delayed">Réception décalée</label>
      </div>
      <div id="contract_reception_delayed_help" class="form-text mb-0 help-text">{{ receptionDelayedHelp }}</div>
    </div>
    <div class="col-4 pt-3">
      <div class="form-check">
        <input
          type="checkbox"
          id="contract_billing_delayed"
          class="form-check-input"
          name="contract[billing_delayed]"
          v-model="contractData.billing_delayed"
          aria-describedby="contract_billing_delayed_help"
        >
        <label class="form-check-label" for="contract_billing_delayed">Facturation décalée</label>
      </div>
      <div id="contract_billing_delayed_help" class="form-text mb-0 help-text">{{ billingDelayedHelp }}</div>
    </div>
  </div>
</template>

<script>
import AmountInput from "./AmountInputComponent.vue";

export default {
  components: { AmountInput },
  mounted() {
    this.contractData = this.contract;
    if (this.contractData.accountId === null) {
      this.contractData.accountId = '';
    }
    if (this.contractData.start !== null) {
      this.contractData.start = (new Date(this.contractData.start)).toISOString().substring(0, 10);
    }
    if (this.contractData.end !== null) {
      this.contractData.end = (new Date(this.contractData.end)).toISOString().substring(0, 10);
    }
    this.contractData.amount = Number(this.contractData.amount);
    this.contractData.billing_delayed = (this.contractData.billing_delayed == 1);
    this.contractData.reception_delayed = (this.contractData.reception_delayed == 1);
  },
  data() {
    return {
      contractData: {},
    }
  },
  computed: {
    account() {
      return this.accounts.find(account => account.id === this.contractData.accountId);
    },
    billingDelayedHelp() {
      return 'Facturation à la fin du mois' +
          ((this.contractData.billing_delayed) ? ' suivant' : '');
    },
    receptionDelayedHelp() {
      return 'Réception au début du mois' +
          ((this.contractData.reception_delayed) ? ' suivant' : '');
    }
  },
  props: {
    accounts: {
      type: Array,
      required: true,
    },
    contract: {
      type: Object,
      required: true,
    },
    errors: {
      type: Object,
      default: {},
    },
  }
}
</script>

<style scoped>
  .form-check-label {
    margin-bottom: 0;
  }
</style>
<template>
  <div class="input-group">
    <span class="input-group-text" v-if="!price">€</span>
    <input
        type="text"
        :name="name"
        :value="value"
        :inputmode="(price > 0) ? 'numeric' : 'decimal'"
        :disabled="price < 0"
        v-bind:class="inputClass"
    >
    <span class="input-group-text" v-if="price > 0">
      x {{ price.toFixed(2) }} €
    </span>
    <div class="invalid-feedback" v-if="error">{{ error }}</div>
  </div>
</template>

<script>
export default {
  mounted() {
    this.inputClass = ['form-control'];
    if (this.error) {
      this.inputClass.push('is-invalid');
    }
  },
  data() {
    return {
      inputClass: [],
    }
  },
  props: {
    error: {
      type: String,
    },
    name: {
      type: String,
      required: true,
    },
    price: {
      type: Number,
      default: -1,
    },
    value: {
      type: Number,
    },
  },
}
</script>

<style scoped>

</style>
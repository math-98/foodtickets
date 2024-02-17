<template>
  <div class="input-group">
    <span class="input-group-text" v-if="!price">€</span>
    <input
        type="text"
        min="0"
        :name="name"
        :inputmode="(price > 0) ? 'numeric' : 'decimal'"
        :disabled="price < 0"
        v-model="value"
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
  computed: {
    value: {
      get() {
        return this.modelValue;
      },
      set(value) {
        value = value.replace(',', '.');
        this.$emit('update:modelValue', value);
      },
    },
  },
  data() {
    return {
      inputClass: [],
    }
  },
  emits: ['update:modelValue'],
  props: {
    error: {
      type: String,
    },
    modelValue: {
      type: String,
      required: true,
    },
    name: {
      type: String
    },
    price: {
      type: Number,
      default: -1,
    },
  },
}
</script>

<style scoped>

</style>
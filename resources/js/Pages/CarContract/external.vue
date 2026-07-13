<script setup>
import ContractIndex from './index.vue';

const props = defineProps({
  client: Array,
  user: String,
  contractCreators: { type: Array, default: () => [] },
  showBrokerage: { type: Boolean, default: false },
});
</script>

<template>
  <ContractIndex
    v-bind="props"
    contract-type="external"
  />
</template>

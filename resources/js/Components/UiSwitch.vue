<script setup>
defineProps({
  modelValue: {
    type: [Boolean, Number, String],
    default: false,
  },
  disabled: {
    type: Boolean,
    default: false,
  },
  onColor: {
    type: String,
    default: 'bg-orange-500',
  },
});

const emit = defineEmits(['update:modelValue', 'change']);

const isOn = (value) => value === true || value === 1 || value === '1';
</script>

<template>
  <button
    type="button"
    role="switch"
    :aria-checked="isOn(modelValue) ? 'true' : 'false'"
    :disabled="disabled"
    class="relative inline-flex h-7 w-12 shrink-0 cursor-pointer items-center rounded-full border-2 border-transparent transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-orange-400 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 dark:focus:ring-offset-slate-900"
    :class="isOn(modelValue) ? onColor : 'bg-slate-400 dark:bg-slate-600'"
    @click.stop="emit('change', !isOn(modelValue))"
  >
    <span
      class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow transition duration-200"
      :class="isOn(modelValue) ? 'translate-x-5' : 'translate-x-0.5'"
    />
  </button>
</template>

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
    dir="ltr"
    :aria-checked="isOn(modelValue) ? 'true' : 'false'"
    :disabled="disabled"
    class="ui-switch relative inline-block h-7 w-12 shrink-0 cursor-pointer rounded-full transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-orange-400 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 dark:focus:ring-offset-slate-900"
    :class="isOn(modelValue) ? onColor : 'bg-slate-400 dark:bg-slate-600'"
    @click.stop="emit('change', !isOn(modelValue))"
  >
    <span
      class="ui-switch-thumb pointer-events-none absolute top-0.5 left-0.5 h-6 w-6 rounded-full bg-white shadow"
      :class="{ 'is-on': isOn(modelValue) }"
    />
  </button>
</template>

<style scoped>
.ui-switch {
  /* Force LTR geometry so RTL page layout doesn't push the thumb outside */
  direction: ltr;
}

.ui-switch-thumb {
  transform: translateX(0);
  transition: transform 0.2s ease;
}

.ui-switch-thumb.is-on {
  transform: translateX(1.25rem); /* 20px = w-12(48) - thumb(24) - padding(4) */
}
</style>

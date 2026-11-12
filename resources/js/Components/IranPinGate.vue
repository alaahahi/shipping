<script setup>
import VuePincodeInput from 'vue3-pincode-input';

defineProps({
  unlocked: { type: Boolean, default: false },
  pin: { type: [String, Number], default: '' },
  error: { type: String, default: '' },
  unlocking: { type: Boolean, default: false },
});

const emit = defineEmits(['update:pin']);
</script>

<template>
  <div v-if="!unlocked" class="flex min-h-[60vh] items-center justify-center px-4 py-12">
    <div
      class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-8 text-center shadow-sm dark:border-slate-700 dark:bg-slate-800"
    >
      <div
        class="mx-auto mb-5 flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-900 text-lg font-bold text-white dark:bg-slate-950"
        aria-hidden="true"
      >
        PIN
      </div>
      <h3 class="text-lg font-bold text-slate-900 dark:text-white">
        {{ $t('iran_pin_title') }}
      </h3>
      <p class="mt-2 text-sm leading-relaxed text-slate-500 dark:text-slate-300">
        {{ $t('iran_pin_hint') }}
      </p>
      <div dir="ltr" class="mt-6">
        <VuePincodeInput
          :model-value="String(pin || '')"
          @update:model-value="emit('update:pin', $event)"
          :digits="6"
          :secure="true"
          class="justify-center"
          :autofocus="true"
          success-class="border-2 border-emerald-400"
          input-class="rounded-xl text-slate-700 border-2 border-slate-200 shadow-sm mx-1.5 mt-2 dark:bg-slate-900 dark:border-slate-600 dark:text-white"
        />
      </div>
      <p v-if="unlocking" class="mt-5 text-sm font-medium text-slate-400 dark:text-slate-400">
        {{ $t('iran_pin_checking') }}
      </p>
      <p
        v-else-if="error"
        class="mt-5 rounded-xl border border-rose-500/40 bg-rose-500/10 px-3 py-2 text-sm font-semibold text-rose-600 dark:text-rose-300"
      >
        {{ error }}
      </p>
    </div>
  </div>
  <slot v-else />
</template>

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
  <div v-if="!unlocked" class="py-16">
    <div class="max-w-md mx-auto text-center px-4">
      <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-2">
        أدخل رمز PIN للدخول إلى صفحة إيران
      </h3>
      <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
        الصفحة محمية برمز PIN — متاحة لأي مستخدم مسجّل بعد إدخال الرمز
      </p>
      <div dir="ltr">
        <VuePincodeInput
          :model-value="String(pin || '')"
          @update:model-value="emit('update:pin', $event)"
          :digits="6"
          :secure="true"
          class="justify-center"
          :autofocus="true"
          success-class="border-2 border-green-400"
          input-class="rounded-full text-gray-500 border-2 border-gray-200 shadow mx-2 mt-5"
        />
      </div>
      <p v-if="unlocking" class="mt-4 text-sm text-gray-400">جاري التحقق...</p>
      <p v-else-if="error" class="mt-4 text-red-600 dark:text-red-400">{{ error }}</p>
    </div>
  </div>
  <slot v-else />
</template>

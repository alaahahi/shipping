<script setup>
import Modal from "@/Components/Modal.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import { ref, watch, computed, defineProps, defineEmits } from 'vue';

const props = defineProps({
  show: Boolean,
  buyer: Object,
  merchantId: [String, Number],
  isLoading: Boolean,
});

const emit = defineEmits(['close', 'confirm']);

const paymentAmount = ref(0);
const paymentNote = ref('');
const validationError = ref('');

// Calculate max allowed payment (absolute value of remaining debt)
const maxPayment = computed(() => {
  if (!props.buyer || !props.buyer.remaining_debt) return 0;
  return Math.abs(props.buyer.remaining_debt);
});

// Validate payment amount
const isValidAmount = computed(() => {
  const amount = parseFloat(paymentAmount.value) || 0;
  if (amount <= 0) {
    validationError.value = 'يرجى إدخال مبلغ أكبر من الصفر';
    return false;
  }
  if (amount > maxPayment.value) {
    validationError.value = `المبلغ أكبر من الدين المتبقي (${formatNumber(maxPayment.value)} $)`;
    return false;
  }
  validationError.value = '';
  return true;
});

// Reset form when modal opens/closes
watch(() => props.show, (newVal) => {
  if (newVal) {
    paymentAmount.value = 0;
    paymentNote.value = '';
    validationError.value = '';
  }
});

// Watch payment amount changes to validate
watch(paymentAmount, () => {
  if (paymentAmount.value) {
    isValidAmount.value; // Trigger validation
  } else {
    validationError.value = '';
  }
});

function formatNumber(value) {
  if (value === null || value === undefined) return '0';
  const num = typeof value === 'number' ? value : parseFloat(value) || 0;
  const formatted = num.toFixed(2);
  return formatted.replace(/\.00$/, '');
}

function handleClose() {
  emit('close');
}

function handleConfirm() {
  if (!isValidAmount.value) {
    return;
  }
  emit('confirm', {
    amount: parseFloat(paymentAmount.value) || 0,
    note: paymentNote.value || ''
  });
}
</script>

<template>
  <Modal :show="show" @close="handleClose">
    <template #header>
      <h2 class="text-xl font-bold dark:text-gray-100">إضافة دفع - {{ buyer?.name }}</h2>
    </template>
    
    <template #body>
      <div v-if="buyer" class="mb-4 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
        <div class="text-sm space-y-1">
          <div class="flex justify-between">
            <span class="text-gray-600 dark:text-gray-400">إجمالي المبيعات:</span>
            <span class="font-medium">{{ formatNumber(buyer.total_sales) }} $</span>
          </div>
          <div class="flex justify-between">
            <span class="text-gray-600 dark:text-gray-400">المدفوع:</span>
            <span class="font-medium text-green-600">{{ formatNumber(buyer.total_paid) }} $</span>
          </div>
          <div class="flex justify-between font-bold text-lg pt-2 border-t">
            <span class="text-red-600">المتبقي (دين):</span>
            <span class="text-red-600">{{ formatNumber(Math.abs(buyer.remaining_debt)) }} $</span>
          </div>
        </div>
      </div>
      
      <div class="space-y-4">
        <div>
          <InputLabel for="payment_amount" value="مبلغ الدفع ($)" />
          <TextInput
            id="payment_amount"
            type="number"
            step="0.01"
            min="0"
            v-model="paymentAmount"
            :max="maxPayment"
            placeholder="أدخل مبلغ الدفع"
            :class="[
              'mt-1 block w-full',
              validationError ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : ''
            ]"
          />
          <p v-if="validationError" class="mt-1 text-xs text-red-600 dark:text-red-400">
            {{ validationError }}
          </p>
          <p v-else class="mt-1 text-xs text-gray-500 dark:text-gray-400">
            الحد الأقصى: {{ formatNumber(maxPayment) }} $
          </p>
        </div>
        <div>
          <InputLabel for="payment_note" value="ملاحظة (اختياري)" />
          <textarea
            id="payment_note"
            v-model="paymentNote"
            rows="3"
            placeholder="أدخل ملاحظة للدفعة (اختياري)"
            class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
          ></textarea>
        </div>
      </div>
    </template>
    
    <template #footer>
      <div class="flex justify-end gap-2">
        <button
          @click="handleClose"
          class="px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-200 dark:bg-gray-700 rounded hover:bg-gray-300 dark:hover:bg-gray-600"
          :disabled="isLoading"
        >
          إلغاء
        </button>
        <button
          @click="handleConfirm"
          class="px-4 py-2 text-white bg-green-600 rounded hover:bg-green-700 dark:bg-green-700 dark:hover:bg-green-800 disabled:opacity-50 disabled:cursor-not-allowed"
          :disabled="isLoading || !isValidAmount || !paymentAmount || paymentAmount <= 0"
        >
          <span v-if="isLoading">جاري الحفظ...</span>
          <span v-else>إضافة الدفع</span>
        </button>
      </div>
    </template>
  </Modal>
</template>


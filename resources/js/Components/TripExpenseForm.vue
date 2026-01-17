<script setup>
import { ref } from 'vue';
import axios from 'axios';
import { useToast } from "vue-toastification";
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
  tripId: {
    type: [String, Number],
    required: true,
  },
});

const emit = defineEmits(['expense-added']);

const toast = useToast();

const form = ref({
  expense_type: 'other',
  amount: '',
  currency: 'dollar',
  note: '',
  date: new Date().toISOString().split('T')[0],
});

const isSubmitting = ref(false);
const showForm = ref(false);

const expenseTypes = [
  { value: 'shipping', label: 'شحن' },
  { value: 'fuel', label: 'وقود' },
  { value: 'port', label: 'ميناء' },
  { value: 'customs', label: 'جمارك' },
  { value: 'other', label: 'أخرى' },
];

const submitExpense = async () => {
  if (!form.value.amount || parseFloat(form.value.amount) <= 0) {
    toast.error('يرجى إدخال مبلغ صحيح');
    return;
  }

  isSubmitting.value = true;
  try {
    const response = await axios.post(`/api/trips/${props.tripId}/expenses`, form.value);
    
    if (response.data.success) {
      toast.success('تم إضافة المصاريف بنجاح');
      emit('expense-added', response.data.expense);
      // Reset form
      form.value = {
        expense_type: 'other',
        amount: '',
        currency: 'dollar',
        note: '',
        date: new Date().toISOString().split('T')[0],
      };
      showForm.value = false;
    } else {
      toast.error(response.data.message || 'حدث خطأ أثناء إضافة المصاريف');
    }
  } catch (error) {
    console.error(error);
    const errorMessage = error.response?.data?.message || 'حدث خطأ أثناء إضافة المصاريف';
    toast.error(errorMessage);
  } finally {
    isSubmitting.value = false;
  }
};
</script>

<template>
  <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 sm:p-6">
    <div class="flex justify-between items-center mb-4">
      <h3 class="text-lg font-semibold text-gray-900 dark:text-white">إضافة مصاريف</h3>
      <button
        @click="showForm = !showForm"
        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
      >
        <svg
          class="w-4 h-4 ml-2"
          :class="{ 'rotate-45': showForm }"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        {{ showForm ? 'إخفاء' : 'إضافة مصاريف' }}
      </button>
    </div>

    <div v-show="showForm" class="mt-6 space-y-4 border-t border-gray-200 dark:border-gray-700 pt-6">
      <!-- Expense Type -->
      <div>
        <InputLabel for="expense_type" value="نوع المصاريف *" />
        <select
          id="expense_type"
          v-model="form.expense_type"
          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
          :disabled="isSubmitting"
        >
          <option v-for="type in expenseTypes" :key="type.value" :value="type.value">
            {{ type.label }}
          </option>
        </select>
      </div>

      <!-- Amount & Currency - Responsive Grid -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="sm:col-span-2">
          <InputLabel for="amount" value="المبلغ *" />
          <TextInput
            id="amount"
            v-model="form.amount"
            type="number"
            step="0.01"
            min="0"
            placeholder="0.00"
            class="mt-1 block w-full"
            required
            :disabled="isSubmitting"
          />
        </div>
        <div>
          <InputLabel for="currency" value="العملة *" />
          <select
            id="currency"
            v-model="form.currency"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
            :disabled="isSubmitting"
          >
            <option value="dollar">دولار ($)</option>
            <option value="dinar">دينار</option>
          </select>
        </div>
      </div>

      <!-- Date -->
      <div>
        <InputLabel for="expense_date" value="التاريخ" />
        <TextInput
          id="expense_date"
          v-model="form.date"
          type="date"
          class="mt-1 block w-full"
          :disabled="isSubmitting"
        />
      </div>

      <!-- Note -->
      <div>
        <InputLabel for="note" value="ملاحظات" />
        <textarea
          id="note"
          v-model="form.note"
          rows="3"
          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
          placeholder="أي ملاحظات إضافية..."
          :disabled="isSubmitting"
        ></textarea>
      </div>

      <!-- Submit Button -->
      <div class="flex justify-end">
        <button
          @click="submitExpense"
          :disabled="isSubmitting || !form.amount || parseFloat(form.amount) <= 0"
          class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-900 focus:outline-none focus:border-green-900 focus:ring ring-green-300 disabled:opacity-25 disabled:cursor-not-allowed transition ease-in-out duration-150"
        >
          <svg v-if="isSubmitting" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          {{ isSubmitting ? 'جاري الحفظ...' : 'إضافة المصاريف' }}
        </button>
      </div>
    </div>
  </div>
</template>

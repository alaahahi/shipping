<script setup>
import Modal from "@/Components/Modal.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import { ModelListSelect } from "vue-search-select";
import "vue-search-select/dist/VueSearchSelect.css";
import { ref, watch, defineProps, defineEmits } from 'vue';

const props = defineProps({
  show: Boolean,
  sale: Object,
  clients: Array,
});

const emit = defineEmits(['close', 'update']);

const form = ref({
  id: null,
  client_id: null,
  client_name: '', // Added for new client
  client_phone: '', // Added for new client
  car_id: null,
  car_name: '',
  car_price: 0,
  shipping: 0,
  sale_price: 0,
  paid_amount: 0,
  expenses: 0,
  additional_expenses: 0,
  note: '',
  sale_date: '',
});

const showNewClientForm = ref(false); // Added state for toggling new client form

function getTodayDate() {
  const today = new Date();
  const year = today.getFullYear();
  const month = String(today.getMonth() + 1).padStart(2, '0');
  const day = String(today.getDate()).padStart(2, '0');
  return `${year}-${month}-${day}`;
}

function formatSaleDate(dateString) {
  if (!dateString) return getTodayDate();
  try {
    if (typeof dateString === 'string' && dateString.includes('T')) {
      return dateString.split('T')[0];
    }
    const date = new Date(dateString);
    if (isNaN(date.getTime())) return dateString;
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
  } catch (error) {
    return dateString;
  }
}

watch(() => props.sale, (newSale) => {
  if (newSale) {
    form.value = {
      id: newSale.id,
      client_id: newSale.client_id,
      car_id: newSale.car_id,
      car_name: newSale.car ? `${newSale.car.car_type} ${newSale.car.year} - ${newSale.car.vin || newSale.car.car_number}` : '',
      car_price: newSale.car_price || 0,
      shipping: newSale.shipping || 0,
      sale_price: newSale.sale_price,
      paid_amount: newSale.paid_amount,
      expenses: newSale.expenses,
      additional_expenses: newSale.additional_expenses || 0,
      note: newSale.note || '',
      sale_date: formatSaleDate(newSale.sale_date || newSale.created_at),
      // Reset new client fields when opening modal
      client_name: '',
      client_phone: ''
    };
    showNewClientForm.value = false;
  }
}, { deep: true, immediate: true });

function submit() {
  emit('update', form.value);
}

function close() {
  emit('close');
}
</script>

<template>
  <Modal :show="show" @close="close">
    <template #header>
      <h2 class="text-xl font-bold dark:text-gray-100">تعديل مبيعة داخلية</h2>

      <div class="space-y-4">
         <div>
          <InputLabel for="edit_client_select" value="العميل" />
          <ModelListSelect
            v-model="form.client_id"
            :list="clients"
            option-value="value"
            option-text="text"
            placeholder="ابحث عن عميل..."
            class="dark:bg-gray-800 dark:text-gray-100"
          />
        </div>

        <!-- السيارة (للقراءة فقط) -->
        <div>
          <InputLabel for="car_display_edit" value="السيارة" />
          <TextInput
            id="car_display_edit"
            type="text"
            v-model="form.car_name"
            class="mt-1 block w-full bg-gray-100 dark:bg-gray-800 cursor-not-allowed text-gray-500 dark:text-gray-400"
            disabled
          />
        </div>

        <div>
          <!-- حقل car_id مخفي -->
          <input type="hidden" v-model="form.car_id" />
        </div>

        <div>
          <InputLabel for="edit_car_price" value="سعر السيارة ($)" />
          <TextInput
            id="edit_car_price"
            type="number"
            step="0.01"
            v-model="form.car_price"
            class="mt-1 block w-full"
          />
        </div>
        <div>
          <!-- تم إخفاء النقل في التعديل -->
          <input
            type="hidden"
            v-model="form.shipping"
          />
        </div>
        <div>
          <InputLabel for="edit_sale_price" value="سعر البيع ($)" />
          <TextInput
            id="edit_sale_price"
            type="number"
            step="0.01"
            v-model="form.sale_price"
            class="mt-1 block w-full"
          />
        </div>
        <div>
          <InputLabel for="edit_paid_amount" value="المبلغ المدفوع ($)" />
          <TextInput
            id="edit_paid_amount"
            type="number"
            step="0.01"
            v-model="form.paid_amount"
            class="mt-1 block w-full"
          />
        </div>
        <div>
          <InputLabel for="edit_expenses" value="المصاريف ($)" />
          <TextInput
            id="edit_expenses"
            type="number"
            step="0.01"
            v-model="form.expenses"
            class="mt-1 block w-full"
          />
        </div>
        <div>
          <InputLabel for="edit_additional_expenses" value="مصاريف إضافية ($)" />
          <TextInput
            id="edit_additional_expenses"
            type="number"
            step="0.01"
            v-model="form.additional_expenses"
            class="mt-1 block w-full"
          />
        </div>
        <div>
          <InputLabel for="edit_sale_date" value="تاريخ البيع" />
          <TextInput
            id="edit_sale_date"
            type="date"
            v-model="form.sale_date"
            class="mt-1 block w-full"
          />
        </div>
        <div>
          <InputLabel for="edit_note" value="ملاحظات" />
          <textarea
            id="edit_note"
            v-model="form.note"
            rows="3"
            class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100"
          ></textarea>
        </div>
      </div>
    </template>
    <template #footer>
      <div class="flex justify-end gap-2">
        <button
          @click="close"
          class="px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-200 dark:bg-gray-700 rounded hover:bg-gray-300 dark:hover:bg-gray-600"
        >
          إلغاء
        </button>
        <button
          @click="submit"
          class="px-4 py-2 text-white bg-blue-600 rounded hover:bg-blue-700 dark:bg-blue-700 dark:hover:bg-blue-800"
        >
          تحديث
        </button>
      </div>
    </template>
  </Modal>
</template>

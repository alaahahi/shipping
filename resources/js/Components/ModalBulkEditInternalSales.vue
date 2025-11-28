<script setup>
import Modal from "@/Components/Modal.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import { ref, watch, defineProps, defineEmits } from 'vue';

const props = defineProps({
  show: Boolean,
  selectedCount: Number,
});

const emit = defineEmits(['close', 'confirm']);

const form = ref({
  car_price: null,
  expenses: null,
  additional_expenses: null
});

// Reset form when modal opens/closes
watch(() => props.show, (newVal) => {
  if (newVal) {
    form.value = {
      car_price: null,
      expenses: null,
      additional_expenses: null
    };
  }
});

function handleClose() {
  emit('close');
}

function handleConfirm() {
  emit('confirm', {
    car_price: form.value.car_price !== null ? parseFloat(form.value.car_price) : null,
    expenses: form.value.expenses !== null ? parseFloat(form.value.expenses) : null,
    additional_expenses: form.value.additional_expenses !== null ? parseFloat(form.value.additional_expenses) : null,
  });
}
</script>

<template>
  <Modal :show="show" @close="handleClose">
    <template #header>
      <h2 class="text-xl font-bold dark:text-gray-100">تعديل مجمع ({{ selectedCount }} مبيعة)</h2>
    </template>
    
    <template #body>
      <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
        سيتم تطبيق القيم المدخلة على جميع المبيعات المختارة. اترك الحقل فارغاً إذا كنت لا تريد تعديله.
      </div>
      <div class="space-y-4">
        <div>
          <InputLabel for="bulk_car_price" value="سعر السيارة ($)" />
          <TextInput
            id="bulk_car_price"
            type="number"
            step="0.01"
            v-model="form.car_price"
            placeholder="اتركه فارغاً إذا كنت لا تريد تعديله"
            class="mt-1 block w-full"
          />
        </div>
        <div>
          <InputLabel for="bulk_expenses" value="المصاريف ($)" />
          <TextInput
            id="bulk_expenses"
            type="number"
            step="0.01"
            v-model="form.expenses"
            placeholder="اتركه فارغاً إذا كنت لا تريد تعديله"
            class="mt-1 block w-full"
          />
        </div>
        <div>
          <InputLabel for="bulk_additional_expenses" value="مصاريف إضافية ($)" />
          <TextInput
            id="bulk_additional_expenses"
            type="number"
            step="0.01"
            v-model="form.additional_expenses"
            placeholder="اتركه فارغاً إذا كنت لا تريد تعديله"
            class="mt-1 block w-full"
          />
        </div>
      </div>
    </template>
    
    <template #footer>
      <div class="flex justify-end gap-2">
        <button
          @click="handleClose"
          class="px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-200 dark:bg-gray-700 rounded hover:bg-gray-300 dark:hover:bg-gray-600"
        >
          إلغاء
        </button>
        <button
          @click="handleConfirm"
          class="px-4 py-2 text-white bg-blue-600 rounded hover:bg-blue-700 dark:bg-blue-700 dark:hover:bg-blue-800"
        >
          حفظ التعديلات
        </button>
      </div>
    </template>
  </Modal>
</template>








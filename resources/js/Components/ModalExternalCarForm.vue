<script setup>
import { watch } from 'vue';

const props = defineProps({
  show: Boolean,
  formData: {
    type: Object,
    default: () => ({}),
  },
});

const emit = defineEmits(['save', 'close']);

function getTodayDate() {
  const today = new Date();
  const year = today.getFullYear();
  const month = String(today.getMonth() + 1).padStart(2, '0');
  const day = String(today.getDate()).padStart(2, '0');
  return `${year}-${month}-${day}`;
}

watch(
  () => props.show,
  (visible) => {
    if (!visible || !props.formData) return;
    if (!props.formData.date) {
      props.formData.date = getTodayDate();
    }
  },
);

function submit() {
  emit('save', { ...props.formData });
}
</script>

<template>
  <Transition name="modal">
    <div v-if="show" class="modal-mask">
      <div class="modal-wrapper max-h-[85vh]">
        <div class="modal-container dark:bg-gray-900 overflow-auto max-h-[85vh]">
          <div class="modal-header">
            <h2 class="text-center dark:text-gray-200 font-bold py-3">
              {{ formData?.id ? 'تعديل سيارة خارجية' : 'إضافة سيارة خارجية' }}
            </h2>
            <p class="text-center text-xs text-gray-500 dark:text-gray-400 px-4 pb-2">
              تُحفظ خارج جدول السيارات الرئيسي — بدون ربط أو تسجيل
            </p>
          </div>

          <div class="modal-body px-4 pb-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
              <label class="block">
                <span class="text-sm font-semibold dark:text-gray-200">التاجر</span>
                <input
                  v-model="formData.dealer_name"
                  type="text"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600"
                  placeholder="اسم التاجر"
                />
              </label>

              <label class="block">
                <span class="text-sm font-semibold dark:text-gray-200">نوع السيارة</span>
                <input
                  v-model="formData.car_type"
                  type="text"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600"
                  placeholder="مثال: تويوتا كامري"
                />
              </label>

              <label class="block">
                <span class="text-sm font-semibold dark:text-gray-200">السنة</span>
                <input
                  v-model="formData.year"
                  type="number"
                  min="1900"
                  max="2100"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600"
                />
              </label>

              <label class="block">
                <span class="text-sm font-semibold dark:text-gray-200">اللون</span>
                <input
                  v-model="formData.car_color"
                  type="text"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600"
                />
              </label>

              <label class="block">
                <span class="text-sm font-semibold dark:text-gray-200">رقم السيارة</span>
                <input
                  v-model="formData.car_number"
                  type="text"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600"
                />
              </label>

              <label class="block">
                <span class="text-sm font-semibold dark:text-gray-200">التاريخ</span>
                <input
                  v-model="formData.date"
                  type="date"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600"
                />
              </label>

              <label class="block">
                <span class="text-sm font-semibold dark:text-gray-200">مدفوع دولار</span>
                <input
                  v-model.number="formData.paid_dollar"
                  type="number"
                  min="0"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600"
                />
              </label>

              <label class="block">
                <span class="text-sm font-semibold dark:text-gray-200">مدفوع دينار</span>
                <input
                  v-model.number="formData.paid_dinar"
                  type="number"
                  min="0"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600"
                />
              </label>

              <label class="block md:col-span-2">
                <span class="text-sm font-semibold dark:text-gray-200">ملاحظة (اختياري)</span>
                <input
                  v-model="formData.note"
                  type="text"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600"
                />
              </label>
            </div>

            <div class="flex justify-center gap-3 mt-5">
              <button
                type="button"
                class="px-5 py-2 rounded-md bg-emerald-600 text-white font-semibold hover:bg-emerald-700"
                @click="submit"
              >
                حفظ
              </button>
              <button
                type="button"
                class="px-5 py-2 rounded-md bg-gray-500 text-white font-semibold hover:bg-gray-600"
                @click="emit('close')"
              >
                إلغاء
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </Transition>
</template>

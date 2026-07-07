<script setup>
import { ref, watch } from 'vue';
import axios from 'axios';
import debounce from 'lodash/debounce';
import { useToast } from 'vue-toastification';

const props = defineProps({
  show: Boolean,
  formData: {
    type: Object,
    default: () => ({}),
  },
});

const emit = defineEmits(['save', 'close']);
const toast = useToast();

const isSearching = ref(false);

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

const searchByVin = debounce(async (vin) => {
  const q = String(vin ?? '').trim();
  if (q.length < 4 || !props.formData) return;

  isSearching.value = true;
  try {
    const response = await axios.get('/api/getIndexCarSearch', { params: { q } });
    const rows = Array.isArray(response.data) ? response.data : (response.data?.data ?? []);
    const exact = rows.find((c) => String(c.vin ?? '').toUpperCase() === q.toUpperCase())
      || rows.find((c) => String(c.vin ?? '').toUpperCase().endsWith(q.toUpperCase()))
      || rows[0];

    if (!exact) return;

    if (exact.client?.name) props.formData.dealer_name = exact.client.name;
    if (exact.car_type) props.formData.car_type = exact.car_type;
    if (exact.year) props.formData.year = exact.year;
    if (exact.car_color) props.formData.car_color = exact.car_color;
    if (exact.car_number) props.formData.car_number = exact.car_number;
    if (exact.vin) props.formData.vin = exact.vin;
  } catch (error) {
    console.error(error);
  } finally {
    isSearching.value = false;
  }
}, 400);

watch(
  () => props.formData?.vin,
  (val) => {
    if (!props.show) return;
    searchByVin(val);
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
          <div class="modal-header px-4 pt-4">
            <h2 class="text-center text-lg font-bold dark:text-gray-100">
              {{ formData?.id ? 'تعديل سيارة خارجية' : 'إضافة سيارة خارجية' }}
            </h2>
            <p class="text-center text-xs text-gray-500 dark:text-gray-400 mt-1 px-2">
              أدخل الشانصي أولاً لملء البيانات تلقائياً — بدون ربط بجدول السيارات
            </p>
          </div>

          <div class="modal-body px-4 pb-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
              <label class="block md:col-span-2">
                <span class="text-sm font-semibold dark:text-gray-200">الشانصي (VIN)</span>
                <div class="relative mt-1">
                  <input
                    v-model="formData.vin"
                    type="text"
                    dir="ltr"
                    class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm dark:bg-gray-800 dark:border-gray-600 dark:text-gray-100"
                    placeholder="بحث فوري بالشانصي"
                    autocomplete="off"
                  />
                  <span
                    v-if="isSearching"
                    class="absolute left-3 top-1/2 -translate-y-1/2 text-xs text-gray-400"
                  >
                    بحث...
                  </span>
                </div>
              </label>

              <label class="block">
                <span class="text-sm font-semibold dark:text-gray-200">تاجر</span>
                <input
                  v-model="formData.dealer_name"
                  type="text"
                  class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-gray-100"
                  placeholder="اسم التاجر"
                />
              </label>

              <label class="block">
                <span class="text-sm font-semibold dark:text-gray-200">نوع السيارة</span>
                <input
                  v-model="formData.car_type"
                  type="text"
                  class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-gray-100"
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
                  class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-gray-100"
                />
              </label>

              <label class="block">
                <span class="text-sm font-semibold dark:text-gray-200">اللون</span>
                <input
                  v-model="formData.car_color"
                  type="text"
                  class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-gray-100"
                />
              </label>

              <label class="block">
                <span class="text-sm font-semibold dark:text-gray-200">رقم السيارة</span>
                <input
                  v-model="formData.car_number"
                  type="text"
                  class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-gray-100"
                />
              </label>

              <label class="block">
                <span class="text-sm font-semibold dark:text-gray-200">التاريخ</span>
                <input
                  v-model="formData.date"
                  type="date"
                  class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-gray-100"
                />
              </label>

              <label class="block">
                <span class="text-sm font-semibold dark:text-gray-200">مدفوع $</span>
                <input
                  v-model.number="formData.paid_dollar"
                  type="number"
                  min="0"
                  class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-gray-100"
                />
              </label>

              <label class="block">
                <span class="text-sm font-semibold dark:text-gray-200">مدفوع د.</span>
                <input
                  v-model.number="formData.paid_dinar"
                  type="number"
                  min="0"
                  class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-gray-100"
                />
              </label>

              <label class="block md:col-span-2">
                <span class="text-sm font-semibold dark:text-gray-200">ملاحظة</span>
                <input
                  v-model="formData.note"
                  type="text"
                  class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-gray-100"
                />
              </label>
            </div>

            <div class="flex justify-center gap-3 mt-5">
              <button
                type="button"
                class="px-6 py-2.5 rounded-lg bg-emerald-600 text-white font-semibold hover:bg-emerald-700"
                @click="submit"
              >
                حفظ
              </button>
              <button
                type="button"
                class="px-6 py-2.5 rounded-lg bg-gray-500 text-white font-semibold hover:bg-gray-600"
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

<style scoped>
.modal-mask {
  position: fixed;
  z-index: 9998;
  inset: 0;
  background-color: rgba(0, 0, 0, 0.5);
  display: table;
}

.modal-wrapper {
  display: table-cell;
  vertical-align: middle;
}

.modal-container {
  width: min(560px, 94vw);
  margin: 0 auto;
  background-color: #fff;
  border-radius: 12px;
  box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}
</style>

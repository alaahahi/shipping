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
        <div class="modal-container external-car-modal overflow-auto max-h-[85vh]">
          <div class="modal-header px-4 pt-4">
            <h2 class="modal-title text-center text-lg font-bold">
              {{ formData?.id ? 'تعديل سيارة خارجية' : 'إضافة سيارة خارجية' }}
            </h2>
            <p class="modal-subtitle text-center text-xs mt-1 px-2">
              أدخل الشانصي أولاً لملء البيانات تلقائياً — بدون ربط بجدول السيارات
            </p>
          </div>

          <div class="modal-body px-4 pb-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
              <label class="block md:col-span-2">
                <span class="modal-field-label text-sm font-semibold">الشانصي (VIN)</span>
                <div class="relative mt-1">
                  <input
                    v-model="formData.vin"
                    type="text"
                    dir="ltr"
                    class="modal-field-input block w-full rounded-lg px-3 py-2 text-sm shadow-sm"
                    placeholder="بحث فوري بالشانصي"
                    autocomplete="off"
                  />
                  <span
                    v-if="isSearching"
                    class="modal-search-hint absolute left-3 top-1/2 -translate-y-1/2 text-xs"
                  >
                    بحث...
                  </span>
                </div>
              </label>

              <label class="block">
                <span class="modal-field-label text-sm font-semibold">تاجر</span>
                <input
                  v-model="formData.dealer_name"
                  type="text"
                  class="modal-field-input mt-1 block w-full rounded-lg px-3 py-2 text-sm"
                  placeholder="اسم التاجر"
                />
              </label>

              <label class="block">
                <span class="modal-field-label text-sm font-semibold">نوع السيارة</span>
                <input
                  v-model="formData.car_type"
                  type="text"
                  class="modal-field-input mt-1 block w-full rounded-lg px-3 py-2 text-sm"
                  placeholder="مثال: تويوتا كامري"
                />
              </label>

              <label class="block">
                <span class="modal-field-label text-sm font-semibold">السنة</span>
                <input
                  v-model="formData.year"
                  type="number"
                  min="1900"
                  max="2100"
                  class="modal-field-input mt-1 block w-full rounded-lg px-3 py-2 text-sm"
                />
              </label>

              <label class="block">
                <span class="modal-field-label text-sm font-semibold">اللون</span>
                <input
                  v-model="formData.car_color"
                  type="text"
                  class="modal-field-input mt-1 block w-full rounded-lg px-3 py-2 text-sm"
                />
              </label>

              <label class="block">
                <span class="modal-field-label text-sm font-semibold">رقم السيارة</span>
                <input
                  v-model="formData.car_number"
                  type="text"
                  class="modal-field-input mt-1 block w-full rounded-lg px-3 py-2 text-sm"
                />
              </label>

              <label class="block">
                <span class="modal-field-label text-sm font-semibold">التاريخ</span>
                <input
                  v-model="formData.date"
                  type="date"
                  class="modal-field-input mt-1 block w-full rounded-lg px-3 py-2 text-sm"
                />
              </label>

              <label class="block">
                <span class="modal-field-label text-sm font-semibold">مدفوع $</span>
                <input
                  v-model.number="formData.paid_dollar"
                  type="number"
                  min="0"
                  class="modal-field-input mt-1 block w-full rounded-lg px-3 py-2 text-sm"
                />
              </label>

              <label class="block">
                <span class="modal-field-label text-sm font-semibold">مدفوع د.</span>
                <input
                  v-model.number="formData.paid_dinar"
                  type="number"
                  min="0"
                  class="modal-field-input mt-1 block w-full rounded-lg px-3 py-2 text-sm"
                />
              </label>

              <label class="block md:col-span-2">
                <span class="modal-field-label text-sm font-semibold">ملاحظة</span>
                <input
                  v-model="formData.note"
                  type="text"
                  class="modal-field-input mt-1 block w-full rounded-lg px-3 py-2 text-sm"
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
  color: #111827;
}

.modal-title {
  color: #111827;
}

.modal-subtitle {
  color: #6b7280;
}

.modal-field-label {
  color: #374151;
}

.modal-field-input {
  border: 1px solid #d1d5db;
  background: #fff;
  color: #111827;
}

.modal-field-input:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.25);
}

.modal-field-input::placeholder {
  color: #9ca3af;
}

.modal-search-hint {
  color: #9ca3af;
}

:global(.dark) .external-car-modal {
  background: #111827;
  border: 1px solid #6b7280;
  color: #f9fafb;
  color-scheme: dark;
  box-shadow: 0 8px 30px rgba(0, 0, 0, 0.65);
}

:global(.dark) .external-car-modal .modal-title {
  color: #ffffff !important;
}

:global(.dark) .external-car-modal .modal-subtitle {
  color: #e5e7eb !important;
}

:global(.dark) .external-car-modal .modal-field-label {
  color: #f3f4f6 !important;
  font-weight: 700;
}

:global(.dark) .external-car-modal .modal-field-input {
  background: #030712 !important;
  border-color: #6b7280 !important;
  color: #ffffff !important;
  font-weight: 500;
}

:global(.dark) .external-car-modal .modal-field-input::placeholder {
  color: #9ca3af !important;
}

:global(.dark) .external-car-modal .modal-search-hint {
  color: #d1d5db !important;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}
</style>

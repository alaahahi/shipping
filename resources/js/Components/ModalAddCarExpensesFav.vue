<script setup>
import { ref, watch, computed } from 'vue';
import axios from 'axios';
import debounce from 'lodash/debounce';
import { useToast } from 'vue-toastification';

const props = defineProps({
  show: Boolean,
  formData: Object,
  client: Array,
  saveCar: Boolean,
});

const emit = defineEmits(['a', 'close']);
const toast = useToast();

const vinQuery = ref('');
const dealerFilter = ref(0);
const searchResults = ref([]);
const selectedCar = ref(null);
const isSearching = ref(false);
const isAdding = ref(false);

function getTodayDate() {
  const today = new Date();
  const year = today.getFullYear();
  const month = String(today.getMonth() + 1).padStart(2, '0');
  const day = String(today.getDate()).padStart(2, '0');
  return `${year}-${month}-${day}`;
}

function resetForm() {
  vinQuery.value = '';
  dealerFilter.value = 0;
  searchResults.value = [];
  selectedCar.value = null;
  isSearching.value = false;
  isAdding.value = false;
  if (props.formData) {
    props.formData.carId = null;
    props.formData.date = '';
  }
}

watch(
  () => props.show,
  (visible) => {
    if (visible) resetForm();
  },
);

const filteredResults = computed(() => {
  if (!dealerFilter.value) return searchResults.value;
  return searchResults.value.filter((c) => Number(c.client_id) === Number(dealerFilter.value));
});

function carLabel(car) {
  const parts = [
    car.car_type,
    car.car_color,
    car.year,
    car.car_number ? `كاتي ${car.car_number}` : null,
    car.vin ? `شانصي ${car.vin}` : null,
  ].filter(Boolean);
  return parts.join(' — ');
}

function selectCar(car) {
  selectedCar.value = car;
  if (props.formData) {
    props.formData.carId = car.id;
  }
}

const searchByVin = debounce(async (term) => {
  const q = String(term ?? '').trim();
  if (q.length < 3) {
    searchResults.value = [];
    selectedCar.value = null;
    return;
  }

  isSearching.value = true;
  try {
    const response = await axios.get('/api/getIndexCarSearch', {
      params: { q, for_registration: 1 },
    });
    const rows = Array.isArray(response.data) ? response.data : (response.data?.data ?? []);
    searchResults.value = rows.filter((c) => Number(c.car_have_expenses) === 0);

    const exact = searchResults.value.find(
      (c) => String(c.vin ?? '').toUpperCase() === q.toUpperCase(),
    );
    if (exact) selectCar(exact);
  } catch (error) {
    console.error(error);
    toast.error('تعذر البحث', { timeout: 2500, position: 'bottom-right', rtl: true });
  } finally {
    isSearching.value = false;
  }
}, 350);

watch(vinQuery, (val) => {
  selectedCar.value = null;
  if (props.formData) props.formData.carId = null;
  searchByVin(val);
});

function submitAdd() {
  if (!selectedCar.value?.id || isAdding.value) return;
  isAdding.value = true;

  const payload = {
    ...props.formData,
    carId: selectedCar.value.id,
    date: props.formData?.date || getTodayDate(),
  };

  emit('a', payload);
  isAdding.value = false;
}

function closeModal() {
  resetForm();
  emit('close');
}
</script>

<template>
  <Transition name="modal">
    <div v-if="show" class="modal-mask">
      <div class="modal-wrapper max-h-[85vh]">
        <div class="modal-container registration-modal overflow-auto max-h-[85vh]">
          <div class="modal-header px-4 pt-4">
            <h2 class="modal-title text-center text-lg font-bold">
              إضافة سيارة لتسجيل المصاريف
            </h2>
            <p class="modal-subtitle text-center text-xs mt-1">
              ابحث بالشانصي مباشرة — أسرع من اختيار التاجر
            </p>
          </div>

          <div class="modal-body px-4 pb-2">
            <div class="space-y-3">
              <label class="block">
                <span class="modal-field-label text-sm font-semibold">الشانصي (VIN)</span>
                <div class="relative mt-1">
                  <input
                    v-model="vinQuery"
                    type="text"
                    dir="ltr"
                    class="modal-field-input block w-full rounded-lg px-3 py-2.5 text-sm shadow-sm"
                    placeholder="أدخل آخر 6 أرقام أو الشانصي كاملاً"
                    autocomplete="off"
                  />
                  <span
                    v-if="isSearching"
                    class="modal-search-hint absolute left-3 top-1/2 -translate-y-1/2 text-xs"
                  >
                    جاري البحث...
                  </span>
                </div>
              </label>

              <label class="block">
                <span class="modal-field-label text-sm font-semibold">تاجر (فلتر اختياري)</span>
                <select
                  v-model="dealerFilter"
                  class="modal-field-select mt-1 block w-full rounded-lg px-3 py-2 text-sm"
                >
                  <option :value="0">الكل</option>
                  <option v-for="c in client" :key="c.id" :value="c.id">{{ c.name }}</option>
                </select>
              </label>

              <div
                v-if="vinQuery.trim().length >= 3 && !isSearching && filteredResults.length === 0"
                class="modal-alert modal-alert--warn rounded-lg px-3 py-2 text-sm"
              >
                لا توجد سيارة متاحة لهذا الشانصي (قد تكون مضافة مسبقاً)
              </div>

              <div v-if="filteredResults.length" class="space-y-2 max-h-52 overflow-y-auto">
                <button
                  v-for="car in filteredResults"
                  :key="car.id"
                  type="button"
                  class="modal-result-card w-full text-right rounded-lg border px-3 py-2.5 text-sm transition"
                  :class="{ 'modal-result-card--selected': selectedCar?.id === car.id }"
                  @click="selectCar(car)"
                >
                  <div class="modal-result-title font-semibold">{{ carLabel(car) }}</div>
                  <div class="modal-result-sub text-xs mt-0.5">
                    {{ car.client?.name || '—' }}
                  </div>
                </button>
              </div>

              <div
                v-if="selectedCar"
                class="modal-alert modal-alert--ok rounded-lg px-3 py-2 text-sm"
              >
                <span class="font-semibold">محددة: </span>
                <span>{{ carLabel(selectedCar) }}</span>
              </div>
            </div>

            <div v-if="!saveCar" class="mt-5">
              <button
                type="button"
                class="w-full rounded-lg py-3 font-semibold text-white transition"
                :class="selectedCar ? 'bg-blue-600 hover:bg-blue-700' : 'bg-gray-400 cursor-not-allowed'"
                :disabled="!selectedCar || isAdding"
                @click="submitAdd"
              >
                إضافة ومتابعة
              </button>
            </div>
          </div>

          <div class="modal-footer px-4 pb-4">
            <button
              type="button"
              class="w-full rounded-lg py-2.5 font-semibold text-white bg-rose-500 hover:bg-rose-600"
              @click="closeModal"
            >
              إغلاق
            </button>
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
  transition: opacity 0.3s ease;
}

.modal-wrapper {
  display: table-cell;
  vertical-align: middle;
}

.modal-container {
  width: min(520px, 94vw);
  margin: 0 auto;
  padding: 0 0 8px;
  background-color: #fff;
  border-radius: 12px;
  box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
  transition: all 0.3s ease;
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

.modal-field-input,
.modal-field-select {
  border: 1px solid #d1d5db;
  background: #fff;
  color: #111827;
}

.modal-field-input:focus,
.modal-field-select:focus {
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

.modal-result-card {
  border: 1px solid #e5e7eb;
  background: #fff;
  color: #111827;
}

.modal-result-card:hover {
  border-color: #cbd5e1;
  background: #f9fafb;
}

.modal-result-card--selected {
  border-color: #3b82f6;
  background: #eff6ff;
}

.modal-result-sub {
  color: #6b7280;
}

.modal-alert--warn {
  border: 1px solid #fcd34d;
  background: #fffbeb;
  color: #92400e;
}

.modal-alert--ok {
  border: 1px solid #6ee7b7;
  background: #ecfdf5;
  color: #065f46;
}

:global(.dark) .registration-modal {
  background: #111827;
  border: 1px solid #6b7280;
  color: #f9fafb;
  color-scheme: dark;
  box-shadow: 0 8px 30px rgba(0, 0, 0, 0.65);
}

:global(.dark) .registration-modal .modal-title {
  color: #ffffff !important;
}

:global(.dark) .registration-modal .modal-subtitle {
  color: #e5e7eb !important;
}

:global(.dark) .registration-modal .modal-field-label {
  color: #f3f4f6 !important;
  font-weight: 700;
}

:global(.dark) .registration-modal .modal-field-input,
:global(.dark) .registration-modal .modal-field-select {
  background: #030712 !important;
  border-color: #6b7280 !important;
  color: #ffffff !important;
  font-weight: 500;
}

:global(.dark) .registration-modal .modal-field-input::placeholder {
  color: #9ca3af !important;
}

:global(.dark) .registration-modal .modal-field-select option {
  background: #111827;
  color: #f9fafb;
}

:global(.dark) .registration-modal .modal-search-hint {
  color: #d1d5db !important;
}

:global(.dark) .registration-modal .modal-result-card {
  background: #1f2937 !important;
  border-color: #6b7280 !important;
  color: #ffffff !important;
}

:global(.dark) .registration-modal .modal-result-card:hover {
  background: #374151 !important;
  border-color: #9ca3af !important;
}

:global(.dark) .registration-modal .modal-result-card--selected {
  background: #1e3a8a !important;
  border-color: #60a5fa !important;
}

:global(.dark) .registration-modal .modal-result-title {
  color: #ffffff !important;
  font-weight: 700;
}

:global(.dark) .registration-modal .modal-result-sub {
  color: #e5e7eb !important;
}

:global(.dark) .registration-modal .modal-alert--warn {
  background: #451a03 !important;
  border-color: #fbbf24 !important;
  color: #fde68a !important;
}

:global(.dark) .registration-modal .modal-alert--ok {
  background: #064e3b !important;
  border-color: #34d399 !important;
  color: #d1fae5 !important;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

.modal-enter-from .modal-container,
.modal-leave-to .modal-container {
  transform: scale(1.03);
}
</style>

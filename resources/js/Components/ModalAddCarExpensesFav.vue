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
        <div class="modal-container dark:bg-gray-900 overflow-auto max-h-[85vh]">
          <div class="modal-header px-4 pt-4">
            <h2 class="text-center text-lg font-bold dark:text-gray-100">
              إضافة سيارة لتسجيل المصاريف
            </h2>
            <p class="text-center text-xs text-gray-500 dark:text-gray-400 mt-1">
              ابحث بالشانصي مباشرة — أسرع من اختيار التاجر
            </p>
          </div>

          <div class="modal-body px-4 pb-2">
            <div class="space-y-3">
              <label class="block">
                <span class="text-sm font-semibold dark:text-gray-200">الشانصي (VIN)</span>
                <div class="relative mt-1">
                  <input
                    v-model="vinQuery"
                    type="text"
                    dir="ltr"
                    class="block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-100"
                    placeholder="أدخل آخر 6 أرقام أو الشانصي كاملاً"
                    autocomplete="off"
                  />
                  <span
                    v-if="isSearching"
                    class="absolute left-3 top-1/2 -translate-y-1/2 text-xs text-gray-400"
                  >
                    جاري البحث...
                  </span>
                </div>
              </label>

              <label class="block">
                <span class="text-sm font-semibold dark:text-gray-200">تاجر (فلتر اختياري)</span>
                <select
                  v-model="dealerFilter"
                  class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-gray-100"
                >
                  <option :value="0">الكل</option>
                  <option v-for="c in client" :key="c.id" :value="c.id">{{ c.name }}</option>
                </select>
              </label>

              <div
                v-if="vinQuery.trim().length >= 3 && !isSearching && filteredResults.length === 0"
                class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-800 dark:border-amber-800 dark:bg-amber-950/40 dark:text-amber-200"
              >
                لا توجد سيارة متاحة لهذا الشانصي (قد تكون مضافة مسبقاً)
              </div>

              <div v-if="filteredResults.length" class="space-y-2 max-h-52 overflow-y-auto">
                <button
                  v-for="car in filteredResults"
                  :key="car.id"
                  type="button"
                  class="w-full text-right rounded-lg border px-3 py-2.5 text-sm transition"
                  :class="selectedCar?.id === car.id
                    ? 'border-blue-500 bg-blue-50 dark:bg-blue-950/50 dark:border-blue-400'
                    : 'border-gray-200 hover:border-gray-300 dark:border-gray-700 dark:hover:border-gray-600'"
                  @click="selectCar(car)"
                >
                  <div class="font-semibold dark:text-gray-100">{{ carLabel(car) }}</div>
                  <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                    {{ car.client?.name || '—' }}
                  </div>
                </button>
              </div>

              <div
                v-if="selectedCar"
                class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm dark:border-emerald-800 dark:bg-emerald-950/30"
              >
                <span class="font-semibold text-emerald-800 dark:text-emerald-200">محددة: </span>
                <span class="dark:text-gray-200">{{ carLabel(selectedCar) }}</span>
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

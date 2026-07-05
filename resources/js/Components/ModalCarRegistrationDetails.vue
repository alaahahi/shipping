<script setup>
import { ref, watch } from 'vue';
import axios from 'axios';
import print from '@/Components/icon/print.vue';

const props = defineProps({
  show: Boolean,
  carId: [Number, String],
});

const emit = defineEmits(['close']);

const loading = ref(false);
const details = ref(null);

function formatNumber(n) {
  return new Intl.NumberFormat('en-US').format(Number(n) || 0);
}

watch(() => props.show, (visible) => {
  if (visible && props.carId) {
    loadDetails();
  } else {
    details.value = null;
  }
});

async function loadDetails() {
  loading.value = true;
  try {
    const response = await axios.get('/api/getCarRegistrationDetails', {
      params: { car_id: props.carId },
    });
    details.value = response.data;
  } catch (error) {
    console.error(error);
    details.value = null;
  } finally {
    loading.value = false;
  }
}
</script>

<template>
  <Transition name="modal">
    <div v-if="show" class="modal-mask">
      <div class="modal-wrapper max-h-[90vh]">
        <div class="modal-container registration-details-modal dark:bg-gray-900 overflow-auto max-h-[90vh]">
          <div class="modal-header registration-details-header">
            <h2 class="registration-details-title text-center py-4 text-lg font-bold">تفاصيل التسجيل</h2>
            <p v-if="details?.car" class="registration-details-subtitle text-center text-sm font-semibold px-4">
              {{ details.car.car_type }} — شانص {{ details.car.vin }} — رقم {{ details.car.car_number }}
            </p>
            <p v-if="details?.link_exchange_rate" class="registration-details-rate text-center text-sm font-bold text-amber-700 dark:text-amber-300 px-4 mt-1">
              سعر التحويل عند الربط: {{ formatNumber(details.link_exchange_rate) }}
            </p>
          </div>

          <div class="modal-body px-4 pb-4">
            <div v-if="loading" class="registration-details-loading text-center py-8 font-medium">جاري التحميل...</div>

            <template v-else-if="details?.has_registration">
              <div class="registration-details-summary grid grid-cols-2 gap-3 mb-4 p-3 rounded-lg bg-blue-50 dark:bg-blue-950/80 border border-blue-200 dark:border-blue-700 text-center font-bold">
                <div class="registration-details-summary-dollar">
                  <span class="registration-details-label">دولار:</span>
                  {{ formatNumber(details.total_dollar) }} $
                </div>
                <div class="registration-details-summary-dinar">
                  <span class="registration-details-label">دينار:</span>
                  {{ formatNumber(details.total_dinar) }} د
                </div>
              </div>

              <div class="overflow-x-auto rounded-lg border border-gray-300 dark:border-gray-600">
                <table class="registration-details-table w-full text-sm text-center">
                  <thead class="bg-emerald-600 dark:bg-emerald-800 text-white">
                    <tr>
                      <th class="px-2 py-2 font-semibold">التاريخ</th>
                      <th class="px-2 py-2 font-semibold">دولار</th>
                      <th class="px-2 py-2 font-semibold">دينار</th>
                      <th class="px-2 py-2 font-semibold">ملاحظة</th>
                      <th class="px-2 py-2 font-semibold">عبر</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr
                      v-for="expense in details.expenses"
                      :key="expense.id"
                      class="registration-details-row border-t border-gray-200 dark:border-gray-600"
                    >
                      <td class="registration-details-cell px-2 py-2">{{ expense.created }}</td>
                      <td class="registration-details-cell registration-details-dollar px-2 py-2 font-semibold">
                        {{ formatNumber(expense.amount_dollar) }}
                      </td>
                      <td class="registration-details-cell registration-details-dinar px-2 py-2 font-semibold">
                        {{ formatNumber(expense.amount_dinar) }}
                      </td>
                      <td class="registration-details-cell registration-details-note px-2 py-2 text-right text-xs max-w-[10rem]">{{ expense.note }}</td>
                      <td class="registration-details-cell px-2 py-2">{{ expense.user?.name }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>

              <div class="text-center mt-4">
                <a
                  target="_blank"
                  :href="`/api/getIndexExpensesPrint?car_id=${details.car.id}`"
                  class="inline-flex items-center gap-1 px-4 py-2 bg-blue-600 text-white rounded font-semibold"
                >
                  طباعة كاملة
                  <print />
                </a>
              </div>
            </template>

            <p v-else class="registration-details-empty text-center py-6 font-medium">لا توجد مصاريف تسجيل مسجّلة لهذه السيارة</p>
          </div>

          <div class="modal-footer px-4 pb-4">
            <button
              type="button"
              class="w-full py-3 bg-gray-500 text-white rounded"
              @click="emit('close')"
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
  background-color: rgba(0, 0, 0, 0.55);
  display: table;
}

.modal-wrapper {
  display: table-cell;
  vertical-align: middle;
}

.modal-container {
  width: 96%;
  max-width: 720px;
  margin: 0 auto;
  padding: 16px 20px 24px;
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 8px 30px rgba(0, 0, 0, 0.25);
}

:global(.dark) .modal-container {
  background: #111827;
  border: 1px solid #4b5563;
  box-shadow: 0 8px 30px rgba(0, 0, 0, 0.55);
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

.registration-details-title {
  color: #111827 !important;
}

.registration-details-subtitle {
  color: #1f2937 !important;
}

.registration-details-rate {
  color: #b45309 !important;
}

.registration-details-loading,
.registration-details-empty {
  color: #374151 !important;
}

.registration-details-label {
  color: #374151 !important;
  font-weight: 600;
}

.registration-details-summary-dollar {
  color: #166534 !important;
}

.registration-details-summary-dinar {
  color: #1e40af !important;
}

.registration-details-table {
  color: #111827 !important;
}

.registration-details-table thead th {
  color: #ffffff !important;
}

.registration-details-row {
  background-color: #ffffff;
}

.registration-details-row:nth-child(even) {
  background-color: #f3f4f6;
}

.registration-details-cell {
  color: #111827 !important;
}

.registration-details-dollar {
  color: #166534 !important;
}

.registration-details-dinar {
  color: #1e40af !important;
}

.registration-details-note {
  color: #1f2937 !important;
}

:global(.dark) .registration-details-title {
  color: #f9fafb !important;
}

:global(.dark) .registration-details-subtitle {
  color: #e5e7eb !important;
}

:global(.dark) .registration-details-rate {
  color: #fcd34d !important;
}

:global(.dark) .registration-details-loading,
:global(.dark) .registration-details-empty {
  color: #d1d5db !important;
}

:global(.dark) .registration-details-label {
  color: #d1d5db !important;
}

:global(.dark) .registration-details-summary-dollar {
  color: #86efac !important;
}

:global(.dark) .registration-details-summary-dinar {
  color: #93c5fd !important;
}

:global(.dark) .registration-details-table {
  color: #f3f4f6 !important;
}

:global(.dark) .registration-details-row {
  background-color: #111827;
}

:global(.dark) .registration-details-row:nth-child(even) {
  background-color: #1f2937;
}

:global(.dark) .registration-details-cell {
  color: #f3f4f6 !important;
}

:global(.dark) .registration-details-dollar {
  color: #86efac !important;
}

:global(.dark) .registration-details-dinar {
  color: #93c5fd !important;
}

:global(.dark) .registration-details-note {
  color: #e5e7eb !important;
}
</style>

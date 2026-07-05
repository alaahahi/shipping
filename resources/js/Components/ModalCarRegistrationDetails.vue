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
        <div class="modal-container dark:bg-gray-900 overflow-auto max-h-[90vh]">
          <div class="modal-header">
            <h2 class="text-center py-4 text-lg font-bold text-gray-900 dark:text-white">تفاصيل التسجيل</h2>
            <p v-if="details?.car" class="text-center text-sm text-gray-800 dark:text-gray-200 px-4">
              {{ details.car.car_type }} — شانص {{ details.car.vin }} — رقم {{ details.car.car_number }}
            </p>
            <p v-if="details?.link_exchange_rate" class="text-center text-sm font-bold text-amber-700 dark:text-amber-300 px-4 mt-1">
              سعر التحويل عند الربط: {{ formatNumber(details.link_exchange_rate) }}
            </p>
          </div>

          <div class="modal-body px-4 pb-4">
            <div v-if="loading" class="text-center py-8 text-gray-600 dark:text-gray-300">جاري التحميل...</div>

            <template v-else-if="details?.has_registration">
              <div class="grid grid-cols-2 gap-3 mb-4 p-3 rounded-lg bg-blue-50 dark:bg-blue-950/80 border border-blue-200 dark:border-blue-700 text-center font-bold">
                <div class="text-green-800 dark:text-green-300">
                  دولار: {{ formatNumber(details.total_dollar) }} $
                </div>
                <div class="text-blue-800 dark:text-blue-300">
                  دينار: {{ formatNumber(details.total_dinar) }} د
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
                      <td class="px-2 py-2">{{ expense.created }}</td>
                      <td class="px-2 py-2 text-green-800 dark:text-green-300 font-semibold">
                        {{ formatNumber(expense.amount_dollar) }}
                      </td>
                      <td class="px-2 py-2 text-blue-800 dark:text-blue-300 font-semibold">
                        {{ formatNumber(expense.amount_dinar) }}
                      </td>
                      <td class="px-2 py-2 text-right text-xs max-w-[10rem]">{{ expense.note }}</td>
                      <td class="px-2 py-2">{{ expense.user?.name }}</td>
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

            <p v-else class="text-center py-6 text-gray-500 dark:text-gray-300">لا توجد مصاريف تسجيل مسجّلة لهذه السيارة</p>
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

.registration-details-table {
  color: #111827;
}

.registration-details-table thead th {
  color: #ffffff;
}

.registration-details-row {
  background-color: #ffffff;
  color: #111827;
}

.registration-details-row:nth-child(even) {
  background-color: #f3f4f6;
}

:global(.dark) .registration-details-table {
  color: #f3f4f6;
}

:global(.dark) .registration-details-row {
  background-color: #111827;
  color: #f3f4f6;
}

:global(.dark) .registration-details-row:nth-child(even) {
  background-color: #1f2937;
}
</style>

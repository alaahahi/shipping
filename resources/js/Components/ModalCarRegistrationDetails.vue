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
            <h2 class="text-center py-4 text-lg font-bold dark:text-white">تفاصيل التسجيل</h2>
            <p v-if="details?.car" class="text-center text-sm text-gray-600 dark:text-gray-300 px-4">
              {{ details.car.car_type }} — شانص {{ details.car.vin }} — رقم {{ details.car.car_number }}
            </p>
          </div>

          <div class="modal-body px-4 pb-4">
            <div v-if="loading" class="text-center py-8 text-gray-500">جاري التحميل...</div>

            <template v-else-if="details?.has_registration">
              <div class="grid grid-cols-2 gap-3 mb-4 p-3 rounded-lg bg-blue-50 dark:bg-blue-950 text-center font-bold">
                <div class="text-green-700 dark:text-green-400">
                  دولار: {{ formatNumber(details.total_dollar) }} $
                </div>
                <div class="text-blue-700 dark:text-blue-400">
                  دينار: {{ formatNumber(details.total_dinar) }} د
                </div>
              </div>

              <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                <table class="w-full text-sm text-center">
                  <thead class="bg-emerald-600 text-white">
                    <tr>
                      <th class="px-2 py-2">التاريخ</th>
                      <th class="px-2 py-2">دولار</th>
                      <th class="px-2 py-2">دينار</th>
                      <th class="px-2 py-2">ملاحظة</th>
                      <th class="px-2 py-2">عبر</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr
                      v-for="expense in details.expenses"
                      :key="expense.id"
                      class="border-t dark:border-gray-700 even:bg-gray-50 dark:even:bg-gray-800"
                    >
                      <td class="px-2 py-2">{{ expense.created }}</td>
                      <td class="px-2 py-2 text-green-700 dark:text-green-400 font-semibold">
                        {{ formatNumber(expense.amount_dollar) }}
                      </td>
                      <td class="px-2 py-2 text-blue-700 dark:text-blue-400 font-semibold">
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

            <p v-else class="text-center py-6 text-gray-500">لا توجد مصاريف تسجيل مسجّلة لهذه السيارة</p>
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

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}
</style>

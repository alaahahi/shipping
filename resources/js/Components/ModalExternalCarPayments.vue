<script setup>
import { ref, watch, computed } from 'vue';
import axios from 'axios';
import { useToast } from 'vue-toastification';
import trash from '@/Components/icon/trash.vue';
import print from '@/Components/icon/print.vue';

const props = defineProps({
  show: Boolean,
  car: {
    type: Object,
    default: null,
  },
});

const emit = defineEmits(['close', 'updated']);
const toast = useToast();

const loading = ref(false);
const saving = ref(false);
const payments = ref([]);
const paidDollar = ref(0);
const paidDinar = ref(0);

const form = ref({
  amount_dollar: '',
  amount_dinar: '',
  note: '',
  created: getTodayDate(),
});

function getTodayDate() {
  const today = new Date();
  const year = today.getFullYear();
  const month = String(today.getMonth() + 1).padStart(2, '0');
  const day = String(today.getDate()).padStart(2, '0');
  return `${year}-${month}-${day}`;
}

function formatNumber(n) {
  return new Intl.NumberFormat('en-US').format(Number(n) || 0);
}

function formatDate(value) {
  if (!value) return '';
  return String(value).includes('T') ? String(value).split('T')[0] : String(value);
}

const carTitle = computed(() => {
  if (!props.car) return '';
  return [props.car.car_type, props.car.car_number].filter(Boolean).join(' — ');
});

function resetForm() {
  form.value = {
    amount_dollar: '',
    amount_dinar: '',
    note: '',
    created: getTodayDate(),
  };
}

async function loadPayments() {
  if (!props.car?.id) return;
  loading.value = true;
  try {
    const { data } = await axios.get('/api/getExternalCarPayments', {
      params: { external_car_id: props.car.id },
    });
    payments.value = data.payments || [];
    paidDollar.value = Number(data.paid_dollar) || 0;
    paidDinar.value = Number(data.paid_dinar) || 0;
  } catch (e) {
    toast.error(e.response?.data?.error || 'تعذر تحميل الدفعات');
  } finally {
    loading.value = false;
  }
}

watch(
  () => [props.show, props.car?.id],
  ([visible]) => {
    if (visible && props.car?.id) {
      resetForm();
      loadPayments();
    }
  },
);

async function addPayment() {
  if (!props.car?.id || saving.value) return;
  const amountDollar = Number(form.value.amount_dollar) || 0;
  const amountDinar = Number(form.value.amount_dinar) || 0;
  if (amountDollar <= 0 && amountDinar <= 0) {
    toast.error('أدخل مبلغاً بالدولار أو الدينار');
    return;
  }

  saving.value = true;
  try {
    const { data } = await axios.post('/api/storeExternalCarPayment', {
      external_car_id: props.car.id,
      amount_dollar: amountDollar,
      amount_dinar: amountDinar,
      note: form.value.note,
      created: form.value.created,
    });
    payments.value = [data.payment, ...payments.value];
    paidDollar.value = Number(data.car?.paid_dollar) || 0;
    paidDinar.value = Number(data.car?.paid_dinar) || 0;
    resetForm();
    emit('updated', data.car);
    toast.success('تمت إضافة الدفعة');
  } catch (e) {
    toast.error(e.response?.data?.error || 'تعذر حفظ الدفعة');
  } finally {
    saving.value = false;
  }
}

async function deletePayment(payment) {
  if (!payment?.id || !confirm('حذف هذه الدفعة؟')) return;
  try {
    const { data } = await axios.post('/api/deleteExternalCarPayment', { id: payment.id });
    payments.value = payments.value.filter((p) => p.id !== payment.id);
    paidDollar.value = Number(data.car?.paid_dollar) || 0;
    paidDinar.value = Number(data.car?.paid_dinar) || 0;
    emit('updated', data.car);
    toast.success('تم حذف الدفعة');
  } catch (e) {
    toast.error(e.response?.data?.error || 'تعذر حذف الدفعة');
  }
}
</script>

<template>
  <Transition name="modal">
    <div v-if="show" class="modal-mask">
      <div class="modal-wrapper max-h-[90vh]">
        <div class="modal-container overflow-auto max-h-[90vh] bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100">
          <div class="px-4 pt-4">
            <h2 class="text-center text-lg font-bold text-gray-900 dark:text-white">دفعات السيارة الخارجية</h2>
            <p class="text-center text-sm text-gray-600 dark:text-gray-300 mt-1">{{ carTitle }}</p>
            <div class="flex justify-center gap-4 mt-2 text-sm font-semibold">
              <span class="text-emerald-700 dark:text-emerald-400">{{ formatNumber(paidDollar) }} $</span>
              <span class="text-indigo-700 dark:text-indigo-400">{{ formatNumber(paidDinar) }} د</span>
            </div>
            <p v-if="car?.expenses_posted" class="text-center text-xs font-bold text-emerald-700 dark:text-emerald-400 mt-2">
              تم الترحيل — يمكنك إضافة دفعة جديدة ثم ترحيلها
            </p>
          </div>

          <div class="px-4 py-4 grid grid-cols-1 md:grid-cols-2 gap-3">
            <label class="block">
              <span class="text-sm font-semibold text-gray-800 dark:text-gray-200">مبلغ دولار</span>
              <input v-model="form.amount_dollar" type="number" min="0" class="mt-1 block w-full rounded-lg border border-gray-300 bg-white text-gray-900 dark:border-gray-600 dark:bg-gray-800 dark:text-white px-3 py-2 text-sm" />
            </label>
            <label class="block">
              <span class="text-sm font-semibold text-gray-800 dark:text-gray-200">مبلغ دينار</span>
              <input v-model="form.amount_dinar" type="number" min="0" class="mt-1 block w-full rounded-lg border border-gray-300 bg-white text-gray-900 dark:border-gray-600 dark:bg-gray-800 dark:text-white px-3 py-2 text-sm" />
            </label>
            <label class="block">
              <span class="text-sm font-semibold text-gray-800 dark:text-gray-200">التاريخ</span>
              <input v-model="form.created" type="date" class="mt-1 block w-full rounded-lg border border-gray-300 bg-white text-gray-900 dark:border-gray-600 dark:bg-gray-800 dark:text-white px-3 py-2 text-sm" />
            </label>
            <label class="block">
              <span class="text-sm font-semibold text-gray-800 dark:text-gray-200">ملاحظة</span>
              <input v-model="form.note" type="text" class="mt-1 block w-full rounded-lg border border-gray-300 bg-white text-gray-900 dark:border-gray-600 dark:bg-gray-800 dark:text-white px-3 py-2 text-sm" />
            </label>
          </div>

          <div class="flex justify-center gap-3 px-4 pb-3">
            <button
              type="button"
              class="px-5 py-2 rounded-lg bg-emerald-600 text-white font-semibold hover:bg-emerald-700 disabled:opacity-50"
              :disabled="saving"
              @click="addPayment"
            >
              {{ saving ? 'جاري الحفظ...' : 'إضافة دفعة' }}
            </button>
            <a
              v-if="car?.id"
              :href="`/api/printExternalCar?external_car_id=${car.id}`"
              target="_blank"
              class="inline-flex items-center gap-1 px-5 py-2 rounded-lg bg-blue-600 text-white font-semibold hover:bg-blue-700"
            >
              طباعة التفاصيل
              <print />
            </a>
            <button
              type="button"
              class="px-5 py-2 rounded-lg bg-gray-500 text-white font-semibold hover:bg-gray-600"
              @click="emit('close')"
            >
              إغلاق
            </button>
          </div>

          <div class="px-4 pb-4">
            <div v-if="loading" class="text-center text-gray-500 dark:text-gray-400 py-4">جاري التحميل...</div>
            <div v-else class="overflow-x-auto border border-gray-200 dark:border-gray-700 rounded-lg">
              <table class="w-full text-sm text-center">
                <thead class="bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-100">
                  <tr>
                    <th class="px-2 py-2">التاريخ</th>
                    <th class="px-2 py-2">ملاحظة</th>
                    <th class="px-2 py-2">دولار</th>
                    <th class="px-2 py-2">دينار</th>
                    <th class="px-2 py-2">حذف</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-if="!payments.length">
                    <td colspan="5" class="px-2 py-4 text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-900">لا توجد دفعات</td>
                  </tr>
                  <tr
                    v-for="p in payments"
                    :key="p.id"
                    class="border-t border-gray-200 bg-white text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                  >
                    <td class="px-2 py-2">{{ formatDate(p.created) }}</td>
                    <td class="px-2 py-2">{{ p.note || '—' }}</td>
                    <td class="px-2 py-2 font-semibold text-emerald-700 dark:text-emerald-400">{{ formatNumber(p.amount_dollar) }}</td>
                    <td class="px-2 py-2 font-semibold text-indigo-700 dark:text-indigo-400">{{ formatNumber(p.amount_dinar) }}</td>
                    <td class="px-2 py-2">
                      <span
                        v-if="p.is_posted"
                        class="text-[11px] font-bold text-emerald-700 dark:text-emerald-400"
                      >مُرحَّل</span>
                      <button
                        v-else
                        type="button"
                        class="px-2 py-1 bg-red-500 text-white rounded hover:bg-red-600"
                        @click="deletePayment(p)"
                      >
                        <trash />
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
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
  width: min(720px, 95vw);
  margin: 0 auto;
  border-radius: 12px;
  box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
}
.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}
</style>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/inertia-vue3';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import axios from 'axios';
import { ref } from 'vue';
import { useToast } from 'vue-toastification';

const toast = useToast();
const contracts = ref([]);
const payments = ref([]);
const loading = ref(false);
const loadingPayments = ref(false);
const q = ref('');
const paymentsQ = ref('');
const selectedContract = ref(null);
const installments = ref([]);
const showModal = ref(false);
const saving = ref(false);
const paymentForm = ref({
  amount: '',
  received_by: '',
  note: '',
  created: new Date().toISOString().slice(0, 10),
});

async function loadContracts() {
  loading.value = true;
  try {
    const response = await axios.get('/api/contract-installments', {
      params: { q: q.value || undefined, limit: 100 },
    });
    contracts.value = response.data.data ?? [];
  } catch (error) {
    console.error(error);
    toast.error('تعذر تحميل العقود', { timeout: 2500, position: 'bottom-right', rtl: true });
  } finally {
    loading.value = false;
  }
}

async function loadPayments() {
  loadingPayments.value = true;
  try {
    const response = await axios.get('/api/contract-installment-payments', {
      params: { q: paymentsQ.value || undefined, limit: 100 },
    });
    payments.value = response.data.data ?? [];
  } catch (error) {
    console.error(error);
    toast.error('تعذر تحميل الدفعات', { timeout: 2500, position: 'bottom-right', rtl: true });
  } finally {
    loadingPayments.value = false;
  }
}

async function openPayments(contract) {
  selectedContract.value = contract;
  showModal.value = true;
  paymentForm.value = {
    amount: '',
    received_by: '',
    note: '',
    created: new Date().toISOString().slice(0, 10),
  };
  try {
    const response = await axios.get(`/api/contract-installments/${contract.id}`);
    selectedContract.value = response.data;
    installments.value = response.data.installments ?? [];
  } catch (error) {
    console.error(error);
  }
}

function printReceipt(installmentId) {
  window.open(`/contract_installment_print/${installmentId}`, '_blank');
}

async function savePayment() {
  if (!selectedContract.value?.id) return;
  const amount = Number(paymentForm.value.amount);
  if (!amount || amount <= 0) {
    toast.info('أدخل مبلغ الدفعة', { timeout: 2200, position: 'bottom-right', rtl: true });
    return;
  }

  saving.value = true;
  try {
    const response = await axios.post('/api/contract-installments', {
      car_contract_id: selectedContract.value.id,
      amount,
      received_by: paymentForm.value.received_by,
      note: paymentForm.value.note,
      created: paymentForm.value.created,
    });
    toast.success('تم تسجيل الدفعة', { timeout: 2200, position: 'bottom-right', rtl: true });
    const installmentId = response.data?.installment?.id;
    showModal.value = false;
    await Promise.all([loadContracts(), loadPayments()]);
    if (installmentId) {
      printReceipt(installmentId);
    }
  } catch (error) {
    const msg = error?.response?.data?.error || 'تعذر تسجيل الدفعة';
    toast.error(msg, { timeout: 2800, position: 'bottom-right', rtl: true });
  } finally {
    saving.value = false;
  }
}

function remaining(contract) {
  return Math.max(0, Number(contract.car_price || 0) - Number(contract.car_paid || 0));
}

loadContracts();
loadPayments();
</script>

<template>
  <Head title="أقساط السيارات" />

  <AuthenticatedLayout>
    <div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8">
      <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">أقساط السيارات</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">تتبع العقود غير المكتملة الدفع وسجل الدفعات المسجّلة.</p>
      </div>

      <div class="bg-white dark:bg-gray-900 rounded-xl border border-slate-200 dark:border-slate-700 p-4 mb-6">
        <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-3">عقود بمتبقي دفع</h2>
        <div class="flex flex-wrap gap-3 items-end mb-4">
          <div class="flex-1 min-w-[220px]">
            <InputLabel value="بحث" />
            <TextInput v-model="q" class="mt-1 block w-full" placeholder="مشتري، شانصي، نوع السيارة..." @keyup.enter="loadContracts" />
          </div>
          <button type="button" class="px-4 py-2 rounded-lg bg-sky-600 text-white font-semibold hover:bg-sky-700" @click="loadContracts">
            {{ loading ? 'جاري التحميل...' : 'بحث' }}
          </button>
        </div>

        <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-700">
          <table class="min-w-full text-sm text-center">
            <thead class="bg-slate-800 text-white">
              <tr>
                <th class="px-3 py-3">#</th>
                <th class="px-3 py-3">المشتري</th>
                <th class="px-3 py-3">السيارة</th>
                <th class="px-3 py-3">الشانصي</th>
                <th class="px-3 py-3">السعر</th>
                <th class="px-3 py-3">المدفوع</th>
                <th class="px-3 py-3">المتبقي</th>
                <th class="px-3 py-3">الإجراء</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="contract in contracts" :key="contract.id" class="border-t border-slate-200 dark:border-slate-700">
                <td class="px-3 py-3">{{ contract.id }}</td>
                <td class="px-3 py-3">{{ contract.name_buyer }}</td>
                <td class="px-3 py-3">{{ contract.car_name }}</td>
                <td class="px-3 py-3 font-mono">{{ contract.vin }}</td>
                <td class="px-3 py-3">{{ contract.car_price }}</td>
                <td class="px-3 py-3">{{ contract.car_paid }}</td>
                <td class="px-3 py-3 font-bold text-rose-600">{{ remaining(contract) }}</td>
                <td class="px-3 py-3">
                  <button type="button" class="rounded-lg bg-emerald-600 px-3 py-2 text-xs font-bold text-white hover:bg-emerald-700" @click="openPayments(contract)">
                    تسجيل دفعة
                  </button>
                </td>
              </tr>
              <tr v-if="!loading && !contracts.length">
                <td colspan="8" class="px-4 py-10 text-slate-500">لا توجد عقود بمتبقي حالياً.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div class="bg-white dark:bg-gray-900 rounded-xl border border-slate-200 dark:border-slate-700 p-4">
        <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-3">سجل الدفعات المسجّلة</h2>
        <div class="flex flex-wrap gap-3 items-end mb-4">
          <div class="flex-1 min-w-[220px]">
            <InputLabel value="بحث في الدفعات" />
            <TextInput v-model="paymentsQ" class="mt-1 block w-full" placeholder="مشتري، شانصي، مستلم..." @keyup.enter="loadPayments" />
          </div>
          <button type="button" class="px-4 py-2 rounded-lg bg-indigo-600 text-white font-semibold hover:bg-indigo-700" @click="loadPayments">
            {{ loadingPayments ? 'جاري التحميل...' : 'بحث' }}
          </button>
        </div>

        <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-700">
          <table class="min-w-full text-sm text-center">
            <thead class="bg-slate-700 text-white">
              <tr>
                <th class="px-3 py-3">#</th>
                <th class="px-3 py-3">العقد</th>
                <th class="px-3 py-3">المشتري</th>
                <th class="px-3 py-3">السيارة</th>
                <th class="px-3 py-3">الشانصي</th>
                <th class="px-3 py-3">المبلغ</th>
                <th class="px-3 py-3">المستلم</th>
                <th class="px-3 py-3">التاريخ</th>
                <th class="px-3 py-3">وصل</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="payment in payments" :key="payment.id" class="border-t border-slate-200 dark:border-slate-700">
                <td class="px-3 py-3">{{ payment.id }}</td>
                <td class="px-3 py-3">#{{ payment.car_contract_id }}</td>
                <td class="px-3 py-3">{{ payment.car_contract?.name_buyer }}</td>
                <td class="px-3 py-3">{{ payment.car_contract?.car_name }}</td>
                <td class="px-3 py-3 font-mono">{{ payment.car_contract?.vin }}</td>
                <td class="px-3 py-3 font-bold text-emerald-600">{{ payment.amount }}</td>
                <td class="px-3 py-3">{{ payment.received_by }}</td>
                <td class="px-3 py-3">{{ payment.created }}</td>
                <td class="px-3 py-3">
                  <button type="button" class="text-sky-600 hover:underline font-semibold" @click="printReceipt(payment.id)">طباعة</button>
                </td>
              </tr>
              <tr v-if="!loadingPayments && !payments.length">
                <td colspan="9" class="px-4 py-10 text-slate-500">لا توجد دفعات مسجّلة بعد.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4">
      <div class="w-full max-w-2xl rounded-2xl bg-white dark:bg-slate-900 border border-slate-700 p-5">
        <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-1">تسجيل دفعة — عقد #{{ selectedContract?.id }}</h2>
        <p class="text-sm text-slate-500 mb-4">
          المتبقي: <span class="font-bold text-rose-600">{{ remaining(selectedContract || {}) }}</span>
        </p>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-4">
          <div>
            <InputLabel value="مبلغ الدفعة" />
            <TextInput v-model="paymentForm.amount" type="number" class="mt-1 block w-full" />
          </div>
          <div>
            <InputLabel value="المستلم" />
            <TextInput v-model="paymentForm.received_by" class="mt-1 block w-full" />
          </div>
          <div>
            <InputLabel value="التاريخ" />
            <TextInput v-model="paymentForm.created" type="date" class="mt-1 block w-full" />
          </div>
          <div>
            <InputLabel value="ملاحظة" />
            <TextInput v-model="paymentForm.note" class="mt-1 block w-full" />
          </div>
        </div>

        <div v-if="installments.length" class="mb-4 max-h-40 overflow-y-auto rounded-lg border border-slate-200 dark:border-slate-700">
          <table class="min-w-full text-xs">
            <thead class="bg-slate-100 dark:bg-slate-800">
              <tr>
                <th class="px-2 py-2">التاريخ</th>
                <th class="px-2 py-2">المبلغ</th>
                <th class="px-2 py-2">المستلم</th>
                <th class="px-2 py-2">وصل</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in installments" :key="item.id" class="border-t border-slate-200 dark:border-slate-700">
                <td class="px-2 py-2">{{ item.created }}</td>
                <td class="px-2 py-2">{{ item.amount }}</td>
                <td class="px-2 py-2">{{ item.received_by }}</td>
                <td class="px-2 py-2">
                  <button type="button" class="text-sky-600 hover:underline" @click="printReceipt(item.id)">طباعة</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="flex justify-end gap-2">
          <button type="button" class="px-4 py-2 rounded-lg bg-slate-200 dark:bg-slate-700" @click="showModal = false">إلغاء</button>
          <button type="button" class="px-4 py-2 rounded-lg bg-emerald-600 text-white font-semibold" :disabled="saving" @click="savePayment">
            {{ saving ? 'جاري الحفظ...' : 'حفظ وطباعة الوصل' }}
          </button>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

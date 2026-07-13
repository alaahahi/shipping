<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/inertia-vue3';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import axios from 'axios';
import { ref } from 'vue';
import { useToast } from 'vue-toastification';
import { useI18n } from 'vue-i18n';

const toast = useToast();
const { t } = useI18n();
const contracts = ref([]);
const loading = ref(false);
const q = ref('');
const contractTypeFilter = ref('');
const selectedContract = ref(null);
const installments = ref([]);
const showPaymentModal = ref(false);
const showHistoryModal = ref(false);
const loadingHistory = ref(false);
const saving = ref(false);
const deletingId = ref(null);
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
      params: {
        q: q.value || undefined,
        contract_type: contractTypeFilter.value || undefined,
        limit: 100,
      },
    });
    contracts.value = response.data.data ?? [];
  } catch (error) {
    console.error(error);
    toast.error(t('installments.toastLoadContractsFailed'), { timeout: 2500, position: 'bottom-right', rtl: true });
  } finally {
    loading.value = false;
  }
}

async function fetchContractDetails(contractId) {
  const response = await axios.get(`/api/contract-installments/${contractId}`);
  selectedContract.value = response.data;
  installments.value = response.data.installments ?? [];
  return response.data;
}

async function openHistory(contract) {
  showHistoryModal.value = true;
  loadingHistory.value = true;
  selectedContract.value = contract;
  installments.value = [];
  try {
    await fetchContractDetails(contract.id);
  } catch (error) {
    console.error(error);
    toast.error(t('installments.toastLoadPaymentsFailed'), { timeout: 2500, position: 'bottom-right', rtl: true });
  } finally {
    loadingHistory.value = false;
  }
}

async function openPaymentForm(contract) {
  showPaymentModal.value = true;
  paymentForm.value = {
    amount: '',
    received_by: '',
    note: '',
    created: new Date().toISOString().slice(0, 10),
  };
  try {
    await fetchContractDetails(contract.id);
  } catch (error) {
    console.error(error);
  }
}

function closeModals() {
  showPaymentModal.value = false;
  showHistoryModal.value = false;
}

function printReceipt(installmentId) {
  window.open(`/contract_installment_print/${installmentId}`, '_blank');
}

async function savePayment() {
  if (!selectedContract.value?.id) return;
  const amount = Number(paymentForm.value.amount);
  if (!amount || amount <= 0) {
    toast.info(t('installments.toastEnterAmount'), { timeout: 2200, position: 'bottom-right', rtl: true });
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
    toast.success(t('installments.toastPaymentSaved'), { timeout: 2200, position: 'bottom-right', rtl: true });
    const installmentId = response.data?.installment?.id;
    showPaymentModal.value = false;
    await loadContracts();
    if (installmentId) {
      printReceipt(installmentId);
    }
  } catch (error) {
    const msg = error?.response?.data?.error || t('installments.toastPaymentFailed');
    toast.error(msg, { timeout: 2800, position: 'bottom-right', rtl: true });
  } finally {
    saving.value = false;
  }
}

async function deletePayment(item) {
  if (!item?.id) return;

  const confirmed = window.confirm(
    t('installments.toastDeleteConfirm', { amount: formatAmount(item.amount) })
  );
  if (!confirmed) return;

  deletingId.value = item.id;
  try {
    const response = await axios.delete(`/api/contract-installments/${item.id}`);
    toast.success(t('installments.toastDeleteSuccess'), { timeout: 2200, position: 'bottom-right', rtl: true });
    if (response.data?.contract) {
      selectedContract.value = response.data.contract;
      installments.value = response.data.contract.installments ?? [];
    } else if (selectedContract.value?.id) {
      await fetchContractDetails(selectedContract.value.id);
    }
    await loadContracts();
  } catch (error) {
    const msg = error?.response?.data?.error || t('installments.toastDeleteFailed');
    toast.error(msg, { timeout: 2800, position: 'bottom-right', rtl: true });
  } finally {
    deletingId.value = null;
  }
}

function remaining(contract) {
  return Math.max(0, Number(contract?.car_price || 0) - Number(contract?.car_paid || 0));
}

function formatAmount(value) {
  const num = Number(value || 0);
  return `$${num.toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 2 })}`;
}

function formatModalAmount(value) {
  return Number(value || 0).toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
}

function contractTypeLabel(type) {
  return type === 'external' ? t('installments.external') : t('installments.company');
}

function contractTypeClass(type) {
  return type === 'external'
    ? 'bg-violet-100 text-violet-700 dark:bg-violet-900/40 dark:text-violet-200'
    : 'bg-sky-100 text-sky-700 dark:bg-sky-900/40 dark:text-sky-200';
}

loadContracts();
</script>

<template>
  <Head :title="t('installments.title')" />

  <AuthenticatedLayout>
    <div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8">
      <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">{{ t('installments.title') }}</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">{{ t('installments.subtitle') }}</p>
      </div>

      <div class="bg-white dark:bg-gray-900 rounded-xl border border-slate-200 dark:border-slate-700 p-4">
        <div class="flex flex-wrap gap-3 items-end mb-4">
          <div class="flex-1 min-w-[220px]">
            <InputLabel :value="t('search')" />
            <TextInput v-model="q" class="mt-1 block w-full" :placeholder="t('installments.searchPlaceholder')" @keyup.enter="loadContracts" />
          </div>
          <div class="min-w-[160px]">
            <InputLabel :value="t('installments.contractType')" />
            <select
              v-model="contractTypeFilter"
              class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
              @change="loadContracts"
            >
              <option value="">{{ t('installments.filterAll') }}</option>
              <option value="company">{{ t('installments.filterCompany') }}</option>
              <option value="external">{{ t('installments.filterExternal') }}</option>
            </select>
          </div>
          <button type="button" class="px-4 py-2 rounded-lg bg-sky-600 text-white font-semibold hover:bg-sky-700" @click="loadContracts">
            {{ loading ? t('installments.loading') : t('search') }}
          </button>
        </div>

        <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-700">
          <table class="min-w-full text-sm text-center">
            <thead class="bg-slate-800 text-white">
              <tr>
                <th class="px-3 py-3">#</th>
                <th class="px-3 py-3">{{ t('installments.type') }}</th>
                <th class="px-3 py-3">{{ t('Buyer') }}</th>
                <th class="px-3 py-3">{{ t('car_type') }}</th>
                <th class="px-3 py-3">{{ t('vin') }}</th>
                <th class="px-3 py-3">{{ t('installments.priceUsd') }}</th>
                <th class="px-3 py-3">{{ t('installments.paidUsd') }}</th>
                <th class="px-3 py-3">{{ t('installments.remainingUsd') }}</th>
                <th class="px-3 py-3">{{ t('installments.payments') }}</th>
                <th class="px-3 py-3">{{ t('installments.actions') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="contract in contracts" :key="contract.id" class="border-t border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800/40">
                <td class="px-3 py-3 font-semibold">{{ contract.id }}</td>
                <td class="px-3 py-3">
                  <span class="inline-block rounded-full px-2.5 py-0.5 text-xs font-bold" :class="contractTypeClass(contract.contract_type)">
                    {{ contractTypeLabel(contract.contract_type) }}
                  </span>
                </td>
                <td class="px-3 py-3">{{ contract.name_buyer }}</td>
                <td class="px-3 py-3">{{ contract.car_name }}</td>
                <td class="px-3 py-3 font-mono text-xs">{{ contract.vin }}</td>
                <td class="px-3 py-3 font-mono">{{ formatAmount(contract.car_price) }}</td>
                <td class="px-3 py-3 text-emerald-600 font-semibold font-mono">{{ formatAmount(contract.car_paid) }}</td>
                <td class="px-3 py-3 font-bold text-rose-600 font-mono">{{ formatAmount(remaining(contract)) }}</td>
                <td class="px-3 py-3">
                  <button
                    type="button"
                    class="inline-flex items-center gap-1 rounded-full bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-200 px-3 py-1 text-xs font-bold hover:bg-indigo-200"
                    @click="openHistory(contract)"
                  >
                    {{ t('installments.paymentCount', { count: contract.installments_count || 0 }) }}
                  </button>
                </td>
                <td class="px-3 py-3">
                  <div class="flex flex-wrap gap-2 justify-center">
                    <button type="button" class="rounded-lg bg-slate-600 px-3 py-2 text-xs font-bold text-white hover:bg-slate-700" @click="openHistory(contract)">
                      {{ t('installments.viewPayments') }}
                    </button>
                    <button type="button" class="rounded-lg bg-emerald-600 px-3 py-2 text-xs font-bold text-white hover:bg-emerald-700" @click="openPaymentForm(contract)">
                      {{ t('installments.newPayment') }}
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="!loading && !contracts.length">
                <td colspan="10" class="px-4 py-12 text-slate-500">{{ t('installments.empty') }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- نافذة سجل الدفعات -->
    <div v-if="showHistoryModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4" @click.self="closeModals">
      <div class="w-full max-w-3xl max-h-[90vh] overflow-hidden rounded-2xl bg-white dark:bg-slate-900 border border-slate-700 shadow-2xl flex flex-col">
        <div class="bg-gradient-to-l from-indigo-600 to-sky-600 px-5 py-4 text-white shrink-0">
          <div class="flex items-start justify-between gap-3">
            <div>
              <h2 class="text-lg font-bold">{{ t('installments.historyTitle') }}</h2>
              <p class="text-sm text-indigo-100 mt-1">
                {{ t('installments.contractNo') }} #{{ selectedContract?.id }}
                <span class="mx-1">·</span>
                {{ contractTypeLabel(selectedContract?.contract_type) }}
                <span class="mx-1">·</span>
                {{ selectedContract?.name_buyer }} — {{ selectedContract?.car_name }}
              </p>
            </div>
            <button type="button" class="text-white/90 hover:text-white text-xl leading-none" @click="closeModals">×</button>
          </div>
        </div>

        <div class="p-5 overflow-y-auto">
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-4">
            <div class="rounded-xl bg-slate-100 dark:bg-slate-800 p-3 text-center">
              <div class="text-xs text-slate-500">{{ t('carPrice') }}</div>
              <div class="font-bold text-lg font-mono">{{ formatAmount(selectedContract?.car_price) }}</div>
            </div>
            <div class="rounded-xl bg-emerald-50 dark:bg-emerald-900/20 p-3 text-center">
              <div class="text-xs text-emerald-700">{{ t('paid') }}</div>
              <div class="font-bold text-lg text-emerald-700 font-mono">{{ formatAmount(selectedContract?.car_paid) }}</div>
            </div>
            <div class="rounded-xl bg-rose-50 dark:bg-rose-900/20 p-3 text-center">
              <div class="text-xs text-rose-700">{{ t('remaining') }}</div>
              <div class="font-bold text-lg text-rose-700 font-mono">{{ formatAmount(remaining(selectedContract)) }}</div>
            </div>
          </div>

          <div v-if="loadingHistory" class="text-center py-10 text-slate-500">{{ t('installments.loadingPayments') }}</div>

          <div v-else-if="installments.length" class="space-y-3">
            <div
              v-for="(item, index) in installments"
              :key="item.id"
              class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-slate-200 dark:border-slate-700 p-4 hover:border-sky-300 dark:hover:border-sky-700 transition"
            >
              <div class="flex items-center gap-3 min-w-0">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-sky-100 text-sky-700 font-bold text-sm">
                  {{ installments.length - index }}
                </div>
                <div class="text-right min-w-0">
                  <div class="font-bold text-emerald-600 text-lg font-mono">{{ formatAmount(item.amount) }}</div>
                  <div class="text-xs text-slate-500 mt-0.5">
                    {{ item.created }} · {{ t('installments.receivedBy') }}: {{ item.received_by || '—' }}
                    <span v-if="item.note"> · {{ item.note }}</span>
                  </div>
                </div>
              </div>
              <div class="flex flex-wrap gap-2 shrink-0">
                <button type="button" class="rounded-lg bg-sky-600 px-4 py-2 text-sm font-bold text-white hover:bg-sky-700" @click="printReceipt(item.id)">
                  {{ t('installments.printReceipt') }}
                </button>
                <button
                  type="button"
                  class="rounded-lg bg-rose-600 px-4 py-2 text-sm font-bold text-white hover:bg-rose-700 disabled:opacity-50"
                  :disabled="deletingId === item.id"
                  @click="deletePayment(item)"
                >
                  {{ deletingId === item.id ? t('installments.deleting') : t('delete') }}
                </button>
              </div>
            </div>
          </div>

          <div v-else class="text-center py-10 text-slate-500 rounded-xl border border-dashed border-slate-300">
            {{ t('installments.noPayments') }}
          </div>
        </div>

        <div class="border-t border-slate-200 dark:border-slate-700 p-4 flex flex-wrap justify-end gap-2 shrink-0">
          <button type="button" class="px-4 py-2 rounded-lg bg-slate-200 dark:bg-slate-700" @click="closeModals">{{ t('installments.close') }}</button>
          <button
            v-if="remaining(selectedContract) > 0"
            type="button"
            class="px-4 py-2 rounded-lg bg-emerald-600 text-white font-semibold"
            @click="showHistoryModal = false; openPaymentForm(selectedContract)"
          >
            {{ t('installments.registerNewPayment') }}
          </button>
        </div>
      </div>
    </div>

    <!-- نافذة تسجيل دفعة -->
    <div v-if="showPaymentModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4" @click.self="closeModals">
      <div class="w-full max-w-lg rounded-2xl bg-white dark:bg-slate-900 border border-slate-700 shadow-2xl overflow-hidden">
        <div class="bg-gradient-to-l from-emerald-600 to-teal-600 px-5 py-4 text-white">
          <h2 class="text-lg font-bold">{{ t('installments.newPaymentTitle') }}</h2>
          <p class="text-sm text-emerald-100 mt-1">
            {{ t('installments.contractNo') }} #{{ selectedContract?.id }}
            ({{ contractTypeLabel(selectedContract?.contract_type) }})
            — {{ t('installments.remainingLabel') }}: ${{ formatModalAmount(remaining(selectedContract)) }}
          </p>
        </div>

        <div class="p-5">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div class="sm:col-span-2">
              <InputLabel :value="t('installments.paymentAmount')" />
              <div class="relative mt-1">
                <span class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-500 font-bold">$</span>
                <TextInput v-model="paymentForm.amount" type="number" step="0.01" min="0" class="block w-full text-lg font-semibold pr-8" placeholder="0" />
              </div>
            </div>
            <div>
              <InputLabel :value="t('installments.receiver')" />
              <TextInput v-model="paymentForm.received_by" class="mt-1 block w-full" />
            </div>
            <div>
              <InputLabel :value="t('date')" />
              <TextInput v-model="paymentForm.created" type="date" class="mt-1 block w-full" />
            </div>
            <div class="sm:col-span-2">
              <InputLabel :value="t('installments.noteOptional')" />
              <TextInput v-model="paymentForm.note" class="mt-1 block w-full" />
            </div>
          </div>
        </div>

        <div class="border-t border-slate-200 dark:border-slate-700 p-4 flex justify-end gap-2">
          <button type="button" class="px-4 py-2 rounded-lg bg-slate-200 dark:bg-slate-700" @click="closeModals">{{ t('cancel') }}</button>
          <button type="button" class="px-4 py-2 rounded-lg bg-emerald-600 text-white font-semibold" :disabled="saving" @click="savePayment">
            {{ saving ? t('saving') : t('installments.saveAndPrint') }}
          </button>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

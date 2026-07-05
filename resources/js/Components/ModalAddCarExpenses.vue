<script setup>
import { computed, ref, watch } from 'vue';
import print from "@/Components/icon/print.vue";
import trash from "@/Components/icon/trash.vue";
import { useToast } from "vue-toastification";
import axios from 'axios';

const toast = useToast();
const emit = defineEmits(['close', 'a']);

const activeTab = ref('add');

const setActiveTab = (tab) => {
  activeTab.value = tab;
};

const props = defineProps({
  show: Boolean,
  company: Array,
  color: Array,
  carModel: Array,
  name: Array,
  client: Array,
  user: Array,
  currentWork: Boolean,
  GenExpenses: Array,
  formData: Object,
});

const REGISTRATION_DINAR_PRESETS = [
  { key: 'fee1860', label: '1,860,000', amount: 1860000 },
  { key: 'fee2965', label: '2,965,000', amount: 2965000 },
  { key: 'fee2705', label: '2,705,000', amount: 2705000 },
  { key: 'fee3755', label: '3,755,000', amount: 3755000 },
];

const paymentForm = ref({
  registrationDollar: '',
  registrationDinar: '',
  repairCurrency: 'dollar',
  repairAmount: '',
  note: '',
});

function resetPaymentForm() {
  paymentForm.value = {
    registrationDollar: '',
    registrationDinar: '',
    repairCurrency: 'dollar',
    repairAmount: '',
    note: '',
  };
}

watch(() => props.show, (visible) => {
  if (visible) {
    resetPaymentForm();
    activeTab.value = 'add';
  }
});

function parseAmount(value) {
  const n = Number(value);
  return Number.isFinite(n) && n > 0 ? n : 0;
}

function formatDinar(n) {
  return new Intl.NumberFormat('ar-IQ').format(n);
}

function addDinarQuick(amount) {
  paymentForm.value.registrationDinar = parseAmount(paymentForm.value.registrationDinar) + amount;
}

const registrationDinarTotal = computed(() => parseAmount(paymentForm.value.registrationDinar));

const repairDollar = computed(() => {
  return paymentForm.value.repairCurrency === 'dollar'
    ? parseAmount(paymentForm.value.repairAmount)
    : 0;
});

const repairDinar = computed(() => {
  return paymentForm.value.repairCurrency === 'dinar'
    ? parseAmount(paymentForm.value.repairAmount)
    : 0;
});

const totalDollar = computed(() => parseAmount(paymentForm.value.registrationDollar) + repairDollar.value);
const totalDinar = computed(() => registrationDinarTotal.value + repairDinar.value);

function buildNote() {
  const parts = [];

  const regDollar = parseAmount(paymentForm.value.registrationDollar);
  if (regDollar > 0) parts.push(`تسجيل ${regDollar}$`);

  const regDinar = registrationDinarTotal.value;
  if (regDinar > 0) parts.push(`تسجيل ${formatDinar(regDinar)} د`);

  if (repairDollar.value > 0) parts.push(`تصليح ${repairDollar.value}$`);
  if (repairDinar.value > 0) parts.push(`تصليح ${formatDinar(repairDinar.value)} د`);

  const userNote = paymentForm.value.note?.trim() || '';
  if (userNote && parts.length) return `${userNote} — ${parts.join(' | ')}`;
  if (parts.length) return parts.join(' | ');
  return userNote;
}

const canSubmit = computed(() => totalDollar.value > 0 || totalDinar.value > 0);

function submitPayment() {
  if (!canSubmit.value) return;

  emit('a', {
    ...props.formData,
    amountDollar: totalDollar.value,
    amountDinar: totalDinar.value,
    amountNote: buildNote(),
  });

  resetPaymentForm();
}

function closeModal() {
  resetPaymentForm();
  activeTab.value = 'add';
  emit('close');
}

function openModalDelClient(expense) {
  if (window.confirm('هل أنت متأكد من الحذف؟')) {
    axios.post('/api/delExpensesCar', expense)
      .then(() => {
        toast.success('تم حذف الدفعة بنجاح', {
          timeout: 3000,
          position: 'bottom-right',
          rtl: true,
        });
        setTimeout(() => location.reload(), 2000);
      })
      .catch((error) => console.error(error));
  }
}
</script>

<template>
  <Transition name="modal">
    <div v-if="show" class="modal-mask">
      <div class="modal-wrapper max-h-[90vh]">
        <div class="modal-container dark:bg-gray-900 dark:border dark:border-gray-600 overflow-auto max-h-[90vh]">
          <div class="modal-header">
            <slot name="header" />
          </div>

          <div class="modal-body">
            <div class="text-sm font-medium text-center text-gray-600 border-b border-gray-200 dark:text-gray-200 dark:border-gray-600">
              <ul class="flex flex-wrap -mb-px">
                <li v-if="currentWork" class="mr-2" @click="setActiveTab('add')">
                  <button
                    type="button"
                    class="inline-block p-4 border-b-2 border-transparent rounded-t-lg transition"
                    :class="activeTab === 'add'
                      ? 'text-blue-600 border-blue-600 dark:text-blue-400 dark:border-blue-400 font-semibold'
                      : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'"
                  >
                    اضافة
                  </button>
                </li>
                <li class="mr-2" @click="setActiveTab('record')">
                  <button
                    type="button"
                    class="inline-block p-4 border-b-2 border-transparent rounded-t-lg transition"
                    :class="activeTab === 'record'
                      ? 'text-blue-600 border-blue-600 dark:text-blue-400 dark:border-blue-400 font-semibold'
                      : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'"
                  >
                    السجل
                  </button>
                </li>
              </ul>
            </div>

            <!-- إضافة -->
            <div v-if="activeTab === 'add' && currentWork" class="mx-4 mt-4 space-y-4">
              <h1 class="text-center text-lg font-bold dark:text-gray-100">تسجيل مصاريف السيارة</h1>

              <div class="pay-card pay-card--green">
                <div class="pay-card__title">
                  <span class="text-xl">📋</span>
                  <span>رسوم التسجيل</span>
                </div>

                <div class="mb-4">
                  <label class="pay-label" for="reg_dollar">دولار $</label>
                  <input
                    id="reg_dollar"
                    type="number"
                    min="0"
                    v-model="paymentForm.registrationDollar"
                    placeholder="0"
                    class="pay-input"
                  />
                </div>

                <div>
                  <label class="pay-label" for="reg_dinar">دينار د.ع</label>
                  <input
                    id="reg_dinar"
                    type="number"
                    min="0"
                    v-model="paymentForm.registrationDinar"
                    placeholder="0"
                    class="pay-input pay-input--highlight mb-2"
                  />
                  <p class="pay-hint mb-3">يمكنك الكتابة مباشرة أو الضغط على الأزرار للإضافة السريعة</p>

                  <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                    <button
                      v-for="preset in REGISTRATION_DINAR_PRESETS"
                      :key="preset.key"
                      type="button"
                      @click="addDinarQuick(preset.amount)"
                      class="pay-quick-btn pay-quick-btn--green"
                    >
                      + {{ preset.label }}
                    </button>
                  </div>
                </div>
              </div>

              <!-- مصروف تصليح -->
              <div class="pay-card pay-card--amber">
                <div class="pay-card__title pay-card__title--amber">
                  <span class="text-xl">🔧</span>
                  <span>مصروف تصليح</span>
                  <span class="text-xs font-normal opacity-80">(منفصل عن التسجيل)</span>
                </div>

                <div class="mb-3 flex gap-2">
                  <button
                    type="button"
                    @click="paymentForm.repairCurrency = 'dollar'"
                    :class="[
                      'pay-currency-btn flex-1',
                      paymentForm.repairCurrency === 'dollar' ? 'pay-currency-btn--active-amber' : '',
                    ]"
                  >
                    💵 دولار
                  </button>
                  <button
                    type="button"
                    @click="paymentForm.repairCurrency = 'dinar'"
                    :class="[
                      'pay-currency-btn flex-1',
                      paymentForm.repairCurrency === 'dinar' ? 'pay-currency-btn--active-amber' : '',
                    ]"
                  >
                    🇮🇶 دينار
                  </button>
                </div>

                <input
                  type="number"
                  min="0"
                  v-model="paymentForm.repairAmount"
                  :placeholder="paymentForm.repairCurrency === 'dollar' ? 'مبلغ التصليح بالدولار' : 'مبلغ التصليح بالدينار'"
                  class="pay-input pay-input--amber"
                />
              </div>

              <!-- ملاحظة -->
              <div>
                <label class="pay-label" for="pay_note">{{ $t('note') }}</label>
                <input
                  id="pay_note"
                  type="text"
                  v-model="paymentForm.note"
                  placeholder="ملاحظة إضافية..."
                  class="pay-input"
                />
              </div>

              <!-- المجموع -->
              <div v-if="canSubmit" class="pay-total">
                <div class="pay-total__label">المجموع الذي سيُحفظ</div>
                <div class="pay-total__value">
                  <span v-if="totalDollar > 0">{{ totalDollar }} $</span>
                  <span v-if="totalDollar > 0 && totalDinar > 0"> + </span>
                  <span v-if="totalDinar > 0">{{ formatDinar(totalDinar) }} د</span>
                </div>
              </div>
            </div>

            <!-- السجل -->
            <div v-else>
              <h1 class="mt-4 text-center text-lg font-bold text-gray-800 dark:text-gray-100">سجل الدفعات</h1>
              <div class="mb-5 mt-4 overflow-x-auto shadow-md sm:rounded-lg">
                <table class="pay-table w-full text-center text-sm">
                  <thead>
                    <tr class="bg-rose-600 text-white">
                      <th class="px-2 py-2 sm:px-4 sm:py-2">{{ $t('date') }}</th>
                      <th class="px-2 py-2 sm:px-4 sm:py-2">دولار</th>
                      <th class="px-2 py-2 sm:px-4 sm:py-2">دينار</th>
                      <th class="px-2 py-2 sm:px-4 sm:py-2">ملاحظة</th>
                      <th class="px-2 py-2 sm:px-4 sm:py-2">عبر</th>
                      <th class="px-2 py-2 sm:px-4 sm:py-2 print:hidden">{{ $t('execute') }}</th>
                    </tr>
                  </thead>
                  <tbody>
                    <template v-for="expense in (formData.carexpenses ?? [])" :key="expense.id">
                      <tr class="pay-table__row">
                        <td class="pay-table__cell">{{ expense.created }}</td>
                        <td class="pay-table__cell">{{ Number(expense.amount_dollar) || 0 }}</td>
                        <td class="pay-table__cell">{{ formatDinar(Number(expense.amount_dinar) || 0) }}</td>
                        <td class="pay-table__cell">{{ expense.note }}</td>
                        <td class="pay-table__cell">{{ expense.user?.name }}</td>
                        <td class="pay-table__cell">
                          <button
                            class="mx-1 rounded bg-orange-500 px-1 py-1 text-white"
                            @click="openModalDelClient(expense)"
                          >
                            <trash />
                          </button>
                        </td>
                      </tr>
                    </template>
                  </tbody>
                </table>
              </div>
              <div class="text-center">
                <a
                  target="_blank"
                  :href="`/api/getIndexExpensesPrint?car_id=${formData.id}`"
                  class="m-1 inline-flex rounded bg-blue-500 px-4 py-1 text-white"
                >
                  جميع الدفعات
                  <print />
                </a>
              </div>
            </div>
          </div>

          <div class="modal-footer my-2">
            <div class="flex flex-row">
              <div class="basis-1/2 px-4">
                <button class="modal-default-button rounded bg-gray-500 py-3" @click="closeModal">
                  {{ $t('cancel') }}
                </button>
              </div>
              <div v-if="activeTab === 'add' && currentWork" class="basis-1/2 px-4">
                <button
                  class="modal-default-button col-6 rounded bg-rose-500 py-3"
                  :disabled="!canSubmit"
                  @click="submitPayment"
                >
                  {{ $t('yes') }}
                </button>
              </div>
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
  transition: opacity 0.3s ease;
}

.modal-wrapper {
  display: table-cell;
  vertical-align: middle;
}

.modal-container {
  width: 96%;
  max-width: 720px;
  min-width: 340px;
  margin: 0 auto;
  padding: 20px 28px 60px;
  background-color: #fff;
  border-radius: 14px;
  box-shadow: 0 12px 40px rgba(0, 0, 0, 0.25);
  transition: all 0.3s ease;
}

:global(.dark) .modal-container {
  background-color: #111827;
  box-shadow: 0 12px 40px rgba(0, 0, 0, 0.6);
}

.pay-label {
  display: block;
  margin-bottom: 0.35rem;
  font-size: 0.875rem;
  font-weight: 600;
  color: #374151;
}

:global(.dark) .pay-label {
  color: #e5e7eb;
}

.pay-hint {
  font-size: 0.75rem;
  color: #6b7280;
}

:global(.dark) .pay-hint {
  color: #9ca3af;
}

.pay-input {
  display: block;
  width: 100%;
  border-radius: 0.5rem;
  border: 2px solid #d1d5db;
  background: #fff;
  color: #111827;
  padding: 0.6rem 0.75rem;
  font-size: 1rem;
  font-weight: 500;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}

.pay-input:focus {
  outline: none;
  border-color: #34d399;
  box-shadow: 0 0 0 3px rgba(52, 211, 153, 0.25);
}

:global(.dark) .pay-input {
  border-color: #4b5563;
  background: #030712;
  color: #f9fafb;
}

:global(.dark) .pay-input:focus {
  border-color: #34d399;
  box-shadow: 0 0 0 3px rgba(52, 211, 153, 0.35);
}

.pay-input--highlight {
  font-size: 1.125rem;
  font-weight: 700;
}

.pay-input--amber:focus {
  border-color: #f59e0b;
  box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.25);
}

:global(.dark) .pay-input--amber:focus {
  border-color: #fbbf24;
  box-shadow: 0 0 0 3px rgba(251, 191, 36, 0.35);
}

.pay-card {
  border-radius: 0.875rem;
  border: 2px solid;
  padding: 1rem;
}

.pay-card--green {
  border-color: #6ee7b7;
  background: #ecfdf5;
}

:global(.dark) .pay-card--green {
  border-color: #059669;
  background: #064e3b;
}

.pay-card--amber {
  border-color: #fcd34d;
  background: #fffbeb;
}

:global(.dark) .pay-card--amber {
  border-color: #d97706;
  background: #451a03;
}

.pay-card__title {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 0.75rem;
  font-weight: 700;
  color: #065f46;
}

:global(.dark) .pay-card__title {
  color: #6ee7b7;
}

.pay-card__title--amber {
  color: #92400e;
}

:global(.dark) .pay-card__title--amber {
  color: #fcd34d;
}

.pay-quick-btn {
  border-radius: 0.5rem;
  border: 2px solid;
  padding: 0.5rem 0.35rem;
  font-size: 0.8rem;
  font-weight: 700;
  transition: all 0.15s ease;
}

.pay-quick-btn--green {
  border-color: #10b981;
  background: #fff;
  color: #047857;
}

.pay-quick-btn--green:hover {
  background: #d1fae5;
}

:global(.dark) .pay-quick-btn--green {
  border-color: #34d399;
  background: #022c22;
  color: #a7f3d0;
}

:global(.dark) .pay-quick-btn--green:hover {
  background: #065f46;
  color: #ecfdf5;
}

.pay-currency-btn {
  border-radius: 0.5rem;
  border: 2px solid #fcd34d;
  background: #fff;
  color: #92400e;
  padding: 0.5rem;
  font-size: 0.875rem;
  font-weight: 700;
  transition: all 0.15s ease;
}

:global(.dark) .pay-currency-btn {
  border-color: #d97706;
  background: #1c1917;
  color: #fde68a;
}

.pay-currency-btn--active-amber {
  border-color: #d97706;
  background: #d97706;
  color: #fff;
}

:global(.dark) .pay-currency-btn--active-amber {
  border-color: #fbbf24;
  background: #f59e0b;
  color: #1c1917;
}

.pay-total {
  border-radius: 0.875rem;
  border: 2px solid #93c5fd;
  background: #eff6ff;
  padding: 1rem;
  text-align: center;
}

:global(.dark) .pay-total {
  border-color: #3b82f6;
  background: #1e3a5f;
}

.pay-total__label {
  font-size: 0.875rem;
  color: #1d4ed8;
}

:global(.dark) .pay-total__label {
  color: #93c5fd;
}

.pay-total__value {
  margin-top: 0.25rem;
  font-size: 1.25rem;
  font-weight: 800;
  color: #1e3a8a;
}

:global(.dark) .pay-total__value {
  color: #dbeafe;
}

.pay-table__row:nth-child(even) {
  background: #f9fafb;
}

:global(.dark) .pay-table__row:nth-child(even) {
  background: #1f2937;
}

.pay-table__cell {
  border: 1px solid #e5e7eb;
  padding: 0.5rem 0.75rem;
  color: #374151;
}

:global(.dark) .pay-table__cell {
  border-color: #4b5563;
  color: #e5e7eb;
}

.modal-default-button:disabled {
  opacity: 0.45;
  cursor: not-allowed;
}

.modal-body {
  margin: 16px 0;
}

.modal-default-button {
  width: 100%;
  color: #fff;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

.modal-enter-from .modal-container,
.modal-leave-to .modal-container {
  transform: scale(1.05);
}
</style>

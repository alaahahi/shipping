<script setup>
import { computed, ref, watch } from 'vue';
import print from "@/Components/icon/print.vue";
import trash from "@/Components/icon/trash.vue";
import { useToast } from "vue-toastification";
import axios from 'axios';

const toast = useToast();
const emit = defineEmits(['close', 'a']);

const activeTab = ref('add');
let repairRowId = 0;

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
  registrationDinarManual: '',
  selectedDinarPresets: [],
  note: '',
});

const repairItems = ref([]);

function resetPaymentForm() {
  repairRowId = 0;
  paymentForm.value = {
    registrationDollar: '',
    registrationDinarManual: '',
    selectedDinarPresets: [],
    note: '',
  };
  repairItems.value = [];
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

function formatNumber(n) {
  return new Intl.NumberFormat('en-US').format(Number(n) || 0);
}

function toggleDinarPreset(key) {
  const selected = paymentForm.value.selectedDinarPresets;
  const idx = selected.indexOf(key);
  if (idx >= 0) {
    selected.splice(idx, 1);
  } else {
    selected.push(key);
  }
}

function isPresetSelected(key) {
  return paymentForm.value.selectedDinarPresets.includes(key);
}

const registrationDinarFromPresets = computed(() => {
  let total = 0;
  for (const preset of REGISTRATION_DINAR_PRESETS) {
    if (paymentForm.value.selectedDinarPresets.includes(preset.key)) {
      total += preset.amount;
    }
  }
  return total;
});

const registrationDinarTotal = computed(() => {
  return registrationDinarFromPresets.value + parseAmount(paymentForm.value.registrationDinarManual);
});

const repairDollar = computed(() => {
  return repairItems.value
    .filter((item) => item.currency === 'dollar')
    .reduce((sum, item) => sum + parseAmount(item.amount), 0);
});

const repairDinar = computed(() => {
  return repairItems.value
    .filter((item) => item.currency === 'dinar')
    .reduce((sum, item) => sum + parseAmount(item.amount), 0);
});

const totalDollar = computed(() => parseAmount(paymentForm.value.registrationDollar) + repairDollar.value);
const totalDinar = computed(() => registrationDinarTotal.value + repairDinar.value);

function addRepairRow() {
  repairItems.value.push({
    id: ++repairRowId,
    currency: 'dollar',
    amount: '',
    note: '',
  });
}

function removeRepairRow(id) {
  repairItems.value = repairItems.value.filter((item) => item.id !== id);
}

function buildNote() {
  const parts = [];

  const regDollar = parseAmount(paymentForm.value.registrationDollar);
  if (regDollar > 0) parts.push(`تسجيل ${formatNumber(regDollar)}$`);

  if (registrationDinarTotal.value > 0) {
    const dinarDetail = [];
    for (const preset of REGISTRATION_DINAR_PRESETS) {
      if (paymentForm.value.selectedDinarPresets.includes(preset.key)) {
        dinarDetail.push(preset.label);
      }
    }
    const manual = parseAmount(paymentForm.value.registrationDinarManual);
    if (manual > 0) dinarDetail.push(formatNumber(manual));
    parts.push(`تسجيل ${dinarDetail.join(' + ')} د`);
  }

  for (const item of repairItems.value) {
    const amount = parseAmount(item.amount);
    if (amount <= 0) continue;
    const label = item.currency === 'dollar' ? `${formatNumber(amount)}$` : `${formatNumber(amount)} د`;
    const detail = item.note?.trim() ? `${label} (${item.note.trim()})` : label;
    parts.push(`تصليح ${detail}`);
  }

  const userNote = paymentForm.value.note?.trim() || '';
  if (userNote && parts.length) return `${userNote} — ${parts.join(' | ')}`;
  if (parts.length) return parts.join(' | ');
  return userNote;
}

const canSubmit = computed(() => totalDollar.value > 0 || totalDinar.value > 0);

function submitPayment() {
  if (!canSubmit.value) return;

  emit('a', {
    id: props.formData?.id,
    amountDollar: totalDollar.value,
    amountDinar: totalDinar.value,
    amountNote: buildNote(),
  });
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
                  <label class="pay-label" for="reg_dinar_manual">دينار د.ع</label>
                  <input
                    id="reg_dinar_manual"
                    type="number"
                    min="0"
                    v-model="paymentForm.registrationDinarManual"
                    placeholder="0"
                    class="pay-input pay-input--highlight mb-2"
                  />
                  <p class="pay-hint mb-3">اكتب مبلغاً يدوياً أو اختر من الأزرار (الضغط مرة أخرى يلغي الاختيار)</p>

                  <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                    <button
                      v-for="preset in REGISTRATION_DINAR_PRESETS"
                      :key="preset.key"
                      type="button"
                      @click="toggleDinarPreset(preset.key)"
                      :class="[
                        'pay-quick-btn pay-quick-btn--green',
                        isPresetSelected(preset.key) ? 'pay-quick-btn--green-active' : '',
                      ]"
                    >
                      {{ preset.label }}
                    </button>
                  </div>

                  <div
                    v-if="registrationDinarTotal > 0"
                    class="pay-subtotal pay-subtotal--green mt-3"
                  >
                    مجموع التسجيل بالدينار: {{ formatNumber(registrationDinarTotal) }} د
                  </div>
                </div>
              </div>

              <!-- مصروف تصليح — قائمة -->
              <div class="pay-card pay-card--amber">
                <div class="flex items-center justify-between gap-2 mb-3">
                  <div class="pay-card__title pay-card__title--amber mb-0">
                    <span class="text-xl">🔧</span>
                    <span>مصروف تصليح</span>
                  </div>
                  <button type="button" class="pay-add-btn" @click="addRepairRow">
                    + إضافة
                  </button>
                </div>

                <p v-if="repairItems.length === 0" class="pay-hint text-center py-2">
                  اضغط + لإضافة بند تصليح
                </p>

                <div v-for="(item, index) in repairItems" :key="item.id" class="repair-row">
                  <div class="repair-row__header">
                    <span class="repair-row__index">#{{ index + 1 }}</span>
                    <button type="button" class="repair-row__remove" @click="removeRepairRow(item.id)">
                      حذف
                    </button>
                  </div>

                  <div class="mb-2 flex gap-2">
                    <button
                      type="button"
                      @click="item.currency = 'dollar'"
                      :class="['pay-currency-btn flex-1', item.currency === 'dollar' ? 'pay-currency-btn--active-amber' : '']"
                    >
                      دولار
                    </button>
                    <button
                      type="button"
                      @click="item.currency = 'dinar'"
                      :class="['pay-currency-btn flex-1', item.currency === 'dinar' ? 'pay-currency-btn--active-amber' : '']"
                    >
                      دينار
                    </button>
                  </div>

                  <input
                    type="number"
                    min="0"
                    v-model="item.amount"
                    :placeholder="item.currency === 'dollar' ? 'المبلغ بالدولار' : 'المبلغ بالدينار'"
                    class="pay-input pay-input--amber mb-2"
                  />
                  <input
                    type="text"
                    v-model="item.note"
                    placeholder="ملاحظة البند (اختياري)"
                    class="pay-input pay-input--amber text-sm"
                  />
                </div>

                <div v-if="repairDollar > 0 || repairDinar > 0" class="pay-subtotal pay-subtotal--amber mt-3">
                  <span v-if="repairDollar > 0">{{ formatNumber(repairDollar) }} $</span>
                  <span v-if="repairDollar > 0 && repairDinar > 0"> + </span>
                  <span v-if="repairDinar > 0">{{ formatNumber(repairDinar) }} د</span>
                </div>
              </div>

              <div>
                <label class="pay-label" for="pay_note">{{ $t('note') }}</label>
                <input
                  id="pay_note"
                  type="text"
                  v-model="paymentForm.note"
                  placeholder="ملاحظة عامة..."
                  class="pay-input"
                />
              </div>

              <div v-if="canSubmit" class="pay-total">
                <div class="pay-total__label">المجموع الذي سيُحفظ</div>
                <div class="pay-total__value">
                  <span v-if="totalDollar > 0">{{ formatNumber(totalDollar) }} $</span>
                  <span v-if="totalDollar > 0 && totalDinar > 0"> + </span>
                  <span v-if="totalDinar > 0">{{ formatNumber(totalDinar) }} د</span>
                </div>
              </div>
            </div>

            <!-- السجل -->
            <div v-else class="record-panel">
              <h1 class="record-panel__title">سجل الدفعات</h1>

              <div class="record-table-wrap">
                <table class="record-table w-full text-center text-sm">
                  <thead>
                    <tr>
                      <th>{{ $t('date') }}</th>
                      <th>دولار</th>
                      <th>دينار</th>
                      <th>ملاحظة</th>
                      <th>عبر</th>
                      <th class="print:hidden">{{ $t('execute') }}</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-if="!(formData.carexpenses ?? []).length">
                      <td colspan="6" class="record-table__empty">لا توجد دفعات مسجّلة</td>
                    </tr>
                    <tr
                      v-for="expense in (formData.carexpenses ?? [])"
                      :key="expense.id"
                      class="record-table__row"
                    >
                      <td class="record-table__cell">{{ expense.created }}</td>
                      <td class="record-table__cell record-table__cell--dollar">
                        {{ formatNumber(Number(expense.amount_dollar) || 0) }}
                      </td>
                      <td class="record-table__cell record-table__cell--dinar">
                        {{ formatNumber(Number(expense.amount_dinar) || 0) }}
                      </td>
                      <td class="record-table__cell record-table__cell--note">{{ expense.note }}</td>
                      <td class="record-table__cell">{{ expense.user?.name }}</td>
                      <td class="record-table__cell print:hidden">
                        <button
                          type="button"
                          class="record-table__delete"
                          @click="openModalDelClient(expense)"
                        >
                          <trash />
                        </button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>

              <div class="text-center mt-4">
                <a
                  target="_blank"
                  :href="`/api/getIndexExpensesPrint?car_id=${formData.id}`"
                  class="record-print-btn"
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
                <button type="button" class="modal-default-button rounded bg-gray-500 py-3" @click="closeModal">
                  {{ $t('cancel') }}
                </button>
              </div>
              <div v-if="activeTab === 'add' && currentWork" class="basis-1/2 px-4">
                <button
                  type="button"
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
  background-color: rgba(0, 0, 0, 0.55);
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
  font-variant-numeric: lining-nums;
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

.pay-input--highlight {
  font-size: 1.125rem;
  font-weight: 700;
}

.pay-input--amber:focus {
  border-color: #f59e0b;
  box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.25);
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
  border: 2px solid #10b981;
  background: #fff;
  color: #047857;
  padding: 0.5rem 0.35rem;
  font-size: 0.8rem;
  font-weight: 700;
  font-variant-numeric: lining-nums;
  transition: all 0.15s ease;
}

.pay-quick-btn--green-active {
  border-color: #047857;
  background: #10b981;
  color: #fff;
}

:global(.dark) .pay-quick-btn {
  border-color: #34d399;
  background: #022c22;
  color: #a7f3d0;
}

:global(.dark) .pay-quick-btn--green-active {
  background: #059669;
  color: #ecfdf5;
}

.pay-add-btn {
  border-radius: 0.5rem;
  border: 2px solid #d97706;
  background: #f59e0b;
  color: #fff;
  padding: 0.35rem 0.75rem;
  font-size: 0.8rem;
  font-weight: 700;
  transition: opacity 0.15s;
}

.pay-add-btn:hover {
  opacity: 0.9;
}

:global(.dark) .pay-add-btn {
  background: #f59e0b;
  color: #1c1917;
}

.repair-row {
  border-radius: 0.625rem;
  border: 1px dashed #fbbf24;
  background: rgba(255, 255, 255, 0.65);
  padding: 0.75rem;
  margin-bottom: 0.75rem;
}

:global(.dark) .repair-row {
  border-color: #b45309;
  background: rgba(0, 0, 0, 0.25);
}

.repair-row__header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.5rem;
}

.repair-row__index {
  font-size: 0.75rem;
  font-weight: 700;
  color: #92400e;
}

:global(.dark) .repair-row__index {
  color: #fcd34d;
}

.repair-row__remove {
  font-size: 0.75rem;
  color: #dc2626;
  font-weight: 600;
}

.pay-currency-btn {
  border-radius: 0.5rem;
  border: 2px solid #fcd34d;
  background: #fff;
  color: #92400e;
  padding: 0.5rem;
  font-size: 0.875rem;
  font-weight: 700;
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

.pay-subtotal {
  border-radius: 0.5rem;
  padding: 0.5rem 0.75rem;
  text-align: center;
  font-size: 0.875rem;
  font-weight: 700;
  font-variant-numeric: lining-nums;
}

.pay-subtotal--green {
  background: #d1fae5;
  color: #065f46;
}

:global(.dark) .pay-subtotal--green {
  background: #065f46;
  color: #d1fae5;
}

.pay-subtotal--amber {
  background: #fef3c7;
  color: #92400e;
}

:global(.dark) .pay-subtotal--amber {
  background: #78350f;
  color: #fde68a;
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
  font-variant-numeric: lining-nums;
}

:global(.dark) .pay-total__value {
  color: #dbeafe;
}

.record-panel {
  padding: 0.5rem 1rem 1rem;
}

.record-panel__title {
  margin-top: 1rem;
  text-align: center;
  font-size: 1.125rem;
  font-weight: 700;
  color: #1f2937;
}

:global(.dark) .record-panel__title {
  color: #f3f4f6;
}

.record-table-wrap {
  margin-top: 1rem;
  overflow-x: auto;
  border-radius: 0.75rem;
  border: 2px solid #e5e7eb;
  box-shadow: 0 4px 14px rgba(0, 0, 0, 0.06);
}

:global(.dark) .record-table-wrap {
  border-color: #4b5563;
  box-shadow: 0 4px 14px rgba(0, 0, 0, 0.35);
}

.record-table thead tr {
  background: linear-gradient(135deg, #e11d48, #be123c);
  color: #fff;
}

:global(.dark) .record-table thead tr {
  background: linear-gradient(135deg, #9f1239, #881337);
}

.record-table th {
  padding: 0.65rem 0.5rem;
  font-size: 0.75rem;
  font-weight: 700;
}

.record-table__row:nth-child(even) {
  background: #f8fafc;
}

:global(.dark) .record-table__row:nth-child(even) {
  background: #1f2937;
}

.record-table__row:hover {
  background: #f1f5f9;
}

:global(.dark) .record-table__row:hover {
  background: #374151;
}

.record-table__cell {
  border-top: 1px solid #e5e7eb;
  padding: 0.55rem 0.5rem;
  color: #374151;
  font-variant-numeric: lining-nums;
}

:global(.dark) .record-table__cell {
  border-color: #4b5563;
  color: #e5e7eb;
}

.record-table__cell--dollar {
  font-weight: 700;
  color: #15803d;
}

:global(.dark) .record-table__cell--dollar {
  color: #4ade80;
}

.record-table__cell--dinar {
  font-weight: 700;
  color: #1d4ed8;
}

:global(.dark) .record-table__cell--dinar {
  color: #60a5fa;
}

.record-table__cell--note {
  max-width: 12rem;
  font-size: 0.8rem;
  text-align: right;
}

.record-table__empty {
  padding: 1.5rem;
  color: #9ca3af;
  font-style: italic;
}

.record-table__delete {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: 0.375rem;
  background: #f97316;
  padding: 0.25rem 0.5rem;
  color: #fff;
}

.record-print-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  border-radius: 0.5rem;
  background: #2563eb;
  padding: 0.45rem 1rem;
  color: #fff;
  font-weight: 600;
}

:global(.dark) .record-print-btn {
  background: #1d4ed8;
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

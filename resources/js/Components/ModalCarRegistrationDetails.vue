<script setup>
import { computed, ref, watch } from 'vue';
import axios from 'axios';
import print from '@/Components/icon/print.vue';
import { useToast } from 'vue-toastification';

const toast = useToast();

const props = defineProps({
  show: Boolean,
  carId: [Number, String],
});

const emit = defineEmits(['close', 'updated']);

const loading = ref(false);
const saving = ref(false);
const details = ref(null);
const editingRate = ref(false);
const rateInput = ref('');
const showAddForm = ref(false);
const addForm = ref({
  currency: 'dinar',
  amount: '',
  item_note: '',
  item_type: 'repair',
});

const CONTRACT_DEFAULT_AMOUNT = 200000;
const isContractType = computed(() => addForm.value.item_type === 'contract');

function formatNumber(n) {
  return new Intl.NumberFormat('en-US').format(Number(n) || 0);
}

function isSixDigitRate(value) {
  const digits = String(value ?? '').trim().replace(/\D/g, '');
  return /^\d{6}$/.test(digits);
}

const isRateInputValid = computed(() => isSixDigitRate(rateInput.value));

function calcRateForConversion(rate) {
  const r = Number(rate);
  if (!r || r <= 0) return null;
  return r > 9999 ? r / 100 : r;
}

function computeLinkedUsd(dinar, dollar, rate) {
  const calc = calcRateForConversion(rate);
  if (!calc) return null;
  return Math.trunc(Number(dinar || 0) / calc) + Math.trunc(Number(dollar || 0));
}

const activeRate = computed(() => {
  if (editingRate.value && isSixDigitRate(rateInput.value)) {
    return Math.trunc(Number(rateInput.value));
  }
  return details.value?.link_exchange_rate
    ? Math.trunc(Number(details.value.link_exchange_rate))
    : null;
});

const convertedUsdTotal = computed(() => {
  if (!details.value || !activeRate.value) return null;
  return computeLinkedUsd(
    details.value.total_dinar,
    details.value.total_dollar,
    activeRate.value,
  );
});

const conversionMatchesCar = computed(() => {
  if (convertedUsdTotal.value === null || !details.value?.is_linked) return null;
  const inSales = details.value.linked_portion_in_sales ?? details.value.linked_usd_total;
  return inSales === convertedUsdTotal.value;
});

function resetAddForm() {
  addForm.value = {
    currency: 'dinar',
    amount: '',
    item_note: '',
    item_type: 'repair',
  };
}

watch(() => addForm.value.item_type, (type) => {
  if (type === 'contract') {
    addForm.value.currency = 'dinar';
    addForm.value.item_note = '';
    addForm.value.amount = String(CONTRACT_DEFAULT_AMOUNT);
  }
});

async function refreshAfterChange() {
  await loadDetails();
  emit('updated');
}

watch(() => props.show, (visible) => {
  if (visible && props.carId) {
    editingRate.value = false;
    showAddForm.value = false;
    rateInput.value = '';
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
    if (details.value?.link_exchange_rate) {
      rateInput.value = String(Math.trunc(details.value.link_exchange_rate));
    }
    resetAddForm();
  } catch (error) {
    console.error(error);
    details.value = null;
  } finally {
    loading.value = false;
  }
}

function startEditRate() {
  rateInput.value = details.value?.link_exchange_rate
    ? String(Math.trunc(details.value.link_exchange_rate))
    : '';
  editingRate.value = true;
}

function cancelEditRate() {
  editingRate.value = false;
  rateInput.value = details.value?.link_exchange_rate
    ? String(Math.trunc(details.value.link_exchange_rate))
    : '';
}

async function saveExchangeRate() {
  if (!isRateInputValid.value || !details.value?.car?.id) return;

  saving.value = true;
  try {
    const response = await axios.post('/api/updateRegistrationExchangeRate', {
      car_id: details.value.car.id,
      exchangeRate: Math.trunc(Number(rateInput.value)),
    });
    const data = response.data;
    if (details.value) {
      details.value.link_exchange_rate = data.link_exchange_rate;
      details.value.linked_usd_total = data.linked_usd_total;
      details.value.linked_portion_in_sales = data.linked_portion_in_sales;
      if (details.value.car && data.car) {
        details.value.car.expenses = data.car.expenses;
        details.value.car.expenses_s = data.car.expenses_s;
      }
    }
    toast.success('تم تحديث سعر الصرف', { timeout: 2500, position: 'bottom-right', rtl: true });
    editingRate.value = false;
    await loadDetails();
    emit('updated');
  } catch (error) {
    toast.error(error?.response?.data?.error || 'تعذر تحديث سعر الصرف', {
      timeout: 3000,
      position: 'bottom-right',
      rtl: true,
    });
  } finally {
    saving.value = false;
  }
}

async function deleteLineItem(expense, lineIndex) {
  if (!details.value?.can_edit) return;
  if (!window.confirm('حذف هذا البند من التسجيل؟')) return;

  saving.value = true;
  try {
    await axios.post('/api/deleteRegistrationExpenseLine', {
      expense_id: expense.id,
      line_index: lineIndex,
    });
    toast.success('تم حذف البند', { timeout: 2500, position: 'bottom-right', rtl: true });
    await refreshAfterChange();
  } catch (error) {
    toast.error(error?.response?.data?.error || 'تعذر حذف البند', {
      timeout: 3000,
      position: 'bottom-right',
      rtl: true,
    });
  } finally {
    saving.value = false;
  }
}

function itemTypeLabel(type) {
  if (type === 'registration') return 'تسجيل';
  if (type === 'repair') return 'تصليح';
  if (type === 'contract') return 'عقد';
  return 'بند';
}

function itemTypeBadgeClass(type) {
  if (type === 'contract') return 'expense-item-badge expense-item-badge-contract';
  if (type === 'registration') return 'expense-item-badge expense-item-badge-registration';
  return 'expense-item-badge';
}

function openAddForm() {
  resetAddForm();
  showAddForm.value = true;
}

function parseAddAmount() {
  const n = Number(addForm.value.amount);
  return Number.isFinite(n) && n > 0 ? n : 0;
}

const canSubmitAdd = computed(() => parseAddAmount() > 0);

async function submitAddExpense() {
  if (!canSubmitAdd.value || !details.value?.car?.id || !details.value?.can_edit) return;

  saving.value = true;
  try {
    await axios.post('/api/addRegistrationExpenseLine', {
      car_id: details.value.car.id,
      create_new: true,
      currency: addForm.value.currency,
      amount: parseAddAmount(),
      item_type: addForm.value.item_type,
      item_note: addForm.value.item_note?.trim() || '',
    });
    toast.success('تمت إضافة المصروف', { timeout: 2500, position: 'bottom-right', rtl: true });
    showAddForm.value = false;
    await refreshAfterChange();
  } catch (error) {
    toast.error(error?.response?.data?.error || 'تعذر إضافة المصروف', {
      timeout: 3000,
      position: 'bottom-right',
      rtl: true,
    });
  } finally {
    saving.value = false;
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

            <div v-if="details?.link_exchange_rate || details?.is_linked" class="px-4 mt-2">
              <div v-if="!editingRate" class="registration-details-rate-row flex flex-col items-center gap-1">
                <div class="flex items-center justify-center gap-2 flex-wrap">
                  <p v-if="details?.link_exchange_rate" class="registration-details-rate text-sm font-bold">
                    سعر التحويل: {{ formatNumber(details.link_exchange_rate) }}
                  </p>
                  <button
                    v-if="details?.can_edit && (details?.is_linked || details?.link_exchange_rate)"
                    type="button"
                    class="rate-edit-btn"
                    :disabled="saving"
                    @click="startEditRate"
                  >
                    تعديل
                  </button>
                </div>
                <p v-if="convertedUsdTotal !== null" class="registration-details-conversion text-sm font-bold">
                  نتيجة التحويل: {{ formatNumber(convertedUsdTotal) }} $
                  <span v-if="details?.is_linked && details?.linked_portion_in_sales != null" class="text-xs font-semibold">
                    — في مصاريف المبيعات: {{ formatNumber(details.linked_portion_in_sales) }} $
                    <span v-if="conversionMatchesCar === true" class="conversion-ok">✓ متطابق</span>
                    <span v-else-if="conversionMatchesCar === false" class="conversion-warn">≠ غير متطابق</span>
                  </span>
                </p>
              </div>
              <div v-else class="rate-edit-form flex flex-col items-center gap-2">
                <label class="registration-details-label text-sm" for="reg_rate_edit">سعر الصرف (6 أرقام) — واحد لكل التسجيل</label>
                <input
                  id="reg_rate_edit"
                  v-model="rateInput"
                  type="number"
                  min="100000"
                  max="999999"
                  step="1"
                  class="rate-edit-input"
                  placeholder="مثال: 153000"
                />
                <p v-if="rateInput && !isRateInputValid" class="rate-edit-error">يجب أن يكون 6 أرقام</p>
                <p v-if="convertedUsdTotal !== null && isRateInputValid" class="registration-details-conversion text-sm font-bold">
                  نتيجة التحويل: {{ formatNumber(convertedUsdTotal) }} $
                </p>
                <div class="flex gap-2">
                  <button
                    type="button"
                    class="rate-save-btn"
                    :disabled="!isRateInputValid || saving"
                    @click="saveExchangeRate"
                  >
                    حفظ
                  </button>
                  <button type="button" class="rate-cancel-btn" :disabled="saving" @click="cancelEditRate">
                    إلغاء
                  </button>
                </div>
              </div>
            </div>
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

              <div v-if="details.can_edit" class="add-expense-panel mb-4 rounded-lg border border-emerald-300 dark:border-emerald-700 overflow-hidden">
                <button
                  type="button"
                  class="add-expense-toggle w-full px-3 py-2 text-sm font-bold"
                  :disabled="saving"
                  @click="showAddForm ? (showAddForm = false) : openAddForm()"
                >
                  {{ showAddForm ? 'إخفاء نموذج الإضافة' : '+ إضافة مصروف' }}
                </button>

                <div v-if="showAddForm" class="add-expense-form px-3 py-3 border-t border-emerald-200 dark:border-emerald-800">
                  <div class="add-type-selector mb-3">
                    <span class="registration-details-label text-xs block mb-1.5">نوع البند</span>
                    <div class="add-type-options flex flex-wrap gap-2">
                      <button
                        type="button"
                        class="add-type-btn"
                        :class="{ 'add-type-btn-active': addForm.item_type === 'repair' }"
                        @click="addForm.item_type = 'repair'"
                      >
                        تصليح
                      </button>
                      <button
                        type="button"
                        class="add-type-btn"
                        :class="{ 'add-type-btn-active': addForm.item_type === 'registration' }"
                        @click="addForm.item_type = 'registration'"
                      >
                        تسجيل
                      </button>
                      <button
                        type="button"
                        class="add-type-btn add-type-btn-contract"
                        :class="{ 'add-type-btn-active': addForm.item_type === 'contract' }"
                        @click="addForm.item_type = 'contract'"
                      >
                        عقد
                      </button>
                    </div>
                  </div>

                  <div v-if="isContractType" class="contract-form-panel mb-3 p-3 rounded-lg border border-amber-300 dark:border-amber-700 bg-amber-50 dark:bg-amber-950/40">
                    <p class="contract-form-hint text-xs font-semibold mb-2">
                      عقد الشركة — مبلغ ثابت بالدينار ({{ formatNumber(CONTRACT_DEFAULT_AMOUNT) }} د)
                    </p>
                    <label class="add-form-field block">
                      <span class="registration-details-label text-xs">مبلغ العقد (دينار)</span>
                      <input
                        v-model="addForm.amount"
                        type="number"
                        min="1"
                        step="any"
                        class="add-form-input w-full"
                        placeholder="200000"
                      />
                    </label>
                  </div>

                  <template v-else>
                    <div class="grid grid-cols-2 gap-2 mb-2">
                      <label class="add-form-field">
                        <span class="registration-details-label text-xs">العملة</span>
                        <select v-model="addForm.currency" class="add-form-input">
                          <option value="dinar">دينار</option>
                          <option value="dollar">دولار</option>
                        </select>
                      </label>
                      <label class="add-form-field">
                        <span class="registration-details-label text-xs">المبلغ</span>
                        <input
                          v-model="addForm.amount"
                          type="number"
                          min="1"
                          step="any"
                          class="add-form-input w-full"
                          placeholder="0"
                        />
                      </label>
                    </div>
                    <label class="add-form-field block mb-2">
                      <span class="registration-details-label text-xs">ملاحظة (اختياري)</span>
                      <input
                        v-model="addForm.item_note"
                        type="text"
                        class="add-form-input w-full"
                        placeholder="مثال: بنزين"
                      />
                    </label>
                  </template>
                  <button
                    type="button"
                    class="add-expense-submit w-full"
                    :disabled="!canSubmitAdd || saving"
                    @click="submitAddExpense"
                  >
                    حفظ المصروف
                  </button>
                </div>
              </div>

              <div
                v-for="expense in details.expenses"
                :key="expense.id"
                class="expense-block mb-4 rounded-lg border border-gray-300 dark:border-gray-500 overflow-hidden"
              >
                <div class="expense-block-header flex flex-wrap items-center justify-between gap-2 px-3 py-2 bg-gray-100 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-600">
                  <span class="expense-block-meta text-sm font-semibold">
                    {{ expense.created }} — {{ expense.user?.name }}
                  </span>
                  <span class="expense-block-totals text-sm font-bold">
                    <span class="registration-details-dollar">{{ formatNumber(expense.amount_dollar) }} $</span>
                    <span class="expense-block-totals-sep">/</span>
                    <span class="registration-details-dinar">{{ formatNumber(expense.amount_dinar) }} د</span>
                  </span>
                </div>

                <ul class="expense-items-list">
                  <li
                    v-for="item in expense.line_items"
                    :key="`${expense.id}-${item.index}`"
                    class="expense-item flex items-start justify-between gap-2 px-3 py-2.5 border-t border-gray-200 dark:border-gray-600"
                  >
                    <div class="expense-item-body text-right flex-1 min-w-0">
                      <span :class="itemTypeBadgeClass(item.type)">{{ itemTypeLabel(item.type) }}</span>
                      <span class="expense-item-label">{{ item.label }}</span>
                    </div>
                    <button
                      v-if="details.can_edit"
                      type="button"
                      class="expense-item-delete shrink-0"
                      :disabled="saving"
                      title="حذف البند"
                      @click="deleteLineItem(expense, item.index)"
                    >
                      ×
                    </button>
                  </li>
                  <li
                    v-if="!expense.line_items?.length"
                    class="expense-item expense-item-note px-3 py-2.5 border-t border-gray-200 dark:border-gray-600 text-sm"
                  >
                    {{ expense.note }}
                  </li>
                </ul>
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
  color-scheme: dark;
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

.registration-details-conversion {
  color: #047857 !important;
}

.conversion-ok {
  color: #16a34a !important;
}

.conversion-warn {
  color: #dc2626 !important;
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

.registration-details-dollar {
  color: #166534 !important;
}

.registration-details-dinar {
  color: #1e40af !important;
}

.expense-block-meta {
  color: #1f2937 !important;
}

.expense-block-totals-sep {
  margin: 0 0.25rem;
  color: #6b7280 !important;
}

.expense-items-list {
  background: #fff;
}

.expense-item {
  background: #fff;
}

.expense-item:nth-child(even) {
  background: #f8fafc;
}

.expense-item-badge {
  display: inline-block;
  margin-left: 0.5rem;
  padding: 0.125rem 0.5rem;
  border-radius: 9999px;
  font-size: 0.6875rem;
  font-weight: 700;
  background: #dbeafe;
  color: #1e40af !important;
}

.expense-item-badge-registration {
  background: #dcfce7;
  color: #166534 !important;
}

.expense-item-badge-contract {
  background: #fef3c7;
  color: #92400e !important;
}

.add-type-options {
  justify-content: center;
}

.add-type-btn {
  flex: 1;
  min-width: 72px;
  padding: 0.5rem 0.75rem;
  border-radius: 8px;
  border: 1px solid #d1d5db;
  background: #fff;
  color: #374151 !important;
  font-size: 0.8125rem;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.15s, border-color 0.15s;
}

.add-type-btn:hover {
  border-color: #9ca3af;
  background: #f9fafb;
}

.add-type-btn-active {
  border-color: #059669;
  background: #ecfdf5;
  color: #047857 !important;
}

.add-type-btn-contract.add-type-btn-active {
  border-color: #d97706;
  background: #fffbeb;
  color: #92400e !important;
}

.contract-form-hint {
  color: #92400e !important;
}

.expense-item-label {
  color: #111827 !important;
  font-size: 0.875rem;
  font-weight: 600;
  line-height: 1.5;
}

.expense-item-note {
  color: #374151 !important;
  font-weight: 500;
  line-height: 1.5;
}

.expense-item-delete {
  width: 28px;
  height: 28px;
  border-radius: 6px;
  border: 1px solid #fca5a5;
  background: #fef2f2;
  color: #dc2626 !important;
  font-size: 1.125rem;
  line-height: 1;
  font-weight: 700;
  cursor: pointer;
}

.expense-item-delete:hover:not(:disabled) {
  background: #fee2e2;
}

.expense-item-delete:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.add-expense-toggle {
  background: #ecfdf5;
  color: #047857 !important;
  text-align: center;
}

.add-expense-form {
  background: #f8fafc;
}

.add-form-input {
  margin-top: 0.25rem;
  padding: 0.5rem 0.625rem;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 0.875rem;
  color: #111827 !important;
  background: #fff;
}

.add-expense-submit {
  height: 38px;
  border-radius: 6px;
  background: #059669;
  color: #fff !important;
  font-weight: 600;
  font-size: 0.875rem;
}

.add-expense-submit:disabled {
  opacity: 0.45;
  cursor: not-allowed;
}

.expense-block-add-btn {
  color: #1d4ed8 !important;
}

.expense-block-add-btn:hover:not(:disabled) {
  text-decoration: underline;
}

.rate-edit-btn,
.rate-save-btn,
.rate-cancel-btn {
  height: 32px;
  padding: 0 0.75rem;
  border-radius: 6px;
  font-size: 0.8125rem;
  font-weight: 600;
  cursor: pointer;
}

.rate-edit-btn {
  background: #fef3c7;
  border: 1px solid #f59e0b;
  color: #92400e !important;
}

.rate-save-btn {
  background: #4f46e5;
  border: none;
  color: #fff !important;
}

.rate-save-btn:disabled {
  opacity: 0.45;
  cursor: not-allowed;
}

.rate-cancel-btn {
  background: #e5e7eb;
  border: none;
  color: #374151 !important;
}

.rate-edit-input {
  width: 100%;
  max-width: 200px;
  padding: 0.5rem 0.75rem;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  text-align: center;
  color: #111827 !important;
  background: #fff;
}

.rate-edit-error {
  color: #dc2626 !important;
  font-size: 0.75rem;
}

:global(.dark) .registration-details-title {
  color: #ffffff !important;
  font-weight: 800;
}

:global(.dark) .registration-details-subtitle {
  color: #f3f4f6 !important;
  font-weight: 600;
}

:global(.dark) .registration-details-rate {
  color: #fcd34d !important;
}

:global(.dark) .registration-details-conversion {
  color: #6ee7b7 !important;
}

:global(.dark) .registration-details-loading,
:global(.dark) .registration-details-empty {
  color: #d1d5db !important;
}

:global(.dark) .registration-details-label {
  color: #f3f4f6 !important;
  font-weight: 700;
}

:global(.dark) .registration-details-summary-dollar {
  color: #86efac !important;
}

:global(.dark) .registration-details-summary-dinar {
  color: #93c5fd !important;
}

:global(.dark) .registration-details-dollar {
  color: #86efac !important;
}

:global(.dark) .registration-details-dinar {
  color: #93c5fd !important;
}

:global(.dark) .registration-details-modal .expense-block {
  border-color: #64748b !important;
  background: #0f172a;
}

:global(.dark) .registration-details-modal .expense-block-header {
  background: #1e293b !important;
  border-bottom-color: #64748b !important;
}

:global(.dark) .registration-details-modal .expense-block-meta {
  color: #f8fafc !important;
}

:global(.dark) .registration-details-modal .expense-block-totals-sep {
  color: #cbd5e1 !important;
}

:global(.dark) .registration-details-modal .expense-items-list {
  background: #0f172a;
}

:global(.dark) .registration-details-modal .expense-item {
  background: #0f172a !important;
  border-top-color: #475569 !important;
}

:global(.dark) .registration-details-modal .expense-item:nth-child(even) {
  background: #1e293b !important;
}

:global(.dark) .registration-details-modal .expense-item-badge {
  background: #1d4ed8;
  color: #eff6ff !important;
  border: 1px solid #60a5fa;
}

:global(.dark) .registration-details-modal .expense-item-badge-registration {
  background: #166534;
  color: #ecfdf5 !important;
  border: 1px solid #4ade80;
}

:global(.dark) .registration-details-modal .expense-item-badge-contract {
  background: #92400e;
  color: #fffbeb !important;
  border: 1px solid #fbbf24;
}

:global(.dark) .registration-details-modal .expense-item-label {
  color: #ffffff !important;
  font-size: 0.9375rem;
  font-weight: 700;
}

:global(.dark) .registration-details-modal .expense-item-note {
  color: #e2e8f0 !important;
}

:global(.dark) .registration-details-modal .expense-item-delete {
  border-color: #f87171;
  background: #450a0a;
  color: #fecaca !important;
}

:global(.dark) .registration-details-modal .expense-item-delete:hover:not(:disabled) {
  background: #7f1d1d;
}

:global(.dark) .registration-details-modal .conversion-ok {
  color: #4ade80 !important;
}

:global(.dark) .registration-details-modal .conversion-warn {
  color: #f87171 !important;
}

:global(.dark) .add-type-btn {
  background: #1f2937;
  border-color: #4b5563;
  color: #e5e7eb !important;
}

:global(.dark) .add-type-btn-active {
  background: #064e3b;
  border-color: #059669;
  color: #6ee7b7 !important;
}

:global(.dark) .add-type-btn-contract.add-type-btn-active {
  background: #78350f;
  border-color: #d97706;
  color: #fde68a !important;
}

:global(.dark) .contract-form-hint {
  color: #fde68a !important;
}

:global(.dark) .rate-edit-input {
  background: #030712;
  border-color: #4b5563;
  color: #f9fafb !important;
}

:global(.dark) .add-expense-toggle {
  background: #064e3b;
  color: #6ee7b7 !important;
}

:global(.dark) .add-expense-form {
  background: #0f172a;
}

:global(.dark) .add-form-input {
  background: #030712;
  border-color: #6b7280;
  color: #ffffff !important;
  font-weight: 600;
}

:global(.dark) .add-form-input::placeholder {
  color: #9ca3af !important;
}

:global(.dark) .rate-edit-input::placeholder {
  color: #9ca3af !important;
}
</style>

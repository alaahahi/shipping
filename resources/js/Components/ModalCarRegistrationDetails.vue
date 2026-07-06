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

function formatNumber(n) {
  return new Intl.NumberFormat('en-US').format(Number(n) || 0);
}

function isSixDigitRate(value) {
  const digits = String(value ?? '').trim().replace(/\D/g, '');
  return /^\d{6}$/.test(digits);
}

const isRateInputValid = computed(() => isSixDigitRate(rateInput.value));

watch(() => props.show, (visible) => {
  if (visible && props.carId) {
    editingRate.value = false;
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
    await axios.post('/api/updateRegistrationExchangeRate', {
      car_id: details.value.car.id,
      exchangeRate: Math.trunc(Number(rateInput.value)),
      previousExchangeRate: details.value?.link_exchange_rate
        ? Math.trunc(Number(details.value.link_exchange_rate))
        : null,
    });
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
    await loadDetails();
    emit('updated');
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
  return 'بند';
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
              <div v-if="!editingRate" class="registration-details-rate-row flex items-center justify-center gap-2 flex-wrap">
                <p v-if="details?.link_exchange_rate" class="registration-details-rate text-sm font-bold">
                  سعر التحويل عند الربط: {{ formatNumber(details.link_exchange_rate) }}
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
              <div v-else class="rate-edit-form flex flex-col items-center gap-2">
                <label class="registration-details-label text-sm" for="reg_rate_edit">سعر الصرف (6 أرقام)</label>
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

              <div
                v-for="expense in details.expenses"
                :key="expense.id"
                class="expense-block mb-4 rounded-lg border border-gray-300 dark:border-gray-600 overflow-hidden"
              >
                <div class="expense-block-header flex flex-wrap items-center justify-between gap-2 px-3 py-2 bg-gray-100 dark:bg-gray-800">
                  <span class="expense-block-meta text-sm font-semibold">
                    {{ expense.created }} — {{ expense.user?.name }}
                  </span>
                  <span class="expense-block-totals text-sm font-bold">
                    <span class="registration-details-dollar">{{ formatNumber(expense.amount_dollar) }} $</span>
                    /
                    <span class="registration-details-dinar">{{ formatNumber(expense.amount_dinar) }} د</span>
                  </span>
                </div>

                <ul class="expense-items-list">
                  <li
                    v-for="item in expense.line_items"
                    :key="`${expense.id}-${item.index}`"
                    class="expense-item flex items-start justify-between gap-2 px-3 py-2 border-t border-gray-200 dark:border-gray-700"
                  >
                    <div class="expense-item-body text-right flex-1 min-w-0">
                      <span class="expense-item-badge">{{ itemTypeLabel(item.type) }}</span>
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
                    class="expense-item px-3 py-2 border-t border-gray-200 dark:border-gray-700 text-sm"
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

.registration-details-dollar {
  color: #166534 !important;
}

.registration-details-dinar {
  color: #1e40af !important;
}

.expense-block-meta {
  color: #1f2937 !important;
}

.expense-item {
  background: #fff;
}

.expense-item:nth-child(even) {
  background: #f9fafb;
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

.expense-item-label {
  color: #111827 !important;
  font-size: 0.8125rem;
  line-height: 1.4;
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

:global(.dark) .registration-details-dollar {
  color: #86efac !important;
}

:global(.dark) .registration-details-dinar {
  color: #93c5fd !important;
}

:global(.dark) .expense-block-meta {
  color: #e5e7eb !important;
}

:global(.dark) .expense-item {
  background: #111827;
}

:global(.dark) .expense-item:nth-child(even) {
  background: #1f2937;
}

:global(.dark) .expense-item-badge {
  background: #1e3a8a;
  color: #bfdbfe !important;
}

:global(.dark) .expense-item-label {
  color: #f3f4f6 !important;
}

:global(.dark) .rate-edit-input {
  background: #030712;
  border-color: #4b5563;
  color: #f9fafb !important;
}
</style>

<script setup>
import { ref, watch, computed } from 'vue';
import axios from 'axios';
import { ModelListSelect } from 'vue-search-select';
import 'vue-search-select/dist/VueSearchSelect.css';
import { formatBaghdadTimestamp } from '@/utils/datetime';

const props = defineProps({
  show: Boolean,
  transaction: Object,
  sourceUserId: { type: [Number, String], default: null },
  sourceUserName: { type: String, default: '' },
  /** 'qasa' = قاصة→قاسة | 'box' = حركة صندوق مباشرة → قاصة */
  mode: { type: String, default: 'qasa' },
});

const emit = defineEmits(['close', 'saved']);

const selectedUserId = ref('');
const note = ref('');
const noteTouched = ref(false);
const saving = ref(false);
const loadingUsers = ref(false);
const error = ref('');
const walletUsers = ref([]);

const isBoxMode = computed(() => props.mode === 'box');
const sourceName = computed(() => (props.sourceUserName || '').trim() || (isBoxMode.value ? 'الصندوق' : 'المصدر'));

function nowBaghdad() {
  const raw = formatBaghdadTimestamp(new Date(), '');
  const m = String(raw).match(/(\d{2})\/(\d{2})\/(\d{4})\s+(\d{2}):(\d{2})/);
  if (!m) {
    const d = new Date();
    const pad = (n) => String(n).padStart(2, '0');
    return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())} ${pad(d.getHours())}:${pad(d.getMinutes())}`;
  }
  return `${m[3]}-${m[2]}-${m[1]} ${m[4]}:${m[5]}`;
}

function buildDefaultNote(toName) {
  const amount = Math.abs(Number(props.transaction?.amount ?? 0));
  const currency = props.transaction?.currency ?? '$';
  if (isBoxMode.value) {
    return `تم النقل من الصندوق/حركة مباشرة إلى قاصة ${toName} — المبلغ: ${amount} ${currency} — بتاريخ النقل: ${nowBaghdad()}`;
  }
  return `تم النقل من قاصة ${sourceName.value} إلى قاصة ${toName} — المبلغ: ${amount} ${currency} — بتاريخ النقل: ${nowBaghdad()}`;
}

function applyDefaultNote() {
  if (noteTouched.value || !selectedUserId.value) return;
  const target = walletUsers.value.find((u) => Number(u.id) === Number(selectedUserId.value));
  if (!target?.name) return;
  note.value = buildDefaultNote(target.name);
}

async function loadWalletUsers() {
  loadingUsers.value = true;
  error.value = '';
  try {
    const { data } = await axios.get('/api/walletUsersForTransfer');
    const sourceId = Number(props.sourceUserId);
    walletUsers.value = (data || []).filter((u) => Number(u.id) !== sourceId);
  } catch (e) {
    error.value = e.response?.data?.message || 'تعذر تحميل قائمة القاصات';
    walletUsers.value = [];
  } finally {
    loadingUsers.value = false;
  }
}

watch(
  () => [props.show, props.transaction],
  () => {
    if (!props.show) return;
    selectedUserId.value = '';
    note.value = '';
    noteTouched.value = false;
    error.value = '';
    loadWalletUsers();
  },
  { immediate: true }
);

watch(selectedUserId, () => {
  applyDefaultNote();
});

async function save() {
  if (!selectedUserId.value) {
    error.value = 'اختر القاصة الهدف';
    return;
  }

  const target = walletUsers.value.find((u) => Number(u.id) === Number(selectedUserId.value));
  if (!note.value.trim()) {
    note.value = buildDefaultNote(target?.name || 'الهدف');
  }

  saving.value = true;
  error.value = '';
  try {
    await axios.post('/api/transferWalletTransaction', {
      transaction_id: props.transaction.id,
      target_user_id: Number(selectedUserId.value),
      note: note.value.trim(),
    });
    emit('saved');
    emit('close');
  } catch (e) {
    error.value = e.response?.data?.message || 'تعذر نقل الحركة';
  } finally {
    saving.value = false;
  }
}
</script>

<template>
  <Transition name="modal">
    <div v-if="show" class="modal-mask">
      <div class="modal-wrapper">
        <div class="modal-container bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700">
          <div class="modal-header text-gray-900 dark:text-gray-100">
            <slot name="header" />
          </div>

          <div class="modal-body text-gray-800 dark:text-gray-200">
            <p v-if="transaction" class="mb-4 text-sm text-gray-600 dark:text-gray-300">
              حركة رقم <strong>{{ transaction.id }}</strong>
              — {{ Math.abs(transaction.amount) }} {{ transaction.currency ?? '$' }}
              <span class="mt-1 block text-xs text-gray-500 dark:text-gray-400">
                <template v-if="isBoxMode">
                  التاريخ الأصلي يبقى كما هو — تُصنَّف الحركة إلى قاصة دون حذف ودون تغيير رصيد الصندوق.
                </template>
                <template v-else>
                  التاريخ الأصلي يبقى كما هو — يتم تعديل انتساب القاصة فقط دون حذف.
                </template>
              </span>
            </p>

            <div class="mb-4 wallet-select-wrap">
              <label for="transfer_wallet_user_id" class="mb-1 block text-sm font-medium text-gray-800 dark:text-gray-100">
                نقل إلى قاصة
              </label>
              <p v-if="loadingUsers" class="mb-2 text-sm text-gray-500 dark:text-gray-400">جاري تحميل القاصات...</p>
              <ModelListSelect
                id="transfer_wallet_user_id"
                v-model="selectedUserId"
                :list="walletUsers"
                option-value="id"
                option-text="name"
                placeholder="ابحث عن القاصة..."
                :disabled="loadingUsers || saving"
              />
            </div>

            <div class="mb-4">
              <label for="transfer_note" class="mb-1 block text-sm font-medium text-gray-800 dark:text-gray-100">
                ملاحظة النقل
              </label>
              <input
                id="transfer_note"
                v-model="note"
                type="text"
                class="w-full rounded border border-gray-300 px-3 py-2 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
                placeholder="تُملأ تلقائياً عند اختيار القاصة الهدف"
                :disabled="saving"
                @input="noteTouched = true"
              />
            </div>

            <p class="text-xs text-gray-500 dark:text-gray-400">
              <template v-if="isBoxMode">
                المبلغ يبقى على الصندوق (نفس الأثر النقدي)؛ يُضاف انتساب للقاصة الهدف مع تسجيل النقل في التفاصيل.
              </template>
              <template v-else>
                حركة الصندوق الأب (إن وُجدت) تبقى على الصندوق؛ يتغيّر وصف انتساب القاصة فقط مع تسجيل النقل في التفاصيل.
              </template>
            </p>
            <p v-if="error" class="mt-3 text-sm text-red-600 dark:text-red-400">{{ error }}</p>
          </div>

          <div class="modal-footer my-2">
            <div class="flex w-full flex-row">
              <div class="basis-1/2 px-2">
                <button
                  type="button"
                  class="w-full rounded bg-gray-500 py-3 text-white"
                  :disabled="saving"
                  @click="$emit('close')"
                >
                  إغلاق
                </button>
              </div>
              <div class="basis-1/2 px-2">
                <button
                  type="button"
                  class="w-full rounded bg-indigo-600 py-3 text-white"
                  :disabled="saving || loadingUsers"
                  @click="save"
                >
                  {{ saving ? 'جاري النقل...' : 'تأكيد' }}
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
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 12px 180px;
  background-color: rgba(0, 0, 0, 0.5);
  overflow: hidden;
}

.modal-wrapper {
  width: 100%;
  max-width: min(900px, 100%);
  max-height: calc(100vh - 24px);
  overflow: visible;
}

.modal-container {
  padding: 28px 32px;
  border-radius: 12px;
  box-shadow: 0 4px 24px rgba(0, 0, 0, 0.25);
  min-height: 280px;
  overflow: visible;
}

.modal-body {
  margin: 16px 0;
  overflow: visible;
}

.wallet-select-wrap {
  position: relative;
  overflow: visible;
  color: #111827;
}

.wallet-select-wrap :deep(.ui.search.selection.dropdown) {
  width: 100%;
  min-height: 3rem;
  font-size: 1rem;
  background: #ffffff !important;
  color: #111827 !important;
  border-color: #d1d5db !important;
}

.wallet-select-wrap :deep(.ui.search.selection.dropdown > .text),
.wallet-select-wrap :deep(.ui.search.selection.dropdown > input.search) {
  color: #111827 !important;
}

.wallet-select-wrap :deep(.ui.search.selection.dropdown .menu) {
  max-height: 240px !important;
  overflow-x: hidden !important;
  overflow-y: auto !important;
  background: #ffffff !important;
  color: #111827 !important;
  border-color: #d1d5db !important;
}

.wallet-select-wrap :deep(.ui.search.selection.dropdown .menu .item),
.wallet-select-wrap :deep(.ui.search.selection.dropdown .menu .item .text) {
  color: #111827 !important;
  background: #ffffff !important;
}

.wallet-select-wrap :deep(.ui.search.selection.dropdown .menu .item:hover),
.wallet-select-wrap :deep(.ui.search.selection.dropdown .menu .item.selected),
.wallet-select-wrap :deep(.ui.search.selection.dropdown .menu .item.active) {
  background: #f3f4f6 !important;
  color: #111827 !important;
}

:global(.dark) .wallet-select-wrap {
  color: #f3f4f6;
}

:global(.dark) .wallet-select-wrap :deep(.ui.search.selection.dropdown) {
  background: #1f2937 !important;
  color: #f3f4f6 !important;
  border-color: #4b5563 !important;
}

:global(.dark) .wallet-select-wrap :deep(.ui.search.selection.dropdown > .text),
:global(.dark) .wallet-select-wrap :deep(.ui.search.selection.dropdown > input.search) {
  color: #f3f4f6 !important;
}

:global(.dark) .wallet-select-wrap :deep(.ui.search.selection.dropdown .menu) {
  max-height: 240px !important;
  overflow-x: hidden !important;
  overflow-y: auto !important;
  background: #111827 !important;
  color: #f3f4f6 !important;
  border-color: #4b5563 !important;
}

:global(.dark) .wallet-select-wrap :deep(.ui.search.selection.dropdown .menu .item),
:global(.dark) .wallet-select-wrap :deep(.ui.search.selection.dropdown .menu .item .text) {
  color: #f3f4f6 !important;
  background: #111827 !important;
  border-top-color: #374151 !important;
}

:global(.dark) .wallet-select-wrap :deep(.ui.search.selection.dropdown .menu .item:hover),
:global(.dark) .wallet-select-wrap :deep(.ui.search.selection.dropdown .menu .item.selected),
:global(.dark) .wallet-select-wrap :deep(.ui.search.selection.dropdown .menu .item.active) {
  background: #374151 !important;
  color: #f9fafb !important;
}

@media (max-width: 1024px) {
  .modal-mask {
    padding: 12px;
  }
}
</style>

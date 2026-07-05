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
  selectedDinarPresets: [],
  registrationDinarExtra: '',
  repairCurrency: 'dollar',
  repairAmount: '',
  note: '',
});

function resetPaymentForm() {
  paymentForm.value = {
    registrationDollar: '',
    selectedDinarPresets: [],
    registrationDinarExtra: '',
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

const registrationDinarTotal = computed(() => {
  let total = 0;
  for (const preset of REGISTRATION_DINAR_PRESETS) {
    if (paymentForm.value.selectedDinarPresets.includes(preset.key)) {
      total += preset.amount;
    }
  }
  total += parseAmount(paymentForm.value.registrationDinarExtra);
  return total;
});

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

  const presetLabels = REGISTRATION_DINAR_PRESETS
    .filter((p) => paymentForm.value.selectedDinarPresets.includes(p.key))
    .map((p) => p.label);
  const extraDinar = parseAmount(paymentForm.value.registrationDinarExtra);

  if (presetLabels.length || extraDinar > 0) {
    const dinarParts = [...presetLabels];
    if (extraDinar > 0) dinarParts.push(formatDinar(extraDinar));
    parts.push(`تسجيل ${dinarParts.join(' + ')} د`);
  }

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
      <div class="modal-wrapper max-h-[85vh]">
        <div class="modal-container dark:bg-gray-900 overflow-auto max-h-[85vh]">
          <div class="modal-header">
            <slot name="header" />
          </div>

          <div class="modal-body">
            <div class="text-sm font-medium text-center text-gray-500 border-b border-gray-200 dark:text-gray-400 dark:border-gray-700">
              <ul class="flex flex-wrap -mb-px">
                <li v-if="currentWork" class="mr-2" @click="setActiveTab('add')">
                  <button
                    class="inline-block p-4 border-b-2 border-transparent rounded-t-lg"
                    :class="activeTab === 'add' ? 'dark:text-blue-500 dark:border-blue-500' : 'hover:text-gray-600 hover:border-gray-300'"
                  >
                    اضافة
                  </button>
                </li>
                <li class="mr-2" @click="setActiveTab('record')">
                  <button
                    class="inline-block p-4 border-b-2 border-transparent rounded-t-lg"
                    :class="activeTab === 'record' ? 'dark:text-blue-500 dark:border-blue-500' : 'hover:text-gray-600 hover:border-gray-300'"
                  >
                    السجل
                  </button>
                </li>
              </ul>
            </div>

            <!-- إضافة -->
            <div v-if="activeTab === 'add' && currentWork" class="mx-4 mt-4 space-y-4">
              <h1 class="text-center text-lg font-bold dark:text-gray-100">تسجيل مصاريف السيارة</h1>

              <!-- التسجيل -->
              <div class="rounded-xl border-2 border-emerald-200 bg-emerald-50/80 p-4 dark:border-emerald-800 dark:bg-emerald-950/30">
                <div class="mb-3 flex items-center gap-2 font-bold text-emerald-800 dark:text-emerald-300">
                  <span class="text-xl">📋</span>
                  <span>رسوم التسجيل</span>
                </div>

                <div class="mb-4">
                  <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">دولار $</label>
                  <input
                    type="number"
                    min="0"
                    v-model="paymentForm.registrationDollar"
                    placeholder="0"
                    class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-400 focus:ring-emerald-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"
                  />
                </div>

                <div>
                  <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">دينار — اختر المبالغ</label>
                  <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                    <button
                      v-for="preset in REGISTRATION_DINAR_PRESETS"
                      :key="preset.key"
                      type="button"
                      @click="toggleDinarPreset(preset.key)"
                      :class="[
                        'rounded-lg border-2 px-2 py-2 text-sm font-semibold transition',
                        isPresetSelected(preset.key)
                          ? 'border-emerald-600 bg-emerald-600 text-white shadow-md'
                          : 'border-emerald-300 bg-white text-emerald-800 hover:bg-emerald-100 dark:border-emerald-700 dark:bg-gray-800 dark:text-emerald-300 dark:hover:bg-emerald-900/40',
                      ]"
                    >
                      {{ preset.label }}
                    </button>
                  </div>

                  <div class="mt-3">
                    <label class="mb-1 block text-xs text-gray-500 dark:text-gray-400">مبلغ دينار إضافي (اختياري)</label>
                    <input
                      type="number"
                      min="0"
                      v-model="paymentForm.registrationDinarExtra"
                      placeholder="0"
                      class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-emerald-400 focus:ring-emerald-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"
                    />
                  </div>

                  <div
                    v-if="registrationDinarTotal > 0"
                    class="mt-3 rounded-lg bg-emerald-100 px-3 py-2 text-center text-sm font-bold text-emerald-900 dark:bg-emerald-900/50 dark:text-emerald-200"
                  >
                    مجموع التسجيل بالدينار: {{ formatDinar(registrationDinarTotal) }} د
                  </div>
                </div>
              </div>

              <!-- مصروف تصليح -->
              <div class="rounded-xl border-2 border-amber-200 bg-amber-50/80 p-4 dark:border-amber-800 dark:bg-amber-950/30">
                <div class="mb-3 flex items-center gap-2 font-bold text-amber-800 dark:text-amber-300">
                  <span class="text-xl">🔧</span>
                  <span>مصروف تصليح</span>
                  <span class="text-xs font-normal text-amber-600 dark:text-amber-400">(منفصل عن التسجيل)</span>
                </div>

                <div class="mb-3 flex gap-2">
                  <button
                    type="button"
                    @click="paymentForm.repairCurrency = 'dollar'"
                    :class="[
                      'flex-1 rounded-lg border-2 py-2 text-sm font-semibold transition',
                      paymentForm.repairCurrency === 'dollar'
                        ? 'border-amber-600 bg-amber-600 text-white'
                        : 'border-amber-300 bg-white text-amber-800 dark:border-amber-700 dark:bg-gray-800 dark:text-amber-300',
                    ]"
                  >
                    💵 دولار
                  </button>
                  <button
                    type="button"
                    @click="paymentForm.repairCurrency = 'dinar'"
                    :class="[
                      'flex-1 rounded-lg border-2 py-2 text-sm font-semibold transition',
                      paymentForm.repairCurrency === 'dinar'
                        ? 'border-amber-600 bg-amber-600 text-white'
                        : 'border-amber-300 bg-white text-amber-800 dark:border-amber-700 dark:bg-gray-800 dark:text-amber-300',
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
                  class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-400 focus:ring-amber-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"
                />
              </div>

              <!-- ملاحظة -->
              <div>
                <label class="mb-1 block text-sm font-medium dark:text-gray-300">{{ $t('note') }}</label>
                <input
                  type="text"
                  v-model="paymentForm.note"
                  placeholder="ملاحظة إضافية..."
                  class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-400 focus:ring-indigo-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"
                />
              </div>

              <!-- المجموع -->
              <div
                v-if="canSubmit"
                class="rounded-xl border-2 border-blue-200 bg-blue-50 p-4 text-center dark:border-blue-800 dark:bg-blue-950/40"
              >
                <div class="text-sm text-blue-600 dark:text-blue-400">المجموع الذي سيُحفظ</div>
                <div class="mt-1 text-xl font-bold text-blue-900 dark:text-blue-200">
                  <span v-if="totalDollar > 0">{{ totalDollar }} $</span>
                  <span v-if="totalDollar > 0 && totalDinar > 0"> + </span>
                  <span v-if="totalDinar > 0">{{ formatDinar(totalDinar) }} د</span>
                </div>
              </div>
            </div>

            <!-- السجل -->
            <div v-else>
              <h1 class="mt-4 text-center dark:text-gray-200">سجل الدفعات</h1>
              <div class="mb-5 mt-4 overflow-x-auto shadow-md sm:rounded-lg">
                <table class="w-full divide-y divide-gray-200 text-center text-sm text-gray-500 dark:divide-gray-800 dark:text-gray-200">
                  <thead class="bg-gray-50 text-xs uppercase text-gray-700 dark:bg-gray-700 dark:text-gray-400">
                    <tr class="bg-rose-500 text-gray-100">
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
                      <tr class="text-center">
                        <td class="border px-4 py-2 dark:border-gray-800">{{ expense.created }}</td>
                        <td class="border px-4 py-2 dark:border-gray-800">{{ Number(expense.amount_dollar) || 0 }}</td>
                        <td class="border px-4 py-2 dark:border-gray-800">{{ formatDinar(Number(expense.amount_dinar) || 0) }}</td>
                        <td class="border px-4 py-2 dark:border-gray-800">{{ expense.note }}</td>
                        <td class="border px-4 py-2 dark:border-gray-800">{{ expense.user?.name }}</td>
                        <td class="border px-4 py-2 dark:border-gray-800">
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
  width: 92%;
  max-width: 520px;
  min-width: 320px;
  margin: 0 auto;
  padding: 20px 24px 60px;
  background-color: #fff;
  border-radius: 12px;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
  transition: all 0.3s ease;
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

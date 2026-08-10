<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
  show: Boolean,
  title: {
    type: String,
    default: '',
  },
  amountDollar: {
    type: [Number, String],
    default: 0,
  },
  amountDinar: {
    type: [Number, String],
    default: 0,
  },
  saving: Boolean,
});

const emit = defineEmits(['close', 'confirm']);

const note = ref('');

watch(
  () => props.show,
  (visible) => {
    if (visible) {
      note.value = '';
    }
  }
);

function formatNumber(n) {
  return new Intl.NumberFormat('en-US').format(Number(n) || 0);
}

function submit() {
  if (props.saving) return;
  emit('confirm', note.value.trim());
}
</script>

<template>
  <Transition name="modal">
    <div v-if="show" class="modal-mask">
      <div class="modal-wrapper">
        <div class="modal-container bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100">
          <h2 class="text-center text-lg font-bold mb-1">ترحيل للقاسة</h2>
          <p v-if="title" class="text-center text-sm text-gray-600 dark:text-gray-300 mb-3">{{ title }}</p>

          <div class="flex justify-center gap-4 mb-4 text-sm font-semibold">
            <span class="text-emerald-700 dark:text-emerald-400">{{ formatNumber(amountDollar) }} $</span>
            <span class="text-indigo-700 dark:text-indigo-400">{{ formatNumber(amountDinar) }} د</span>
          </div>

          <label class="block mb-4">
            <span class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1">ملاحظة الترحيل</span>
            <textarea
              v-model="note"
              rows="3"
              class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
              placeholder="تُضاف على ملاحظة القيد المحاسبي..."
            />
          </label>

          <div class="flex gap-3">
            <button
              type="button"
              class="flex-1 py-2 rounded-lg bg-violet-600 text-white font-semibold hover:bg-violet-700 disabled:opacity-50"
              :disabled="saving"
              @click="submit"
            >
              {{ saving ? 'جاري الترحيل...' : 'تأكيد الترحيل' }}
            </button>
            <button
              type="button"
              class="flex-1 py-2 rounded-lg bg-gray-500 text-white font-semibold hover:bg-gray-600 disabled:opacity-50"
              :disabled="saving"
              @click="emit('close')"
            >
              إلغاء
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
  background-color: rgba(0, 0, 0, 0.5);
  display: table;
}
.modal-wrapper {
  display: table-cell;
  vertical-align: middle;
}
.modal-container {
  width: min(460px, 94vw);
  margin: 0 auto;
  padding: 20px;
  border-radius: 12px;
  box-shadow: 0 8px 30px rgba(0, 0, 0, 0.25);
}
.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}
</style>

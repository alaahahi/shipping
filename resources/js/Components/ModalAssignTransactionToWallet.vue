<script setup>
import { ref, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
  show: Boolean,
  transaction: Object,
  walletUsers: { type: Array, default: () => [] },
});

const emit = defineEmits(['close', 'saved']);

const selectedUserId = ref('');
const saving = ref(false);
const error = ref('');

watch(
  () => [props.show, props.transaction],
  () => {
    if (!props.show) return;
    selectedUserId.value = '';
    error.value = '';
  },
  { immediate: true }
);

async function save() {
  if (!selectedUserId.value) {
    error.value = 'اختر القاسة';
    return;
  }
  saving.value = true;
  error.value = '';
  try {
    await axios.post('/api/assignTransactionToWallet', {
      transaction_id: props.transaction.id,
      user_id: Number(selectedUserId.value),
    });
    emit('saved');
    emit('close');
  } catch (e) {
    error.value = e.response?.data?.message || 'تعذر إسناد الحركة';
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
            </p>
            <div class="mb-4">
              <label for="wallet_user_id" class="block text-sm font-medium text-gray-800 dark:text-gray-100 mb-1">
                إسناد إلى قاسة
              </label>
              <select
                id="wallet_user_id"
                v-model="selectedUserId"
                class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm"
              >
                <option value="">— اختر القاسة —</option>
                <option v-for="user in walletUsers" :key="user.id" :value="user.id">
                  {{ user.name }}
                </option>
              </select>
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400">
              ستُحوَّل الحركة لتظهر كسحب من القاسة المختارة (مثل وصل الصرف من القاسة) مع بقاء خصم الصندوق كما هو.
            </p>
            <p v-if="error" class="mt-3 text-sm text-red-600 dark:text-red-400">{{ error }}</p>
          </div>
          <div class="modal-footer my-2">
            <div class="flex flex-row">
              <div class="basis-1/2 px-4">
                <button
                  type="button"
                  class="modal-default-button py-3 bg-gray-500 rounded w-full"
                  :disabled="saving"
                  @click="$emit('close')"
                >
                  تراجع
                </button>
              </div>
              <div class="basis-1/2 px-4">
                <button
                  type="button"
                  class="modal-default-button py-3 bg-indigo-600 rounded w-full"
                  :disabled="saving"
                  @click="save"
                >
                  {{ saving ? 'جاري الحفظ...' : 'تأكيد' }}
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
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 12px;
}

.modal-wrapper {
  width: 100%;
  max-width: 480px;
}

.modal-container {
  padding: 20px 24px;
  border-radius: 10px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.33);
}

.modal-body {
  margin: 16px 0;
}

.modal-default-button {
  color: #fff;
}
</style>

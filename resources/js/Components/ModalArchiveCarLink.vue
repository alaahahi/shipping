<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
  show: Boolean,
  formData: Object,
});

const emit = defineEmits(['close', 'a']);

const exchangeRate = ref('');

watch(() => props.show, (visible) => {
  if (visible) {
    exchangeRate.value = '';
  }
});

function submit() {
  const rate = Number(exchangeRate.value);
  if (!Number.isFinite(rate) || rate <= 0) return;
  emit('a', {
    id: props.formData?.id,
    exchangeRate: rate,
  });
}
</script>

<template>
  <Transition name="modal">
    <div v-if="show" class="modal-mask">
      <div class="modal-wrapper max-h-[80vh]">
        <div class="modal-container dark:bg-gray-900 overflow-auto max-h-[80vh]">
          <div class="modal-header">
            <slot name="header" />
            <h2 class="text-center py-5 dark:text-white">
              ربط السيارة مع ليست السيارة
              {{ formData?.car_type }} شانص{{ formData?.vin }} رقم{{ formData?.car_number }}
            </h2>
            <p class="text-center text-sm text-gray-600 dark:text-gray-300 px-4">
              سيتم تحويل مصاريف الدينار إلى دولار وإضافتها لحقل المصاريف في ليست السيارة (مرة واحدة).
            </p>
          </div>

          <div class="modal-body px-4">
            <label class="block mb-2 font-semibold dark:text-gray-200" for="exchange_rate">سعر الصرف</label>
            <input
              id="exchange_rate"
              v-model="exchangeRate"
              type="number"
              min="1"
              step="any"
              placeholder="مثال: 140000"
              class="w-full rounded border border-gray-300 p-2.5 dark:bg-gray-800 dark:border-gray-600 dark:text-white"
            />
          </div>

          <div class="modal-footer my-2">
            <div class="flex flex-row">
              <div class="basis-1/2 px-4">
                <button
                  type="button"
                  class="modal-default-button py-3 bg-gray-500 rounded"
                  @click="$emit('close')"
                >
                  تراجع
                </button>
              </div>
              <div class="basis-1/2 px-4">
                <button
                  type="button"
                  class="modal-default-button py-3 bg-indigo-600 rounded col-6"
                  :disabled="!exchangeRate || Number(exchangeRate) <= 0"
                  @click="submit"
                >
                  ربط
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
  width: 50%;
  min-width: 350px;
  margin: 0 auto;
  padding: 20px 30px 60px;
  background-color: #fff;
  border-radius: 10px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.33);
  transition: all 0.3s ease;
}

.modal-default-button {
  float: right;
  width: 100%;
  color: #fff;
}

.modal-default-button:disabled {
  opacity: 0.45;
  cursor: not-allowed;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

.modal-enter-from .modal-container,
.modal-leave-to .modal-container {
  transform: scale(1.1);
}
</style>

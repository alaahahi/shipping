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
        <div class="modal-container overflow-auto max-h-[80vh]">
          <div class="modal-header">
            <slot name="header" />
            <h2 class="link-modal-title">
              ربط السيارة مع ليست السيارة
              {{ formData?.car_type }} شانص{{ formData?.vin }} رقم{{ formData?.car_number }}
            </h2>
            <p class="link-modal-hint">
              سيتم تحويل مصاريف الدينار إلى دولار وإضافتها لحقل المصاريف في ليست السيارة (مرة واحدة).
            </p>
          </div>

          <div class="modal-body px-4">
            <label class="link-modal-label" for="exchange_rate">سعر الصرف</label>
            <input
              id="exchange_rate"
              v-model="exchangeRate"
              type="number"
              min="1"
              step="any"
              placeholder="مثال: 140000"
              class="link-modal-input"
            />
          </div>

          <div class="modal-footer my-2">
            <div class="flex flex-row">
              <div class="basis-1/2 px-4">
                <button
                  type="button"
                  class="modal-default-button modal-btn-cancel"
                  @click="$emit('close')"
                >
                  تراجع
                </button>
              </div>
              <div class="basis-1/2 px-4">
                <button
                  type="button"
                  class="modal-default-button modal-btn-submit"
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
  color: #111827;
  border-radius: 10px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.33);
  transition: all 0.3s ease;
}

:global(.dark) .modal-container {
  background-color: #111827;
  color: #f3f4f6;
  border: 1px solid #4b5563;
  box-shadow: 0 8px 30px rgba(0, 0, 0, 0.55);
}

.link-modal-title {
  text-align: center;
  padding: 1.25rem 1rem 0.5rem;
  font-size: 1.125rem;
  font-weight: 700;
  line-height: 1.5;
  color: #1f2937;
}

:global(.dark) .link-modal-title {
  color: #f9fafb;
}

.link-modal-hint {
  text-align: center;
  font-size: 0.875rem;
  line-height: 1.5;
  padding: 0 1rem 0.75rem;
  color: #374151;
}

:global(.dark) .link-modal-hint {
  color: #d1d5db;
}

.link-modal-label {
  display: block;
  margin-bottom: 0.5rem;
  font-size: 0.875rem;
  font-weight: 600;
  color: #374151;
}

:global(.dark) .link-modal-label {
  color: #e5e7eb;
}

.link-modal-input {
  display: block;
  width: 100%;
  border-radius: 0.375rem;
  border: 1px solid #d1d5db;
  background: #fff;
  color: #111827;
  padding: 0.625rem 0.75rem;
  font-size: 1rem;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}

.link-modal-input::placeholder {
  color: #9ca3af;
}

.link-modal-input:focus {
  outline: none;
  border-color: #818cf8;
  box-shadow: 0 0 0 3px rgba(129, 140, 248, 0.25);
}

:global(.dark) .link-modal-input {
  border-color: #4b5563;
  background: #030712;
  color: #f9fafb;
}

:global(.dark) .link-modal-input::placeholder {
  color: #9ca3af;
}

.modal-default-button {
  float: right;
  width: 100%;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 0.375rem;
  color: #fff;
  font-weight: 600;
  font-size: 0.9375rem;
  line-height: 1;
}

.modal-btn-cancel {
  background-color: #6b7280;
}

.modal-btn-cancel:hover {
  background-color: #4b5563;
}

.modal-btn-submit {
  background-color: #4f46e5;
}

.modal-btn-submit:hover:not(:disabled) {
  background-color: #4338ca;
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

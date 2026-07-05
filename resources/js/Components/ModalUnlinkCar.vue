<script setup>
import { computed, ref, watch } from 'vue';

const props = defineProps({
  show: Boolean,
  formData: Object,
});

const emit = defineEmits(['close', 'a']);

const exchangeRate = ref('');

function isSixDigitExchangeRate(value) {
  if (value === '' || value === null || value === undefined) return false;
  const rate = Number(value);
  if (!Number.isFinite(rate) || rate <= 0 || !Number.isInteger(rate)) return false;
  return String(Math.trunc(rate)).length === 6;
}

const isExchangeRateValid = computed(() => isSixDigitExchangeRate(exchangeRate.value));

watch(() => props.show, (visible) => {
  if (visible) {
    const preset = props.formData?.link_exchange_rate;
    exchangeRate.value = preset ? String(Math.trunc(Number(preset))) : '';
  }
});

function submit() {
  if (!isExchangeRateValid.value) return;
  emit('a', {
    id: props.formData?.id,
    exchangeRate: Math.trunc(Number(exchangeRate.value)),
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
            <h2 class="unlink-modal-title">
              إلغاء ربط السيارة
              {{ formData?.car_type }} شانص{{ formData?.vin }} رقم{{ formData?.car_number }}
            </h2>
            <p class="unlink-modal-hint">
              سعر الصرف المستخدم عند الربط غير متوفر في الملاحظات. أدخل سعر الصرف (6 أرقام) لإتمام إلغاء الربط والتراجع عن المصاريف المضافة.
            </p>
          </div>

          <div class="modal-body px-4">
            <label class="unlink-modal-label" for="unlink_exchange_rate">سعر الصرف</label>
            <input
              id="unlink_exchange_rate"
              v-model="exchangeRate"
              type="number"
              min="100000"
              max="999999"
              step="1"
              placeholder="مثال: 153000"
              class="unlink-modal-input"
            />
            <p
              v-if="exchangeRate && !isExchangeRateValid"
              class="unlink-modal-error"
            >
              يجب أن يكون 6 أرقام
            </p>
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
                  :disabled="!isExchangeRateValid"
                  @click="submit"
                >
                  إلغاء الربط
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

.unlink-modal-title {
  text-align: center;
  padding: 1.25rem 1rem 0.5rem;
  font-size: 1.125rem;
  font-weight: 700;
  line-height: 1.5;
  color: #1f2937;
}

:global(.dark) .unlink-modal-title {
  color: #f9fafb;
}

.unlink-modal-hint {
  text-align: center;
  font-size: 0.875rem;
  line-height: 1.5;
  padding: 0 1rem 0.75rem;
  color: #374151;
}

:global(.dark) .unlink-modal-hint {
  color: #d1d5db;
}

.unlink-modal-label {
  display: block;
  margin-bottom: 0.5rem;
  font-size: 0.875rem;
  font-weight: 600;
  color: #374151;
}

:global(.dark) .unlink-modal-label {
  color: #e5e7eb;
}

.unlink-modal-input {
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

.unlink-modal-input::placeholder {
  color: #9ca3af;
}

.unlink-modal-input:focus {
  outline: none;
  border-color: #f97316;
  box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.25);
}

:global(.dark) .unlink-modal-input {
  border-color: #4b5563;
  background: #030712;
  color: #f9fafb;
}

:global(.dark) .unlink-modal-input::placeholder {
  color: #9ca3af;
}

.unlink-modal-error {
  margin-top: 0.375rem;
  font-size: 0.8125rem;
  color: #dc2626;
}

:global(.dark) .unlink-modal-error {
  color: #f87171;
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
  background-color: #ea580c;
}

.modal-btn-submit:hover:not(:disabled) {
  background-color: #c2410c;
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

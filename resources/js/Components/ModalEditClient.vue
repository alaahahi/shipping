<script setup>
import { reactive, watch } from 'vue';

const props = defineProps({
  show: Boolean,
  formData: {
    type: Object,
    default: () => ({}),
  },
});

const emit = defineEmits(['close', 'confirm']);

const form = reactive({
  id: null,
  name: '',
  phone: '',
  has_internal_sales: false,
  show_in_dashboard: false,
});

watch(
  () => props.show,
  (open) => {
    if (open && props.formData) {
      form.id = props.formData.id ?? null;
      form.name = props.formData.name || '';
      form.phone = props.formData.phone || '';
      form.has_internal_sales = !!(props.formData.has_internal_sales);
      form.show_in_dashboard = !!(props.formData.show_in_dashboard);
    }
  }
);

function submit() {
  if (!form.name?.trim()) return;
  emit('confirm', {
    id: form.id,
    name: form.name.trim(),
    phone: form.phone?.trim() || '',
    has_internal_sales: !!form.has_internal_sales,
    show_in_dashboard: !!form.show_in_dashboard,
  });
}
</script>

<template>
  <Transition name="modal">
    <div v-if="show" class="modal-mask" @click.self="emit('close')">
      <div class="modal-wrapper">
        <div class="modal-container dark:bg-gray-900 max-h-[85vh] overflow-auto">
          <div class="flex items-start justify-between gap-3 mb-4">
            <div>
              <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                تعديل تاجر
              </h2>
              <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                تحديث بيانات التاجر وخيارات العرض والمبيعات الداخلية.
              </p>
            </div>
            <button
              type="button"
              class="rounded-lg p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800 dark:hover:text-gray-200"
              @click="emit('close')"
              aria-label="إغلاق"
            >
              ✕
            </button>
          </div>

          <div class="space-y-4">
            <div>
              <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200" for="edit-client-name">
                الاسم
              </label>
              <input
                id="edit-client-name"
                v-model="form.name"
                type="text"
                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
              />
            </div>

            <div>
              <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200" for="edit-client-phone">
                رقم الهاتف
              </label>
              <input
                id="edit-client-phone"
                v-model="form.phone"
                type="text"
                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
              />
            </div>

            <label
              class="flex cursor-pointer items-center justify-between rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 dark:border-gray-700 dark:bg-gray-800/60"
            >
              <div>
                <div class="text-sm font-semibold text-gray-800 dark:text-gray-100">
                  تفعيل المبيعات الداخلية
                </div>
                <div class="text-xs text-gray-500 dark:text-gray-400">
                  يسمح بإدارة مبيعات داخلية لهذا التاجر
                </div>
              </div>
              <div class="relative">
                <input v-model="form.has_internal_sales" type="checkbox" class="sr-only peer" />
                <div
                  class="h-6 w-11 rounded-full bg-gray-300 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-gray-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-blue-600 peer-checked:after:translate-x-full peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-blue-300 dark:bg-gray-600"
                ></div>
              </div>
            </label>

            <label
              class="flex cursor-pointer items-center justify-between rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 dark:border-gray-700 dark:bg-gray-800/60"
            >
              <div>
                <div class="text-sm font-semibold text-gray-800 dark:text-gray-100">
                  عرض في لوحة التحكم
                </div>
                <div class="text-xs text-gray-500 dark:text-gray-400">
                  يظهر هذا الحساب ضمن المحافظ المميزة في المحاسبة
                </div>
              </div>
              <div class="relative">
                <input v-model="form.show_in_dashboard" type="checkbox" class="sr-only peer" />
                <div
                  class="h-6 w-11 rounded-full bg-gray-300 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-gray-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-orange-500 peer-checked:after:translate-x-full peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-orange-300 dark:bg-gray-600"
                ></div>
              </div>
            </label>
          </div>

          <div class="mt-6 grid grid-cols-2 gap-3">
            <button
              type="button"
              class="rounded-lg bg-gray-500 px-4 py-3 font-semibold text-white hover:bg-gray-600"
              @click="emit('close')"
            >
              إلغاء
            </button>
            <button
              type="button"
              class="rounded-lg bg-rose-600 px-4 py-3 font-semibold text-white hover:bg-rose-700 disabled:cursor-not-allowed disabled:opacity-50"
              :disabled="!form.name?.trim()"
              @click="submit"
            >
              حفظ التعديلات
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
  transition: opacity 0.3s ease;
}

.modal-wrapper {
  display: table-cell;
  vertical-align: middle;
}

.modal-container {
  width: min(520px, 92vw);
  margin: 0 auto;
  padding: 24px;
  background-color: #fff;
  border-radius: 14px;
  box-shadow: 0 12px 40px rgba(0, 0, 0, 0.25);
  transition: all 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

.modal-enter-from .modal-container,
.modal-leave-to .modal-container {
  transform: scale(1.05);
}

.sr-only {
  position: absolute;
  width: 1px;
  height: 1px;
  padding: 0;
  margin: -1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  white-space: nowrap;
  border-width: 0;
}
</style>

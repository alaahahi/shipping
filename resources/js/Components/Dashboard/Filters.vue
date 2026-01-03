<script setup>
import { defineProps, defineEmits } from 'vue';

const props = defineProps({
  selectedYear: {
    type: Number,
    required: true,
  },
  selectedMonth: {
    type: Number,
    default: null,
  },
  years: {
    type: Array,
    required: true,
  },
});

const emit = defineEmits(['update:selectedYear', 'update:selectedMonth']);

const months = [
  { value: null, label: 'جميع الأشهر' },
  { value: 1, label: 'يناير' },
  { value: 2, label: 'فبراير' },
  { value: 3, label: 'مارس' },
  { value: 4, label: 'أبريل' },
  { value: 5, label: 'مايو' },
  { value: 6, label: 'يونيو' },
  { value: 7, label: 'يوليو' },
  { value: 8, label: 'أغسطس' },
  { value: 9, label: 'سبتمبر' },
  { value: 10, label: 'أكتوبر' },
  { value: 11, label: 'نوفمبر' },
  { value: 12, label: 'ديسمبر' },
];

const updateYear = (event) => {
  emit('update:selectedYear', parseInt(event.target.value));
};

const updateMonth = (event) => {
  const value = event.target.value === '' ? null : parseInt(event.target.value);
  emit('update:selectedMonth', value);
};
</script>

<template>
  <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-4">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
          السنة
        </label>
        <select
          :value="selectedYear"
          @change="updateYear"
          class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
        >
          <option v-for="y in years" :key="y" :value="y">
            {{ y }}
          </option>
        </select>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
          الشهر
        </label>
        <select
          :value="selectedMonth"
          @change="updateMonth"
          class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
        >
          <option v-for="m in months" :key="m.value" :value="m.value">
            {{ m.label }}
          </option>
        </select>
      </div>
    </div>
  </div>
</template>

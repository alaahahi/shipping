<script setup>
import { defineProps, defineEmits } from 'vue';

const props = defineProps({
  selectedYear: {
    type: Number,
    default: null,
  },
  selectedYears: {
    type: Array,
    default: () => [],
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

const emit = defineEmits(['update:selectedYear', 'update:selectedYears', 'update:selectedMonth']);

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

const updateYears = (year, checked) => {
  const currentYears = [...props.selectedYears];
  if (checked) {
    if (!currentYears.includes(year)) {
      currentYears.push(year);
    }
  } else {
    const index = currentYears.indexOf(year);
    if (index > -1) {
      currentYears.splice(index, 1);
    }
  }
  emit('update:selectedYears', currentYears);
};

const selectAllYears = () => {
  emit('update:selectedYears', [...props.years]);
};

const deselectAllYears = () => {
  emit('update:selectedYears', []);
};

const updateMonth = (event) => {
  const value = event.target.value === '' ? null : parseInt(event.target.value);
  emit('update:selectedMonth', value);
};
</script>

<template>
  <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-4">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <!-- فلتر سنة واحدة -->
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
          السنة (واحدة)
        </label>
        <select
          :value="selectedYear"
          @change="updateYear"
          class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
        >
          <option :value="null">جميع السنوات</option>
          <option v-for="y in years" :key="y" :value="y">
            {{ y }}
          </option>
        </select>
      </div>

      <!-- فلتر متعدد السنوات -->
      <div>
        <div class="flex justify-between items-center mb-2">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
            السنوات (متعددة)
          </label>
          <div class="flex gap-2">
            <button
              @click="selectAllYears"
              class="text-xs text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
            >
              تحديد الكل
            </button>
            <button
              @click="deselectAllYears"
              class="text-xs text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300"
            >
              إلغاء الكل
            </button>
          </div>
        </div>
        <div class="border border-gray-300 dark:border-gray-600 rounded-md p-2 max-h-32 overflow-y-auto dark:bg-gray-700">
          <div class="flex flex-wrap gap-2">
            <label
              v-for="y in years"
              :key="y"
              class="inline-flex items-center px-2 py-1 rounded cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600"
            >
              <input
                type="checkbox"
                :checked="selectedYears.includes(y)"
                @change="updateYears(y, $event.target.checked)"
                class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700"
              />
              <span class="mr-2 text-sm text-gray-700 dark:text-gray-300">{{ y }}</span>
            </label>
          </div>
        </div>
      </div>

      <!-- فلتر الشهر -->
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

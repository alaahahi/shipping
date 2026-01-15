<script setup>
import { formatNumber } from '@/utils/numberFormat';

const props = defineProps({
  year: {
    type: Number,
    required: true,
  },
  totalIncome: {
    type: Number,
    default: 0,
  },
  totalExpenses: {
    type: Number,
    default: 0,
  },
  totalDiscounts: {
    type: Number,
    default: 0,
  },
  netYearProfit: {
    type: Number,
    default: 0,
  },
  carriedProfit: {
    type: Number,
    default: 0,
  },
  isClosed: {
    type: Boolean,
    default: false,
  },
});
</script>

<template>
  <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
        خلاصة إغلاق السنة {{ year }}
      </h3>
      <span
        v-if="isClosed"
        class="px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200"
      >
        مغلقة
      </span>
      <span
        v-else
        class="px-3 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200"
      >
        مفتوحة
      </span>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <!-- إجمالي الدخل -->
      <div>
        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
          إجمالي الدخل
        </dt>
        <dd class="mt-1 text-2xl font-semibold text-green-600 dark:text-green-400">
          {{ formatNumber(totalIncome) }}
        </dd>
      </div>
      
      <!-- إجمالي المصاريف -->
      <div>
        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
          إجمالي المصاريف
        </dt>
        <dd class="mt-1 text-2xl font-semibold text-red-600 dark:text-red-400">
          {{ formatNumber(totalExpenses) }}
        </dd>
      </div>
      
      <!-- إجمالي الخصومات -->
      <div>
        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
          إجمالي الخصومات
        </dt>
        <dd class="mt-1 text-2xl font-semibold text-orange-600 dark:text-orange-400">
          {{ formatNumber(totalDiscounts) }}
        </dd>
      </div>
      
      <!-- صافي ربح السنة -->
      <div>
        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
          صافي ربح السنة
        </dt>
        <dd class="mt-1 text-2xl font-semibold"
            :class="netYearProfit >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'">
          {{ formatNumber(netYearProfit) }}
        </dd>
      </div>
      
      <!-- الربح المحمول -->
      <div>
        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
          الربح المحمول
        </dt>
        <dd class="mt-1 text-2xl font-semibold text-blue-600 dark:text-blue-400">
          {{ formatNumber(carriedProfit) }}
        </dd>
      </div>
      
      <!-- الإجمالي النهائي -->
      <div>
        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
          الإجمالي النهائي
        </dt>
        <dd class="mt-1 text-2xl font-semibold text-gray-900 dark:text-gray-100">
          {{ formatNumber(netYearProfit + carriedProfit) }}
        </dd>
      </div>
    </div>
  </div>
</template>


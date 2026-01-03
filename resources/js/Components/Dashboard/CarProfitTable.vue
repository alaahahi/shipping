<script setup>
import { formatNumber } from '@/utils/numberFormat';

const props = defineProps({
  cars: {
    type: Array,
    default: () => [],
  },
  maxProfit: {
    type: Number,
    default: 0,
  },
  minProfit: {
    type: Number,
    default: 0,
  },
  avgProfit: {
    type: Number,
    default: 0,
  },
});
</script>

<template>
  <div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
      <thead class="bg-gray-50 dark:bg-gray-900">
        <tr>
          <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
            #
          </th>
          <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
            رقم السيارة
          </th>
          <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
            VIN
          </th>
          <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
            صافي الربح
          </th>
        </tr>
      </thead>
      <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
        <tr v-if="cars.length === 0">
          <td colspan="4" class="px-4 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
            لا توجد بيانات
          </td>
        </tr>
        <tr
          v-for="(car, index) in cars"
          :key="car.id || index"
          class="hover:bg-gray-50 dark:hover:bg-gray-700"
        >
          <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
            {{ index + 1 }}
          </td>
          <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
            {{ car.car_number || '-' }}
          </td>
          <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
            {{ car.vin || '-' }}
          </td>
          <td class="px-4 py-3 whitespace-nowrap text-sm font-semibold"
              :class="(car.net_profit || car.profit || 0) >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'">
            {{ formatNumber(car.net_profit || car.profit || 0) }}
          </td>
        </tr>
      </tbody>
    </table>
    
    <!-- Summary -->
    <div class="mt-4 grid grid-cols-3 gap-4 text-sm">
      <div class="text-center">
        <p class="text-gray-500 dark:text-gray-400">أعلى ربح</p>
        <p class="font-semibold text-green-600 dark:text-green-400">{{ formatNumber(maxProfit) }}</p>
      </div>
      <div class="text-center">
        <p class="text-gray-500 dark:text-gray-400">أقل ربح</p>
        <p class="font-semibold text-red-600 dark:text-red-400">{{ formatNumber(minProfit) }}</p>
      </div>
      <div class="text-center">
        <p class="text-gray-500 dark:text-gray-400">متوسط الربح</p>
        <p class="font-semibold text-gray-900 dark:text-gray-100">{{ formatNumber(avgProfit) }}</p>
      </div>
    </div>
  </div>
</template>

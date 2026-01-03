<script setup>
import { formatNumber } from '@/utils/numberFormat';

const props = defineProps({
  cars: {
    type: Array,
    default: () => [],
  },
  totalDifference: {
    type: Number,
    default: 0,
  },
});
</script>

<template>
  <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6">
      <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">
        السيارات ذات الفرق في الجمرك
      </h3>
      
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
          <thead class="bg-gray-50 dark:bg-gray-900">
            <tr>
              <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                رقم السيارة
              </th>
              <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                VIN
              </th>
              <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                الجمرك الشراء (دينار)
              </th>
              <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                الجمرك البيع (دينار)
              </th>
              <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                الفرق
              </th>
            </tr>
          </thead>
          <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
            <tr v-if="cars.length === 0">
              <td colspan="5" class="px-4 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                لا توجد سيارات بها فرق في الجمرك
              </td>
            </tr>
            <tr
              v-for="(car, index) in cars"
              :key="index"
              class="hover:bg-gray-50 dark:hover:bg-gray-700"
            >
              <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                {{ car.car_number || '-' }}
              </td>
              <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100 font-mono">
                {{ car.vin || '-' }}
              </td>
              <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                {{ formatNumber(car.dinar || 0) }}
              </td>
              <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                {{ formatNumber(car.dinar_s || 0) }}
              </td>
              <td class="px-4 py-3 whitespace-nowrap text-sm font-semibold"
                  :class="(car.difference || 0) >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'">
                {{ formatNumber(car.difference || 0) }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      
      <!-- Summary -->
      <div v-if="cars.length > 0" class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
        <div class="flex justify-between items-center">
          <span class="text-sm text-gray-500 dark:text-gray-400">
            عدد السيارات:
          </span>
          <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">
            {{ cars.length }}
          </span>
        </div>
        <div class="flex justify-between items-center mt-2">
          <span class="text-sm text-gray-500 dark:text-gray-400">
            إجمالي الفرق:
          </span>
          <span class="text-sm font-semibold"
                :class="totalDifference >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'">
            {{ formatNumber(totalDifference) }}
          </span>
        </div>
      </div>
    </div>
  </div>
</template>


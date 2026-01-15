<script setup>
import { formatNumber } from '@/utils/numberFormat';

const props = defineProps({
  traders: {
    type: Array,
    default: () => [],
  },
});
</script>

<template>
  <div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
      <thead class="bg-gray-50 dark:bg-gray-900">
        <tr>
          <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
            اسم التاجر
          </th>
          <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
            الهاتف
          </th>
          <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
            عدد السيارات
          </th>
          <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
            مجموع المشتريات
          </th>
          <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
            مجموع المبيعات
          </th>
          <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
            مجموع الربح
          </th>
        </tr>
      </thead>
      <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
        <tr v-if="traders.length === 0">
          <td colspan="6" class="px-4 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
            لا توجد بيانات
          </td>
        </tr>
        <tr
          v-for="(trader, index) in traders"
          :key="index"
          class="hover:bg-gray-50 dark:hover:bg-gray-700"
        >
          <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
            {{ trader.trader_name || '-' }}
          </td>
          <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
            {{ trader.trader_phone || '-' }}
          </td>
          <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
            {{ trader.cars_count || 0 }}
          </td>
          <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
            {{ formatNumber(trader.total_purchases || 0) }}
          </td>
          <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
            {{ formatNumber(trader.total_sales || 0) }}
          </td>
          <td class="px-4 py-3 whitespace-nowrap text-sm font-semibold"
              :class="(trader.total_profit || 0) >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'">
            {{ formatNumber(trader.total_profit || 0) }}
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>


<script setup>
import { formatNumber } from '@/utils/numberFormat';

const props = defineProps({
  discounts: {
    type: Array,
    default: () => [],
  },
  totalDiscounts: {
    type: Number,
    default: 0,
  },
  maxDiscount: {
    type: Number,
    default: 0,
  },
  minDiscount: {
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
            رقم السيارة
          </th>
          <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
            VIN
          </th>
          <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
            الخصم
          </th>
          <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
            الشراء
          </th>
          <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
            البيع
          </th>
          <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
            الربح بعد الخصم
          </th>
        </tr>
      </thead>
      <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
        <tr v-if="discounts.length === 0">
          <td colspan="6" class="px-4 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
            لا توجد بيانات
          </td>
        </tr>
        <tr
          v-for="(record, index) in discounts.slice(0, 10)"
          :key="index"
          class="hover:bg-gray-50 dark:hover:bg-gray-700"
        >
          <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
            {{ record.car_number || '-' }}
          </td>
          <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
            {{ record.vin || '-' }}
          </td>
          <td class="px-4 py-3 whitespace-nowrap text-sm font-semibold text-red-600 dark:text-red-400">
            {{ formatNumber(record.discount || 0) }}
          </td>
          <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
            {{ formatNumber(record.total || 0) }}
          </td>
          <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
            {{ formatNumber(record.total_s || 0) }}
          </td>
          <td class="px-4 py-3 whitespace-nowrap text-sm font-semibold"
              :class="(record.profit_after_discount || record.profit || 0) >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'">
            {{ formatNumber(record.profit_after_discount || record.profit || 0) }}
          </td>
        </tr>
      </tbody>
    </table>
    
    <!-- Summary -->
    <div class="mt-4 grid grid-cols-3 gap-4 text-sm">
      <div class="text-center">
        <p class="text-gray-500 dark:text-gray-400">المجموع</p>
        <p class="font-semibold text-red-600 dark:text-red-400">{{ formatNumber(totalDiscounts) }}</p>
      </div>
      <div class="text-center">
        <p class="text-gray-500 dark:text-gray-400">أعلى خصم</p>
        <p class="font-semibold text-red-600 dark:text-red-400">{{ formatNumber(maxDiscount) }}</p>
      </div>
      <div class="text-center">
        <p class="text-gray-500 dark:text-gray-400">أقل خصم</p>
        <p class="font-semibold text-red-600 dark:text-red-400">{{ formatNumber(minDiscount) }}</p>
      </div>
    </div>
  </div>
</template>

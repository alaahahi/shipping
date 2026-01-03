<script setup>
import { formatNumber } from '@/utils/numberFormat';

const props = defineProps({
  carsCount: {
    type: Number,
    default: 0,
  },
  totalCustoms: {
    type: Number,
    default: 0,
  },
  exchangeProfit: {
    type: Number,
    default: 0,
  },
  netProfit: {
    type: Number,
    default: 0,
  },
  netTransfers: {
    type: Number,
    default: 0,
  },
  cashBalance: {
    type: Number,
    default: 0,
  },
});
</script>

<template>
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <!-- عدد السيارات -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
      <div class="flex items-center">
        <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
          <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
        </div>
        <div class="ml-5 w-0 flex-1">
          <dl>
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
              عدد السيارات
            </dt>
            <dd class="text-lg font-semibold text-gray-900 dark:text-gray-100">
              {{ carsCount }}
            </dd>
          </dl>
        </div>
      </div>
    </div>

    <!-- مجموع الجمرك -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
      <div class="flex items-center">
        <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
          <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
        <div class="ml-5 w-0 flex-1">
          <dl>
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
              مجموع الجمرك
            </dt>
            <dd class="text-lg font-semibold text-gray-900 dark:text-gray-100">
              {{ formatNumber(totalCustoms) }}
            </dd>
          </dl>
        </div>
      </div>
    </div>

    <!-- الفائدة من فرق سعر الصرف -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
      <div class="flex items-center">
        <div class="flex-shrink-0 bg-yellow-500 rounded-md p-3">
          <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
          </svg>
        </div>
        <div class="ml-5 w-0 flex-1">
          <dl>
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
              الفائدة من فرق سعر الصرف
            </dt>
            <dd class="text-lg font-semibold text-gray-900 dark:text-gray-100">
              {{ formatNumber(exchangeProfit) }}
            </dd>
          </dl>
        </div>
      </div>
    </div>

    <!-- صافي الربح -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
      <div class="flex items-center">
        <div class="flex-shrink-0 bg-purple-500 rounded-md p-3">
          <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
          </svg>
        </div>
        <div class="ml-5 w-0 flex-1">
          <dl>
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
              صافي الربح
            </dt>
            <dd class="text-lg font-semibold text-gray-900 dark:text-gray-100">
              {{ formatNumber(netProfit) }}
            </dd>
          </dl>
        </div>
      </div>
    </div>

    <!-- صافي الحولات -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
      <div class="flex items-center">
        <div class="flex-shrink-0 bg-indigo-500 rounded-md p-3">
          <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
          </svg>
        </div>
        <div class="ml-5 w-0 flex-1">
          <dl>
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
              صافي الحولات
            </dt>
            <dd class="text-lg font-semibold text-gray-900 dark:text-gray-100">
              {{ formatNumber(netTransfers) }}
            </dd>
          </dl>
        </div>
      </div>
    </div>

    <!-- الرصيد النقدي -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
      <div class="flex items-center">
        <div class="flex-shrink-0 bg-red-500 rounded-md p-3">
          <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
          </svg>
        </div>
        <div class="ml-5 w-0 flex-1">
          <dl>
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
              الرصيد النقدي
            </dt>
            <dd class="text-lg font-semibold text-gray-900 dark:text-gray-100">
              {{ formatNumber(cashBalance) }}
            </dd>
          </dl>
        </div>
      </div>
    </div>
  </div>
</template>

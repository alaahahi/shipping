<script setup>
import { formatNumber } from '@/utils/numberFormat';

const props = defineProps({
  grossTransfers: {
    type: Number,
    default: 0,
  },
  transferFees: {
    type: Number,
    default: 0,
  },
  netTransfers: {
    type: Number,
    default: 0,
  },
  erbilTransfers: {
    type: Number,
    default: 0,
  },
});
</script>

<template>
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
    <!-- إجمالي الحولات -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
      <div class="flex items-center">
        <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
          <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
          </svg>
        </div>
        <div class="ml-5 w-0 flex-1">
          <dl>
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
              إجمالي الحولات
            </dt>
            <dd class="text-lg font-semibold text-gray-900 dark:text-gray-100">
              {{ formatNumber(grossTransfers) }}
            </dd>
          </dl>
        </div>
      </div>
    </div>

    <!-- رسوم الحولات -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
      <div class="flex items-center">
        <div class="flex-shrink-0 bg-yellow-500 rounded-md p-3">
          <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
        <div class="ml-5 w-0 flex-1">
          <dl>
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
              رسوم الحولات
            </dt>
            <dd class="text-lg font-semibold text-gray-900 dark:text-gray-100">
              {{ formatNumber(transferFees) }}
            </dd>
          </dl>
        </div>
      </div>
    </div>

    <!-- صافي الحولات -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
      <div class="flex items-center">
        <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
          <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
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

    <!-- حولات أربيل -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
      <div class="flex items-center">
        <div class="flex-shrink-0 bg-purple-500 rounded-md p-3">
          <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
          </svg>
        </div>
        <div class="ml-5 w-0 flex-1">
          <dl>
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
              حولات أربيل
            </dt>
            <dd class="text-lg font-semibold text-gray-900 dark:text-gray-100">
              {{ formatNumber(erbilTransfers) }}
            </dd>
          </dl>
        </div>
      </div>
    </div>
  </div>
</template>


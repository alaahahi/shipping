<script setup>
import { formatNumber } from '@/utils/numberFormat';

const props = defineProps({
  cashIn: {
    type: Number,
    default: 0,
  },
  cashOut: {
    type: Number,
    default: 0,
  },
  netCash: {
    type: Number,
    default: 0,
  },
});
</script>

<template>
  <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <!-- النقد الوارد -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
      <div class="flex items-center">
        <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
          <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
          </svg>
        </div>
        <div class="ml-5 w-0 flex-1">
          <dl>
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
              النقد الوارد
            </dt>
            <dd class="text-lg font-semibold text-green-600 dark:text-green-400">
              {{ formatNumber(cashIn) }}
            </dd>
          </dl>
        </div>
      </div>
    </div>

    <!-- النقد الصادر -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
      <div class="flex items-center">
        <div class="flex-shrink-0 bg-red-500 rounded-md p-3">
          <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" />
          </svg>
        </div>
        <div class="ml-5 w-0 flex-1">
          <dl>
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
              النقد الصادر
            </dt>
            <dd class="text-lg font-semibold text-red-600 dark:text-red-400">
              {{ formatNumber(cashOut) }}
            </dd>
          </dl>
        </div>
      </div>
    </div>

    <!-- صافي النقد -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
      <div class="flex items-center">
        <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
          <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
          </svg>
        </div>
        <div class="ml-5 w-0 flex-1">
          <dl>
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
              صافي النقد
            </dt>
            <dd class="text-lg font-semibold"
                :class="netCash >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'">
              {{ formatNumber(netCash) }}
            </dd>
          </dl>
        </div>
      </div>
    </div>
  </div>
</template>


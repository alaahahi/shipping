<script setup>
import { computed } from 'vue';
import { formatNumber } from '@/utils/numberFormat';

const props = defineProps({
  labels: {
    type: Array,
    default: () => [],
  },
  monthlyProfit: {
    type: Array,
    default: () => [],
  },
  yearlyProfit: {
    type: Number,
    default: 0,
  },
});

const chartData = computed(() => {
  if (!props.monthlyProfit || props.monthlyProfit.length === 0) return { max: 0, points: [] };
  
  const profits = props.monthlyProfit;
  const max = Math.max(...profits, 0);
  const min = Math.min(...profits, 0);
  const range = max - min || 1;
  
  const points = profits.map((profit, index) => {
    const normalizedValue = range > 0 ? ((profit - min) / range) * 100 : 50;
    return {
      x: (index / (profits.length - 1 || 1)) * 100,
      y: 100 - normalizedValue,
      profit: profit,
      label: props.labels[index] || (index + 1),
    };
  });
  
  return { max, min, range, points };
});
</script>

<template>
  <div class="w-full">
    <svg viewBox="0 0 400 200" class="w-full h-64">
      <!-- Grid lines -->
      <defs>
        <pattern id="grid-profit" width="20" height="20" patternUnits="userSpaceOnUse">
          <path d="M 20 0 L 0 0 0 20" fill="none" stroke="#e5e7eb" stroke-width="0.5" class="dark:stroke-gray-700"/>
        </pattern>
      </defs>
      <rect width="100%" height="100%" fill="url(#grid-profit)" />
      
      <!-- Y-axis labels -->
      <text
        v-for="(label, index) in 5"
        :key="label"
        :x="8"
        :y="(index * 25) + 15"
        class="text-sm font-semibold fill-gray-800 dark:fill-gray-200"
        text-anchor="start"
        style="font-size: 11px; font-weight: 600;"
      >
        {{ formatNumber(chartData.max - (chartData.max / 4) * index) }}
      </text>
      
      <!-- X-axis labels -->
      <text
        v-for="(point, index) in chartData.points"
        :key="index"
        :x="(point.x / 100) * 380 + 20"
        :y="192"
        class="text-sm font-semibold fill-gray-800 dark:fill-gray-200"
        text-anchor="middle"
        style="font-size: 10px; font-weight: 600;"
      >
        {{ point.label }}
      </text>
      
      <!-- Line -->
      <polyline
        v-if="chartData.points.length > 0"
        :points="chartData.points.map(p => `${(p.x / 100) * 380 + 20},${(p.y / 100) * 180 + 10}`).join(' ')"
        fill="none"
        stroke="#3b82f6"
        stroke-width="2"
        class="dark:stroke-blue-400"
      />
      
      <!-- Points -->
      <g v-for="(point, index) in chartData.points" :key="index">
        <circle
          :cx="(point.x / 100) * 380 + 20"
          :cy="(point.y / 100) * 180 + 10"
          r="5"
          fill="#3b82f6"
          stroke="#ffffff"
          stroke-width="2"
          class="dark:fill-blue-400 dark:stroke-gray-800"
        >
          <title>{{ point.label }}: {{ formatNumber(point.profit) }}</title>
        </circle>
        <!-- Value label above point -->
        <text
          :x="(point.x / 100) * 380 + 20"
          :y="(point.y / 100) * 180 - 5"
          class="text-xs font-bold fill-gray-900 dark:fill-gray-100"
          text-anchor="middle"
          style="font-size: 9px; font-weight: 700; pointer-events: none;"
        >
          {{ formatNumber(point.profit) }}
        </text>
      </g>
    </svg>
    
    <!-- Legend -->
    <div class="mt-4 text-center">
      <p class="text-base font-bold text-gray-800 dark:text-gray-200">
        إجمالي ربح السنة: <span class="text-blue-600 dark:text-blue-400">{{ formatNumber(yearlyProfit) }}</span>
      </p>
      <div class="mt-2 flex justify-center gap-4 text-sm">
        <span class="text-gray-700 dark:text-gray-300">
          أعلى ربح شهري: <span class="font-semibold text-green-600 dark:text-green-400">{{ formatNumber(chartData.max) }}</span>
        </span>
        <span class="text-gray-700 dark:text-gray-300">
          أقل ربح شهري: <span class="font-semibold text-red-600 dark:text-red-400">{{ formatNumber(chartData.min) }}</span>
        </span>
      </div>
    </div>
  </div>
</template>

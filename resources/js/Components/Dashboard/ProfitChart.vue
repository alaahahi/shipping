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
        :x="5"
        :y="(index * 25) + 10"
        class="text-xs fill-gray-600 dark:fill-gray-400"
        text-anchor="start"
      >
        {{ formatNumber(chartData.max - (chartData.max / 4) * index) }}
      </text>
      
      <!-- X-axis labels -->
      <text
        v-for="(point, index) in chartData.points.filter((_, i) => i % 2 === 0)"
        :key="index"
        :x="(point.x / 100) * 380 + 20"
        :y="195"
        class="text-xs fill-gray-600 dark:fill-gray-400"
        text-anchor="middle"
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
      <circle
        v-for="(point, index) in chartData.points"
        :key="index"
        :cx="(point.x / 100) * 380 + 20"
        :cy="(point.y / 100) * 180 + 10"
        r="4"
        fill="#3b82f6"
        class="dark:fill-blue-400"
      >
        <title>{{ point.label }}: {{ formatNumber(point.profit) }}</title>
      </circle>
    </svg>
    
    <!-- Legend -->
    <div class="mt-4 text-center text-sm text-gray-600 dark:text-gray-400">
      <p>إجمالي ربح السنة: {{ formatNumber(yearlyProfit) }}</p>
      <p class="mt-1">أعلى ربح شهري: {{ formatNumber(chartData.max) }}</p>
      <p class="mt-1">أقل ربح شهري: {{ formatNumber(chartData.min) }}</p>
    </div>
  </div>
</template>

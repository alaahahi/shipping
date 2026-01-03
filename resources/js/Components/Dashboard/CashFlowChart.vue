<script setup>
import { computed } from 'vue';
import { formatNumber } from '@/utils/numberFormat';

const props = defineProps({
  labels: {
    type: Array,
    default: () => [],
  },
  cashInData: {
    type: Array,
    default: () => [],
  },
  cashOutData: {
    type: Array,
    default: () => [],
  },
});

const chartData = computed(() => {
  if (!props.cashInData || props.cashInData.length === 0) return { max: 0, cashInPoints: [], cashOutPoints: [] };
  
  const allValues = [...props.cashInData, ...props.cashOutData];
  const max = Math.max(...allValues, 0);
  const min = Math.min(...allValues, 0);
  const range = max - min || 1;
  
  const cashInPoints = props.cashInData.map((value, index) => {
    const normalizedValue = range > 0 ? ((value - min) / range) * 100 : 50;
    return {
      x: (index / (props.cashInData.length - 1 || 1)) * 100,
      y: 100 - normalizedValue,
      value: value,
      label: props.labels[index] || (index + 1),
    };
  });
  
  const cashOutPoints = props.cashOutData.map((value, index) => {
    const normalizedValue = range > 0 ? ((value - min) / range) * 100 : 50;
    return {
      x: (index / (props.cashOutData.length - 1 || 1)) * 100,
      y: 100 - normalizedValue,
      value: value,
      label: props.labels[index] || (index + 1),
    };
  });
  
  return { max, min, range, cashInPoints, cashOutPoints };
});
</script>

<template>
  <div class="w-full">
    <svg viewBox="0 0 400 200" class="w-full h-64">
      <!-- Grid lines -->
      <defs>
        <pattern id="grid-cash" width="20" height="20" patternUnits="userSpaceOnUse">
          <path d="M 20 0 L 0 0 0 20" fill="none" stroke="#e5e7eb" stroke-width="0.5" class="dark:stroke-gray-700"/>
        </pattern>
      </defs>
      <rect width="100%" height="100%" fill="url(#grid-cash)" />
      
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
        v-for="(point, index) in chartData.cashInPoints.filter((_, i) => i % 2 === 0)"
        :key="index"
        :x="(point.x / 100) * 380 + 20"
        :y="195"
        class="text-xs fill-gray-600 dark:fill-gray-400"
        text-anchor="middle"
      >
        {{ point.label }}
      </text>
      
      <!-- Cash In Line -->
      <polyline
        v-if="chartData.cashInPoints.length > 0"
        :points="chartData.cashInPoints.map(p => `${(p.x / 100) * 380 + 20},${(p.y / 100) * 180 + 10}`).join(' ')"
        fill="none"
        stroke="#10b981"
        stroke-width="2"
        class="dark:stroke-green-400"
      />
      
      <!-- Cash Out Line -->
      <polyline
        v-if="chartData.cashOutPoints.length > 0"
        :points="chartData.cashOutPoints.map(p => `${(p.x / 100) * 380 + 20},${(p.y / 100) * 180 + 10}`).join(' ')"
        fill="none"
        stroke="#ef4444"
        stroke-width="2"
        class="dark:stroke-red-400"
      />
      
      <!-- Cash In Points -->
      <circle
        v-for="(point, index) in chartData.cashInPoints"
        :key="'in-' + index"
        :cx="(point.x / 100) * 380 + 20"
        :cy="(point.y / 100) * 180 + 10"
        r="4"
        fill="#10b981"
        class="dark:fill-green-400"
      >
        <title>وارد - {{ point.label }}: {{ formatNumber(point.value) }}</title>
      </circle>
      
      <!-- Cash Out Points -->
      <circle
        v-for="(point, index) in chartData.cashOutPoints"
        :key="'out-' + index"
        :cx="(point.x / 100) * 380 + 20"
        :cy="(point.y / 100) * 180 + 10"
        r="4"
        fill="#ef4444"
        class="dark:fill-red-400"
      >
        <title>صادر - {{ point.label }}: {{ formatNumber(point.value) }}</title>
      </circle>
    </svg>
    
    <!-- Legend -->
    <div class="mt-4 flex justify-center gap-6 text-sm">
      <div class="flex items-center gap-2">
        <div class="w-4 h-4 bg-green-500 rounded"></div>
        <span class="text-gray-600 dark:text-gray-400">النقد الوارد</span>
      </div>
      <div class="flex items-center gap-2">
        <div class="w-4 h-4 bg-red-500 rounded"></div>
        <span class="text-gray-600 dark:text-gray-400">النقد الصادر</span>
      </div>
    </div>
  </div>
</template>


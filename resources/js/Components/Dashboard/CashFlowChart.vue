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
        v-for="(point, index) in chartData.cashInPoints"
        :key="index"
        :x="(point.x / 100) * 380 + 20"
        :y="192"
        class="text-sm font-semibold fill-gray-800 dark:fill-gray-200"
        text-anchor="middle"
        style="font-size: 10px; font-weight: 600;"
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
      <g v-for="(point, index) in chartData.cashInPoints" :key="'in-' + index">
        <circle
          :cx="(point.x / 100) * 380 + 20"
          :cy="(point.y / 100) * 180 + 10"
          r="5"
          fill="#10b981"
          stroke="#ffffff"
          stroke-width="2"
          class="dark:fill-green-400 dark:stroke-gray-800"
        >
          <title>وارد - {{ point.label }}: {{ formatNumber(point.value) }}</title>
        </circle>
        <!-- Value label above point -->
        <text
          :x="(point.x / 100) * 380 + 20"
          :y="(point.y / 100) * 180 - 5"
          class="text-xs font-bold fill-gray-900 dark:fill-gray-100"
          text-anchor="middle"
          style="font-size: 9px; font-weight: 700; pointer-events: none;"
        >
          {{ formatNumber(point.value) }}
        </text>
      </g>
      
      <!-- Cash Out Points -->
      <g v-for="(point, index) in chartData.cashOutPoints" :key="'out-' + index">
        <circle
          :cx="(point.x / 100) * 380 + 20"
          :cy="(point.y / 100) * 180 + 10"
          r="5"
          fill="#ef4444"
          stroke="#ffffff"
          stroke-width="2"
          class="dark:fill-red-400 dark:stroke-gray-800"
        >
          <title>صادر - {{ point.label }}: {{ formatNumber(point.value) }}</title>
        </circle>
        <!-- Value label above point -->
        <text
          :x="(point.x / 100) * 380 + 20"
          :y="(point.y / 100) * 180 - 5"
          class="text-xs font-bold fill-gray-900 dark:fill-gray-100"
          text-anchor="middle"
          style="font-size: 9px; font-weight: 700; pointer-events: none;"
        >
          {{ formatNumber(point.value) }}
        </text>
      </g>
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


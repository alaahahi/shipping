<template>
  <div class="fixed bottom-4 left-4 z-50" v-if="pendingCount > 0 || !isOnline">
    <!-- بطاقة الحالة -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 p-4 max-w-sm">
      <!-- حالة الاتصال -->
      <div class="flex items-center justify-between mb-3">
        <div class="flex items-center space-x-2 space-x-reverse">
          <div 
            class="w-3 h-3 rounded-full"
            :class="isOnline ? 'bg-green-500 animate-pulse' : 'bg-red-500'"
          ></div>
          <span class="text-sm font-semibold dark:text-gray-200">
            {{ isOnline ? 'متصل' : 'غير متصل' }}
          </span>
        </div>
        
        <button 
          @click="collapsed = !collapsed"
          class="text-gray-500 hover:text-gray-700 dark:text-gray-400"
        >
          <svg 
            class="w-5 h-5 transition-transform"
            :class="{ 'rotate-180': collapsed }"
            fill="none" 
            stroke="currentColor" 
            viewBox="0 0 24 24"
          >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
          </svg>
        </button>
      </div>

      <!-- التفاصيل (قابلة للطي) -->
      <div v-show="!collapsed" class="space-y-3">
        <!-- عداد العقود المعلقة -->
        <div 
          v-if="pendingCount > 0"
          class="flex items-center justify-between p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg border border-yellow-200 dark:border-yellow-800"
        >
          <div class="flex items-center space-x-2 space-x-reverse">
            <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span class="text-sm font-medium text-yellow-800 dark:text-yellow-300">
              {{ pendingCount }} عقد بانتظار المزامنة
            </span>
          </div>
        </div>

        <!-- حالة المزامنة -->
        <div v-if="isSyncing" class="flex items-center space-x-2 space-x-reverse p-2">
          <svg class="animate-spin h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          <span class="text-sm text-blue-600 dark:text-blue-400">جاري المزامنة...</span>
        </div>

        <!-- الإحصائيات -->
        <div class="grid grid-cols-2 gap-2 text-xs dark:text-gray-300">
          <div class="flex justify-between">
            <span>معلقة:</span>
            <span class="font-bold text-yellow-600">{{ stats.pending }}</span>
          </div>
          <div class="flex justify-between">
            <span>فاشلة:</span>
            <span class="font-bold text-red-600">{{ stats.failed }}</span>
          </div>
        </div>

        <!-- آخر مزامنة -->
        <div v-if="lastSyncFormatted" class="text-xs text-gray-500 dark:text-gray-400 pt-2 border-t dark:border-gray-700">
          آخر مزامنة: {{ lastSyncFormatted }}
        </div>

        <!-- أزرار الإجراءات -->
        <div class="flex space-x-2 space-x-reverse pt-2">
          <button
            @click="syncNow"
            :disabled="!isOnline || isSyncing || pendingCount === 0"
            class="flex-1 px-3 py-2 text-xs font-medium text-white bg-blue-600 rounded hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition"
          >
            <span v-if="!isSyncing">مزامنة الآن</span>
            <span v-else>جاري المزامنة...</span>
          </button>
          
          <button
            @click="viewDetails"
            class="px-3 py-2 text-xs font-medium text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-gray-700 rounded hover:bg-gray-200 dark:hover:bg-gray-600 transition"
          >
            التفاصيل
          </button>
        </div>

        <!-- أخطاء المزامنة -->
        <div v-if="syncErrors.length > 0" class="pt-2 border-t dark:border-gray-700">
          <p class="text-xs text-red-600 dark:text-red-400 font-medium mb-1">
            أخطاء المزامنة ({{ syncErrors.length }}):
          </p>
          <div class="max-h-24 overflow-y-auto space-y-1">
            <div 
              v-for="(error, index) in syncErrors.slice(0, 3)" 
              :key="index"
              class="text-xs text-red-500 dark:text-red-400 bg-red-50 dark:bg-red-900/20 p-1 rounded"
            >
              {{ error.contractId }}: {{ error.error }}
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';

const props = defineProps({
  isOnline: {
    type: Boolean,
    required: true
  },
  isSyncing: {
    type: Boolean,
    required: true
  },
  pendingCount: {
    type: Number,
    required: true
  },
  lastSyncFormatted: {
    type: String,
    default: ''
  },
  syncErrors: {
    type: Array,
    default: () => []
  },
  stats: {
    type: Object,
    required: true
  }
});

const emit = defineEmits(['sync', 'view-details']);

const collapsed = ref(false);

const syncNow = () => {
  emit('sync');
};

const viewDetails = () => {
  emit('view-details');
};
</script>

<style scoped>
@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.5;
  }
}

.animate-pulse {
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>


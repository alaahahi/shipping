<template>
  <Modal :show="show" @close="$emit('close')" max-width="4xl">
    <div class="p-6 dark:bg-gray-900">
      <!-- العنوان -->
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold dark:text-gray-200">
          العقود المحفوظة محلياً
        </h2>
        <button 
          @click="$emit('close')"
          class="text-gray-500 hover:text-gray-700 dark:text-gray-400"
        >
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>

      <!-- الإحصائيات -->
      <div class="grid grid-cols-4 gap-4 mb-6">
        <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg border border-blue-200 dark:border-blue-800">
          <p class="text-sm text-blue-600 dark:text-blue-400">الإجمالي</p>
          <p class="text-2xl font-bold dark:text-gray-200">{{ contracts.length }}</p>
        </div>
        <div class="bg-yellow-50 dark:bg-yellow-900/20 p-4 rounded-lg border border-yellow-200 dark:border-yellow-800">
          <p class="text-sm text-yellow-600 dark:text-yellow-400">معلقة</p>
          <p class="text-2xl font-bold dark:text-gray-200">{{ stats.pending }}</p>
        </div>
        <div class="bg-red-50 dark:bg-red-900/20 p-4 rounded-lg border border-red-200 dark:border-red-800">
          <p class="text-sm text-red-600 dark:text-red-400">فاشلة</p>
          <p class="text-2xl font-bold dark:text-gray-200">{{ stats.failed }}</p>
        </div>
        <div class="bg-green-50 dark:bg-green-900/20 p-4 rounded-lg border border-green-200 dark:border-green-800">
          <p class="text-sm text-green-600 dark:text-green-400">نجحت</p>
          <p class="text-2xl font-bold dark:text-gray-200">{{ stats.synced }}</p>
        </div>
      </div>

      <!-- أزرار الإجراءات الرئيسية -->
      <div class="flex space-x-2 space-x-reverse mb-4">
        <button
          @click="$emit('sync-all')"
          :disabled="!isOnline || isSyncing || contracts.length === 0"
          class="flex-1 px-4 py-2 text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition"
        >
          <span v-if="!isSyncing">🔄 مزامنة الكل</span>
          <span v-else>⏳ جاري المزامنة...</span>
        </button>
        
        <button
          @click="$emit('export')"
          :disabled="contracts.length === 0"
          class="px-4 py-2 text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 disabled:bg-gray-300 disabled:cursor-not-allowed transition"
        >
          📥 تصدير
        </button>
        
        <button
          @click="triggerImport"
          class="px-4 py-2 text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition"
        >
          📤 استيراد
        </button>
        <input 
          ref="fileInput"
          type="file" 
          accept="application/json"
          @change="handleFileImport"
          class="hidden"
        >
      </div>

      <!-- قائمة العقود -->
      <div class="max-h-96 overflow-y-auto">
        <div v-if="contracts.length === 0" class="text-center py-12 text-gray-500 dark:text-gray-400">
          <svg class="w-16 h-16 mx-auto mb-4 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
          </svg>
          <p class="text-lg font-medium">لا توجد عقود محفوظة محلياً</p>
        </div>

        <div v-else class="space-y-3">
          <div
            v-for="contract in contracts"
            :key="contract._id"
            class="border dark:border-gray-700 rounded-lg p-4 hover:bg-gray-50 dark:hover:bg-gray-800 transition"
          >
            <!-- رأس العقد -->
            <div class="flex items-start justify-between mb-3">
              <div class="flex-1">
                <div class="flex items-center space-x-2 space-x-reverse mb-1">
                  <h3 class="font-semibold dark:text-gray-200">{{ contract.name_buyer }}</h3>
                  <span 
                    class="px-2 py-1 text-xs rounded-full"
                    :class="getStatusClass(contract._status)"
                  >
                    {{ getStatusText(contract._status) }}
                  </span>
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                  {{ contract.car_name }} - {{ contract.vin }}
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">
                  تاريخ الإنشاء: {{ formatDate(contract._createdAt) }}
                </p>
              </div>
              
              <!-- الأيقونات -->
              <div class="flex space-x-1 space-x-reverse">
                <button
                  v-if="contract._status === 'failed'"
                  @click="$emit('retry', contract._id)"
                  :disabled="!isOnline"
                  class="p-2 text-blue-600 hover:bg-blue-50 dark:text-blue-400 dark:hover:bg-blue-900/20 rounded disabled:opacity-50"
                  title="إعادة المحاولة"
                >
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                  </svg>
                </button>
                
                <button
                  @click="$emit('delete', contract._id)"
                  class="p-2 text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20 rounded"
                  title="حذف"
                >
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                  </svg>
                </button>
                
                <button
                  @click="toggleDetails(contract._id)"
                  class="p-2 text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700 rounded"
                  title="التفاصيل"
                >
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                  </svg>
                </button>
              </div>
            </div>

            <!-- التفاصيل الموسعة -->
            <div v-if="expandedContract === contract._id" class="mt-3 pt-3 border-t dark:border-gray-700">
              <div class="grid grid-cols-2 gap-3 text-sm dark:text-gray-300">
                <div>
                  <p class="text-gray-500 dark:text-gray-500 text-xs">البائع</p>
                  <p class="font-medium">{{ contract.name_seller }}</p>
                  <p class="text-xs text-gray-600 dark:text-gray-400">{{ contract.phone_seller }}</p>
                </div>
                <div>
                  <p class="text-gray-500 dark:text-gray-500 text-xs">المشتري</p>
                  <p class="font-medium">{{ contract.name_buyer }}</p>
                  <p class="text-xs text-gray-600 dark:text-gray-400">{{ contract.phone_buyer }}</p>
                </div>
                <div>
                  <p class="text-gray-500 dark:text-gray-500 text-xs">السعر</p>
                  <p class="font-medium">{{ contract.car_price || 0 }} $</p>
                </div>
                <div>
                  <p class="text-gray-500 dark:text-gray-500 text-xs">المدفوع</p>
                  <p class="font-medium">{{ contract.car_paid || 0 }} $</p>
                </div>
              </div>

              <!-- معلومات المحاولة -->
              <div v-if="contract._retryCount > 0" class="mt-3 p-2 bg-orange-50 dark:bg-orange-900/20 rounded text-xs">
                <p class="text-orange-600 dark:text-orange-400">
                  عدد المحاولات: {{ contract._retryCount }}
                </p>
                <p v-if="contract._error" class="text-red-600 dark:text-red-400 mt-1">
                  آخر خطأ: {{ contract._error }}
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </Modal>
</template>

<script setup>
import Modal from './Modal.vue';
import { ref } from 'vue';

defineProps({
  show: {
    type: Boolean,
    required: true
  },
  isOnline: {
    type: Boolean,
    required: true
  },
  isSyncing: {
    type: Boolean,
    required: true
  },
  contracts: {
    type: Array,
    required: true
  },
  stats: {
    type: Object,
    required: true
  },
  lastSyncFormatted: {
    type: String,
    default: ''
  },
  syncErrors: {
    type: Array,
    default: () => []
  }
});

const emit = defineEmits(['close', 'sync-all', 'retry', 'delete', 'export', 'import']);

const expandedContract = ref(null);
const fileInput = ref(null);

const toggleDetails = (id) => {
  expandedContract.value = expandedContract.value === id ? null : id;
};

const getStatusClass = (status) => {
  const classes = {
    'pending': 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
    'syncing': 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
    'synced': 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
    'failed': 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400'
  };
  return classes[status] || classes.pending;
};

const getStatusText = (status) => {
  const texts = {
    'pending': 'معلق',
    'syncing': 'جاري المزامنة',
    'synced': 'تمت المزامنة',
    'failed': 'فشلت'
  };
  return texts[status] || 'غير معروف';
};

const formatDate = (dateString) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  return date.toLocaleString('ar-EG', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};

const triggerImport = () => {
  fileInput.value.click();
};

const handleFileImport = (event) => {
  const file = event.target.files[0];
  if (file) {
    emit('import', file);
    event.target.value = ''; // Reset input
  }
};
</script>


<template>
  <Head :title="`تفاصيل الجدول: ${tableName}`" />
  <AuthenticatedLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <div class="flex items-center gap-4">
          <button
            @click="$inertia.visit('/sync-monitor')"
            class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600"
          >
            ← رجوع
          </button>
          <h2 class="font-semibold text-xl dark:text-gray-200 text-gray-800">
            📋 تفاصيل الجدول: {{ tableName }}
          </h2>
        </div>
        <div class="flex gap-2">
          <span
            class="px-3 py-1 rounded-full text-sm font-semibold"
            :class="connection === 'sync_sqlite'
              ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-100'
              : 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-100'"
          >
            {{ connection === 'sync_sqlite' ? '🖥️ SQLite محلي' : '☁️ MySQL سيرفر' }}
          </span>
          <button
            @click="loadTableDetails(0)"
            class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600"
            :disabled="loading"
          >
            <span v-if="!loading">🔄 تحديث</span>
            <span v-else>⏳ جاري...</span>
          </button>
        </div>
      </div>
    </template>

    <div class="py-6">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        
        <!-- معلومات الجدول -->
        <div class="mb-6 bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
          <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
              <p class="text-sm text-gray-600 dark:text-gray-400">اسم الجدول</p>
              <p class="text-lg font-semibold dark:text-gray-200">{{ tableName }}</p>
            </div>
            <div>
              <p class="text-sm text-gray-600 dark:text-gray-400">إجمالي السجلات</p>
              <p class="text-lg font-semibold dark:text-gray-200">{{ total?.toLocaleString() || 0 }}</p>
            </div>
            <div>
              <p class="text-sm text-gray-600 dark:text-gray-400">عدد الأعمدة</p>
              <p class="text-lg font-semibold dark:text-gray-200">{{ columns.length }}</p>
            </div>
            <div>
              <p class="text-sm text-gray-600 dark:text-gray-400">الاتصال</p>
              <p class="text-lg font-semibold dark:text-gray-200">
                {{ connection === 'sync_sqlite' ? 'SQLite' : 'MySQL' }}
              </p>
            </div>
          </div>
        </div>

        <!-- رسالة خطأ -->
        <div v-if="error" class="mb-6 bg-red-50 dark:bg-red-900 border-l-4 border-red-500 p-4 rounded">
          <p class="text-red-800 dark:text-red-200">{{ error }}</p>
        </div>

        <!-- جدول البيانات -->
        <div v-if="!loading && !error" class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden">
          <div v-if="columns.length > 0" class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
              <thead class="bg-gray-50 dark:bg-gray-700 sticky top-0">
                <tr>
                  <th
                    v-for="column in columns"
                    :key="column"
                    class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase border-r border-gray-200 dark:border-gray-600"
                  >
                    {{ column }}
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                <tr v-if="data.length === 0">
                  <td :colspan="columns.length || 1" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                    لا توجد بيانات
                  </td>
                </tr>
                <tr
                  v-for="(row, index) in data"
                  :key="index"
                  class="hover:bg-gray-50 dark:hover:bg-gray-700"
                >
                  <td
                    v-for="column in columns"
                    :key="`${index}-${column}`"
                    class="px-4 py-3 text-sm dark:text-gray-300 border-r border-gray-100 dark:border-gray-700 whitespace-nowrap"
                  >
                    {{ formatCellValue(row[column]) }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div v-else class="p-8 text-center text-gray-500 dark:text-gray-400">
            <div class="text-5xl mb-2">📋</div>
            <p>لا توجد أعمدة متاحة للعرض</p>
            <p class="text-xs mt-2">عدد البيانات: {{ data.length }}</p>
          </div>

          <!-- Pagination -->
          <div v-if="total > limit" class="px-6 py-4 bg-gray-50 dark:bg-gray-700 flex justify-between items-center border-t border-gray-200 dark:border-gray-600">
            <div class="text-sm text-gray-700 dark:text-gray-300">
              عرض {{ offset + 1 }} إلى {{ Math.min(offset + limit, total) }} من {{ total }}
            </div>
            <div class="flex gap-2">
              <button
                @click="loadTableDetails(offset - limit)"
                :disabled="offset === 0 || loading"
                class="px-4 py-2 border rounded disabled:opacity-50 dark:text-gray-200 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-600"
              >
                ← السابق
              </button>
              <button
                @click="loadTableDetails(offset + limit)"
                :disabled="offset + limit >= total || loading"
                class="px-4 py-2 border rounded disabled:opacity-50 dark:text-gray-200 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-600"
              >
                التالي →
              </button>
            </div>
          </div>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-8 text-center">
          <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
          <p class="mt-4 text-gray-600 dark:text-gray-400">جاري تحميل البيانات...</p>
        </div>

      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { Head, usePage } from '@inertiajs/inertia-vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import axios from 'axios';
import { useToast } from 'vue-toastification';

const props = defineProps({
  tableName: {
    type: String,
    required: true
  },
  connection: {
    type: String,
    default: 'auto'
  }
});

const toast = useToast();

const columns = ref([]);
const data = ref([]);
const total = ref(0);
const limit = ref(50);
const offset = ref(0);
const loading = ref(false);
const error = ref(null);

const formatCellValue = (value) => {
  if (value === null || value === undefined) {
    return '-';
  }
  
  if (typeof value === 'boolean') {
    return value ? '✓' : '✗';
  }
  
  if (typeof value === 'object') {
    return JSON.stringify(value);
  }
  
  if (typeof value === 'string' && value.length > 100) {
    return value.substring(0, 100) + '...';
  }
  
  return String(value);
};

const loadTableDetails = async (newOffset = 0) => {
  loading.value = true;
  error.value = null;
  
  try {
    const params = {
      limit: limit.value,
      offset: newOffset
    };
    
    // استخدام connection المحدد
    if (props.connection && props.connection !== 'auto') {
      params.force_connection = props.connection;
    }
    
    const response = await axios.get(`/api/sync-monitor/table/${props.tableName}`, { 
      params,
      withCredentials: true
    });
    
    // التأكد من أن البيانات هي array
    let cols = [];
    if (Array.isArray(response.data.columns)) {
      cols = response.data.columns;
    } else if (response.data.columns && typeof response.data.columns === 'object') {
      cols = Object.values(response.data.columns);
    }
    
    let tableData = [];
    if (Array.isArray(response.data.data)) {
      tableData = response.data.data;
    } else if (response.data.data && typeof response.data.data === 'object') {
      tableData = Object.values(response.data.data);
    }
    
    // إذا كانت الأعمدة فارغة لكن هناك بيانات، استخرج الأعمدة من أول سطر
    if (cols.length === 0 && tableData.length > 0) {
      const firstRow = tableData[0];
      if (firstRow && typeof firstRow === 'object') {
        cols = Object.keys(firstRow);
      }
    }
    
    columns.value = cols;
    data.value = tableData;
    total.value = response.data.total || 0;
    offset.value = response.data.offset || newOffset;
    
  } catch (err) {
    console.error('فشل تحميل تفاصيل الجدول:', err);
    error.value = err.response?.data?.error || err.message || 'فشل تحميل البيانات';
    toast.error('فشل تحميل تفاصيل الجدول');
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  loadTableDetails(0);
});
</script>

<style scoped>
.animate-spin {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}
</style>


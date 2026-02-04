<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/inertia-vue3';
import { ref, onMounted } from 'vue';
import { useToast } from "vue-toastification";
import axios from 'axios';

const toast = useToast();

const logFiles = ref([]);
const selectedFile = ref('laravel.log');
const logContent = ref('');
const loading = ref(false);
const clearing = ref(false);

onMounted(() => {
  if (props.logFiles?.length) {
    logFiles.value = props.logFiles;
    if (logFiles.value.length > 0 && !logFiles.value.find(f => f.name === selectedFile.value)) {
      selectedFile.value = logFiles.value[0].name;
    }
  }
  loadLog();
});

const props = defineProps({
  logFiles: {
    type: Array,
    default: () => [],
  },
});

onMounted(() => {
  if (props.logFiles?.length) {
    logFiles.value = props.logFiles;
    if (logFiles.value.length > 0 && !logFiles.value.find(f => f.name === selectedFile.value)) {
      selectedFile.value = logFiles.value[0].name;
    }
  }
  loadLog();
});

function loadLog() {
  loading.value = true;
  axios.get(route('logViewer.content'), { params: { file: selectedFile.value } })
    .then(response => {
      logContent.value = response.data.content || '(فارغ)';
      if (response.data.logFiles?.length) {
        logFiles.value = response.data.logFiles;
      }
    })
    .catch(error => {
      console.error(error);
      toast.error('فشل تحميل اللوغ', { timeout: 3000, position: "bottom-right", rtl: true });
      logContent.value = '(خطأ في التحميل)';
    })
    .finally(() => {
      loading.value = false;
    });
}

function clearLog() {
  if (!confirm('هل أنت متأكد من تفريغ ملف اللوغ؟ سيتم حذف كل المحتوى.')) {
    return;
  }
  clearing.value = true;
  axios.post(route('logViewer.clear'), { file: selectedFile.value })
    .then(response => {
      toast.success(response.data.message || 'تم التفريغ بنجاح', { timeout: 2000, position: "bottom-right", rtl: true });
      logContent.value = '(تم التفريغ)';
      if (response.data.logFiles?.length) {
        logFiles.value = response.data.logFiles;
      }
    })
    .catch(error => {
      toast.error(error.response?.data?.error || 'فشل التفريغ', { timeout: 3000, position: "bottom-right", rtl: true });
    })
    .finally(() => {
      clearing.value = false;
    });
}

function formatSize(bytes) {
  if (bytes < 1024) return bytes + ' B';
  if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
  return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
}

function formatDate(timestamp) {
  if (!timestamp) return '—';
  return new Date(timestamp * 1000).toLocaleString('ar-IQ');
}
</script>

<template>
  <Head title="عرض وتفريغ لوغ الأخطاء" />

  <AuthenticatedLayout>
    <template #header>
      <h1 class="text-xl font-semibold text-gray-800 dark:text-gray-200">
        عرض وتفريغ لوغ الأخطاء
      </h1>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 text-gray-900 dark:text-gray-100">
            <div class="flex flex-wrap items-center gap-4 mb-4">
              <div class="flex items-center gap-2">
                <label for="log-file" class="text-sm font-medium">الملف:</label>
                <select
                  id="log-file"
                  v-model="selectedFile"
                  @change="loadLog"
                  class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                >
                  <option v-for="f in logFiles" :key="f.name" :value="f.name">
                    {{ f.name }} ({{ formatSize(f.size) }})
                  </option>
                  <option v-if="logFiles.length === 0" value="laravel.log">laravel.log</option>
                </select>
              </div>
              <button
                @click="loadLog"
                :disabled="loading"
                class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-50 text-sm"
              >
                {{ loading ? 'جاري التحميل...' : 'تحديث' }}
              </button>
              <button
                @click="clearLog"
                :disabled="clearing"
                class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 disabled:opacity-50 text-sm"
              >
                {{ clearing ? 'جاري...' : 'تفريغ اللوغ' }}
              </button>
            </div>

            <div class="border border-gray-300 dark:border-gray-600 rounded-lg overflow-hidden bg-gray-50 dark:bg-gray-900">
              <pre class="p-4 text-xs overflow-auto max-h-[70vh] whitespace-pre-wrap font-mono text-gray-800 dark:text-gray-200" dir="ltr">{{ logContent }}</pre>
            </div>

            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
              مسار الملفات: storage/logs/
            </p>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

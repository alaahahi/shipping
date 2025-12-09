<template>
  <AuthenticatedLayout>
    <Head title="تاريخ السيارة" />

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg mb-6">
          <div class="p-6 bg-white border-b border-gray-200">
            <div class="flex items-center justify-between">
              <div>
                <h1 class="text-2xl font-bold text-gray-900">
                  📋 تاريخ السيارة رقم {{ car.no }}
                </h1>
                <p class="mt-1 text-sm text-gray-600">
                  {{ car.company?.name }} - {{ car.name?.name }} - {{ car.model?.name }} - {{ car.color?.name }}
                </p>
              </div>
              <div class="flex space-x-3">
                <Link
                  :href="route('dashboard')"
                  class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded"
                >
                  ← العودة للوحة التحكم
                </Link>
              </div>
            </div>
          </div>
        </div>

        <!-- Filters -->
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg mb-6">
          <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">نوع العملية</label>
                <select
                  v-model="filters.action"
                  @change="fetchHistory"
                  class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                >
                  <option value="">الكل</option>
                  <option value="create">إضافة</option>
                  <option value="update">تحديث</option>
                  <option value="delete">حذف</option>
                </select>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">المستخدم</label>
                <select
                  v-model="filters.user_id"
                  @change="fetchHistory"
                  class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                >
                  <option value="">الكل</option>
                  <option v-for="user in availableUsers" :key="user.id" :value="user.id">
                    {{ user.name }}
                  </option>
                </select>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">من تاريخ</label>
                <input
                  type="date"
                  v-model="filters.date_from"
                  @change="fetchHistory"
                  class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                >
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">إلى تاريخ</label>
                <input
                  type="date"
                  v-model="filters.date_to"
                  @change="fetchHistory"
                  class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                >
              </div>
            </div>
          </div>
        </div>

        <!-- History List -->
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
          <div class="p-6">
            <div v-if="loading" class="text-center py-8">
              <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600 mx-auto"></div>
              <p class="mt-4 text-gray-600">جاري تحميل البيانات...</p>
            </div>

            <div v-else-if="history.length === 0" class="text-center py-8">
              <div class="text-gray-400 text-6xl mb-4">📋</div>
              <h3 class="text-lg font-medium text-gray-900 mb-2">لا توجد تغييرات</h3>
              <p class="text-gray-600">لم يتم العثور على أي تغييرات لهذه السيارة</p>
            </div>

            <div v-else>
              <div class="space-y-4">
                <div
                  v-for="item in history"
                  :key="item.id"
                  class="border rounded-lg p-4 hover:bg-gray-50 transition-colors"
                  :class="{
                    'border-green-200 bg-green-50': item.action === 'create',
                    'border-blue-200 bg-blue-50': item.action === 'update',
                    'border-red-200 bg-red-50': item.action === 'delete'
                  }"
                >
                  <div class="flex items-start justify-between">
                    <div class="flex-1">
                      <div class="flex items-center space-x-3 mb-2">
                        <span
                          class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                          :class="{
                            'bg-green-100 text-green-800': item.action === 'create',
                            'bg-blue-100 text-blue-800': item.action === 'update',
                            'bg-red-100 text-red-800': item.action === 'delete'
                          }"
                        >
                          {{ getActionText(item.action) }}
                        </span>
                        <time class="text-sm text-gray-500">
                          {{ formatDate(item.created_at) }}
                        </time>
                      </div>

                      <p class="text-sm text-gray-700 mb-2">{{ item.description }}</p>

                      <div v-if="item.changes_summary" class="text-sm text-gray-600 mb-2">
                        <strong>التغييرات:</strong> {{ item.changes_summary }}
                      </div>

                      <div class="flex items-center text-sm text-gray-500">
                        <span>بواسطة: {{ item.user?.name || item.user_name || 'غير معروف' }}</span>
                        <span v-if="item.ip_address" class="mx-2">•</span>
                        <span v-if="item.ip_address">IP: {{ item.ip_address }}</span>
                      </div>
                    </div>

                    <div class="flex space-x-2">
                      <button
                        @click="showDetails(item)"
                        class="text-indigo-600 hover:text-indigo-900 text-sm font-medium"
                      >
                        التفاصيل
                      </button>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Pagination -->
              <div v-if="pagination.last_page > 1" class="mt-6 flex justify-center">
                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                  <button
                    v-for="page in pagination.links"
                    :key="page.label"
                    @click="goToPage(page.url)"
                    class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50"
                    :class="{
                      'bg-indigo-50 border-indigo-500 text-indigo-600': page.active,
                      'pointer-events-none': !page.url
                    }"
                    v-html="page.label"
                  ></button>
                </nav>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Details Modal -->
    <Modal :show="showModal" @close="showModal = false" max-width="4xl">
      <div class="p-6">
        <h2 class="text-lg font-medium text-gray-900 mb-4">
          تفاصيل التغيير
        </h2>

        <div v-if="selectedItem" class="space-y-4">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700">نوع العملية</label>
              <span class="text-sm text-gray-900">{{ getActionText(selectedItem.action) }}</span>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">التاريخ والوقت</label>
              <span class="text-sm text-gray-900">{{ formatDateTime(selectedItem.created_at) }}</span>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">المستخدم</label>
              <span class="text-sm text-gray-900">{{ selectedItem.user?.name || selectedItem.user_name || 'غير معروف' }}</span>
            </div>
            <div v-if="selectedItem.ip_address">
              <label class="block text-sm font-medium text-gray-700">عنوان IP</label>
              <span class="text-sm text-gray-900">{{ selectedItem.ip_address }}</span>
            </div>
          </div>

          <div v-if="selectedItem.description">
            <label class="block text-sm font-medium text-gray-700 mb-2">الوصف</label>
            <p class="text-sm text-gray-900 bg-gray-50 p-3 rounded">{{ selectedItem.description }}</p>
          </div>

          <div v-if="selectedItem.changes && Object.keys(selectedItem.changes).length > 0">
            <label class="block text-sm font-medium text-gray-700 mb-2">التغييرات التفصيلية</label>
            <div class="bg-gray-50 p-3 rounded space-y-2">
              <div
                v-for="[field, change] in Object.entries(selectedItem.changes)"
                :key="field"
                class="text-sm"
              >
                <strong>{{ field }}:</strong>
                <span class="text-red-600">{{ change.old }}</span> →
                <span class="text-green-600">{{ change.new }}</span>
              </div>
            </div>
          </div>

          <div v-if="selectedItem.old_data" class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">البيانات القديمة</label>
              <pre class="text-xs bg-red-50 p-3 rounded overflow-auto max-h-40">{{ JSON.stringify(selectedItem.old_data, null, 2) }}</pre>
            </div>
            <div v-if="selectedItem.new_data">
              <label class="block text-sm font-medium text-gray-700 mb-2">البيانات الجديدة</label>
              <pre class="text-xs bg-green-50 p-3 rounded overflow-auto max-h-40">{{ JSON.stringify(selectedItem.new_data, null, 2) }}</pre>
            </div>
          </div>
        </div>

        <div class="flex justify-end mt-6">
          <button
            @click="showModal = false"
            class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded"
          >
            إغلاق
          </button>
        </div>
      </div>
    </Modal>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Head, Link } from '@inertiajs/inertia-vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Modal from '@/Components/Modal.vue'
import axios from 'axios'

const props = defineProps({
  car: Object,
  filters: Object
})

const history = ref([])
const loading = ref(false)
const showModal = ref(false)
const selectedItem = ref(null)
const availableUsers = ref([])
const pagination = ref({
  current_page: 1,
  last_page: 1,
  links: []
})

const filters = ref({
  action: props.filters?.action || '',
  user_id: props.filters?.user_id || '',
  date_from: props.filters?.date_from || '',
  date_to: props.filters?.date_to || ''
})

const fetchHistory = async (page = 1) => {
  loading.value = true
  try {
    const params = {
      page,
      ...filters.value
    }

    const response = await axios.get(`/api/car/${props.car.id}/history`, { params })
    history.value = response.data.data
    pagination.value = {
      current_page: response.data.current_page,
      last_page: response.data.last_page,
      links: response.data.links || []
    }
  } catch (error) {
    console.error('Error fetching history:', error)
  } finally {
    loading.value = false
  }
}

const goToPage = (url) => {
  if (url) {
    const urlObj = new URL(url)
    const page = urlObj.searchParams.get('page')
    fetchHistory(page)
  }
}

const showDetails = (item) => {
  selectedItem.value = item
  showModal.value = true
}

const getActionText = (action) => {
  const actions = {
    create: 'إضافة',
    update: 'تحديث',
    delete: 'حذف',
    restore: 'استعادة'
  }
  return actions[action] || action
}

const formatDate = (dateString) => {
  const date = new Date(dateString)
  return date.toLocaleDateString('ar-IQ', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

const formatDateTime = (dateString) => {
  const date = new Date(dateString)
  return date.toLocaleString('ar-IQ', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

onMounted(() => {
  fetchHistory()
})
</script>



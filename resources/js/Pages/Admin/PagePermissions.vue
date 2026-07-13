<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/inertia-vue3';
import { ref, computed, onMounted } from 'vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { useToast } from 'vue-toastification';
import axios from 'axios';

const toast = useToast();

const pages = ref([]);
const userTypes = ref([]);
const loading = ref(false);
const activeTab = ref('pages');
const showModal = ref(false);
const editingPage = ref(null);
const selectedUserTypeId = ref(null);

const formData = ref({
  label: '',
  route_name: '',
  path: '',
  nav_group: 'main',
  sort_order: 0,
  is_active: true,
  user_type_ids: [],
});

const selectedTypePageIds = ref([]);

const sortedPages = computed(() => {
  return [...pages.value].sort((a, b) => (a.sort_order ?? 0) - (b.sort_order ?? 0));
});

const selectedUserType = computed(() => {
  return userTypes.value.find((t) => t.id === selectedUserTypeId.value) || null;
});

onMounted(() => {
  loadData();
});

function loadData() {
  loading.value = true;
  axios.get('/api/page-permissions')
    .then((response) => {
      pages.value = response.data.pages || [];
      userTypes.value = response.data.userTypes || [];
      if (!selectedUserTypeId.value && userTypes.value.length) {
        selectUserType(userTypes.value[0].id);
      } else {
        refreshSelectedTypePages();
      }
    })
    .catch(() => {
      toast.error('حدث خطأ أثناء تحميل الصلاحيات', { timeout: 3000, position: 'bottom-right', rtl: true });
    })
    .finally(() => {
      loading.value = false;
    });
}

function openAddModal() {
  editingPage.value = null;
  formData.value = {
    label: '',
    route_name: '',
    path: '',
    nav_group: 'main',
    sort_order: pages.value.length + 1,
    is_active: true,
    user_type_ids: [],
  };
  showModal.value = true;
}

function openEditModal(page) {
  editingPage.value = page;
  formData.value = {
    label: page.label,
    route_name: page.route_name || '',
    path: page.path || '',
    nav_group: page.nav_group || 'main',
    sort_order: page.sort_order ?? 0,
    is_active: !!page.is_active,
    user_type_ids: (page.user_types || []).map((t) => t.id),
  };
  showModal.value = true;
}

function closeModal() {
  showModal.value = false;
  editingPage.value = null;
}

function toggleUserType(typeId) {
  const ids = formData.value.user_type_ids;
  const index = ids.indexOf(typeId);
  if (index === -1) {
    ids.push(typeId);
  } else {
    ids.splice(index, 1);
  }
}

function savePage() {
  if (!formData.value.label.trim()) {
    toast.error('اسم الصفحة مطلوب', { timeout: 3000, position: 'bottom-right', rtl: true });
    return;
  }

  const payload = { ...formData.value };

  const request = editingPage.value
    ? axios.put(`/api/page-permissions/${editingPage.value.id}`, payload)
    : axios.post('/api/page-permissions', payload);

  request
    .then(() => {
      toast.success(editingPage.value ? 'تم تحديث الصفحة' : 'تمت إضافة الصفحة', { timeout: 2000, position: 'bottom-right', rtl: true });
      closeModal();
      loadData();
    })
    .catch((error) => {
      const message = error.response?.data?.message || 'حدث خطأ أثناء الحفظ';
      toast.error(message, { timeout: 3000, position: 'bottom-right', rtl: true });
    });
}

function deletePage(page) {
  if (!confirm(`حذف الصفحة "${page.label}"؟`)) return;

  axios.delete(`/api/page-permissions/${page.id}`)
    .then(() => {
      toast.success('تم حذف الصفحة', { timeout: 2000, position: 'bottom-right', rtl: true });
      loadData();
    })
    .catch(() => {
      toast.error('تعذر حذف الصفحة', { timeout: 3000, position: 'bottom-right', rtl: true });
    });
}

function selectUserType(typeId) {
  selectedUserTypeId.value = typeId;
  refreshSelectedTypePages();
}

function refreshSelectedTypePages() {
  if (!selectedUserTypeId.value) {
    selectedTypePageIds.value = [];
    return;
  }

  selectedTypePageIds.value = pages.value
    .filter((p) => (p.user_types || []).some((t) => t.id === selectedUserTypeId.value))
    .map((p) => p.id);
}

function toggleTypePage(pageId) {
  const index = selectedTypePageIds.value.indexOf(pageId);
  if (index === -1) {
    selectedTypePageIds.value.push(pageId);
  } else {
    selectedTypePageIds.value.splice(index, 1);
  }
}

function saveUserTypePages() {
  if (!selectedUserTypeId.value) return;

  axios.put(`/api/user-type-pages/${selectedUserTypeId.value}`, {
    page_ids: selectedTypePageIds.value,
  })
    .then(() => {
      toast.success('تم حفظ صلاحيات النوع', { timeout: 2000, position: 'bottom-right', rtl: true });
      loadData();
    })
    .catch(() => {
      toast.error('حدث خطأ أثناء حفظ الصلاحيات', { timeout: 3000, position: 'bottom-right', rtl: true });
    });
}

function groupLabel(group) {
  return group === 'more' ? 'قائمة المزيد' : 'القائمة الرئيسية';
}
</script>

<template>
  <Head title="صلاحيات الصفحات" />

  <AuthenticatedLayout>
    <div class="py-8">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-900 overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
              <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">إدارة صلاحيات الصفحات</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                  تحكم بصفحات النظام وأنواع المستخدمين المسموح لها بالوصول
                </p>
              </div>
              <button
                v-if="activeTab === 'pages'"
                @click="openAddModal"
                class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium"
              >
                + إضافة صفحة
              </button>
            </div>

            <div class="flex gap-2 mb-6 border-b border-gray-200 dark:border-gray-700">
              <button
                @click="activeTab = 'pages'"
                :class="activeTab === 'pages' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500'"
                class="px-4 py-2 border-b-2 font-medium"
              >
                الصفحات
              </button>
              <button
                @click="activeTab = 'types'"
                :class="activeTab === 'types' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500'"
                class="px-4 py-2 border-b-2 font-medium"
              >
                حسب نوع المستخدم
              </button>
            </div>

            <div v-if="loading" class="text-center py-10 text-gray-500">جاري التحميل...</div>

            <div v-else-if="activeTab === 'pages'" class="overflow-x-auto">
              <table class="w-full text-sm text-right">
                <thead class="bg-gray-50 dark:bg-gray-800 text-gray-600 dark:text-gray-300">
                  <tr>
                    <th class="px-3 py-2">#</th>
                    <th class="px-3 py-2">الاسم</th>
                    <th class="px-3 py-2">Route</th>
                    <th class="px-3 py-2">المسار</th>
                    <th class="px-3 py-2">المجموعة</th>
                    <th class="px-3 py-2">الترتيب</th>
                    <th class="px-3 py-2">الحالة</th>
                    <th class="px-3 py-2">الأنواع المسموحة</th>
                    <th class="px-3 py-2">إجراءات</th>
                  </tr>
                </thead>
                <tbody>
                  <tr
                    v-for="page in sortedPages"
                    :key="page.id"
                    class="border-b border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800/50"
                  >
                    <td class="px-3 py-2">{{ page.id }}</td>
                    <td class="px-3 py-2 font-medium">{{ page.label }}</td>
                    <td class="px-3 py-2 font-mono text-xs">{{ page.route_name || '—' }}</td>
                    <td class="px-3 py-2 font-mono text-xs">{{ page.path || '—' }}</td>
                    <td class="px-3 py-2">{{ groupLabel(page.nav_group) }}</td>
                    <td class="px-3 py-2">{{ page.sort_order }}</td>
                    <td class="px-3 py-2">
                      <span :class="page.is_active ? 'text-green-600' : 'text-red-500'">
                        {{ page.is_active ? 'مفعّلة' : 'معطّلة' }}
                      </span>
                    </td>
                    <td class="px-3 py-2">
                      <div class="flex flex-wrap gap-1">
                        <span
                          v-for="type in page.user_types"
                          :key="type.id"
                          class="px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-200 text-xs"
                        >
                          {{ type.name }}
                        </span>
                        <span v-if="!page.user_types?.length" class="text-gray-400 text-xs">لا يوجد</span>
                      </div>
                    </td>
                    <td class="px-3 py-2 whitespace-nowrap">
                      <button @click="openEditModal(page)" class="text-blue-600 hover:underline ml-2">تعديل</button>
                      <button @click="deletePage(page)" class="text-red-600 hover:underline">حذف</button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-6">
              <div class="lg:col-span-1 space-y-2">
                <h3 class="font-semibold text-gray-800 dark:text-gray-200 mb-2">أنواع المستخدمين</h3>
                <button
                  v-for="type in userTypes"
                  :key="type.id"
                  @click="selectUserType(type.id)"
                  :class="selectedUserTypeId === type.id
                    ? 'bg-indigo-600 text-white'
                    : 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-700'"
                  class="w-full text-right px-4 py-2 rounded-lg transition"
                >
                  <div class="font-medium">{{ type.name }}</div>
                  <div class="text-xs opacity-80">{{ type.app_pages_count ?? 0 }} صفحة</div>
                </button>
              </div>

              <div class="lg:col-span-2">
                <div v-if="selectedUserType" class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-4">
                  <h3 class="font-semibold mb-4">صفحات مسموحة لـ: {{ selectedUserType.name }}</h3>
                  <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-[28rem] overflow-y-auto">
                    <label
                      v-for="page in sortedPages"
                      :key="page.id"
                      class="flex items-center gap-2 p-2 rounded border border-gray-200 dark:border-gray-700 cursor-pointer hover:bg-white dark:hover:bg-gray-800"
                    >
                      <input
                        type="checkbox"
                        :checked="selectedTypePageIds.includes(page.id)"
                        @change="toggleTypePage(page.id)"
                        class="rounded border-gray-300 text-indigo-600"
                      />
                      <span class="text-sm">
                        <span class="font-medium">{{ page.label }}</span>
                        <span class="block text-xs text-gray-500">{{ page.route_name || page.path }}</span>
                      </span>
                    </label>
                  </div>
                  <button
                    @click="saveUserTypePages"
                    class="mt-4 px-5 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg"
                  >
                    حفظ صلاحيات النوع
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
      <div class="bg-white dark:bg-gray-900 rounded-xl shadow-xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
        <div class="p-6">
          <h2 class="text-xl font-bold mb-4 text-gray-900 dark:text-gray-100">
            {{ editingPage ? 'تعديل صفحة' : 'إضافة صفحة جديدة' }}
          </h2>

          <div class="space-y-4">
            <div>
              <InputLabel value="اسم الصفحة (عربي)" />
              <TextInput v-model="formData.label" class="w-full mt-1" />
            </div>
            <div>
              <InputLabel value="اسم الـ Route (اختياري)" />
              <TextInput v-model="formData.route_name" class="w-full mt-1" placeholder="مثال: purchases" />
            </div>
            <div>
              <InputLabel value="المسار (اختياري)" />
              <TextInput v-model="formData.path" class="w-full mt-1" placeholder="/purchases" />
            </div>
            <div class="grid grid-cols-2 gap-3">
              <div>
                <InputLabel value="المجموعة" />
                <select v-model="formData.nav_group" class="w-full mt-1 rounded-md border-gray-300 dark:bg-gray-800 dark:border-gray-700">
                  <option value="main">القائمة الرئيسية</option>
                  <option value="more">قائمة المزيد</option>
                </select>
              </div>
              <div>
                <InputLabel value="الترتيب" />
                <TextInput v-model.number="formData.sort_order" type="number" min="0" class="w-full mt-1" />
              </div>
            </div>
            <div class="flex items-center gap-2">
              <input id="is_active" v-model="formData.is_active" type="checkbox" class="rounded border-gray-300 text-indigo-600" />
              <label for="is_active" class="text-sm text-gray-700 dark:text-gray-300">الصفحة مفعّلة</label>
            </div>
            <div>
              <InputLabel value="أنواع المستخدمين المسموحة" />
              <div class="mt-2 grid grid-cols-2 gap-2 max-h-40 overflow-y-auto">
                <label
                  v-for="type in userTypes"
                  :key="type.id"
                  class="flex items-center gap-2 text-sm cursor-pointer"
                >
                  <input
                    type="checkbox"
                    :checked="formData.user_type_ids.includes(type.id)"
                    @change="toggleUserType(type.id)"
                    class="rounded border-gray-300 text-indigo-600"
                  />
                  {{ type.name }}
                </label>
              </div>
            </div>
          </div>

          <div class="flex justify-end gap-2 mt-6">
            <button @click="closeModal" class="px-4 py-2 rounded-lg bg-gray-200 dark:bg-gray-700">إلغاء</button>
            <button @click="savePage" class="px-4 py-2 rounded-lg bg-indigo-600 text-white">حفظ</button>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

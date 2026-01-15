<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/inertia-vue3';
import { ref, onMounted } from 'vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { useToast } from "vue-toastification";
import axios from 'axios';

const toast = useToast();

const systems = ref([]);
const showModal = ref(false);
const editingSystem = ref(null);
const formData = ref({
  name: '',
  domain: '',
  api_key: '',
  is_active: true
});

onMounted(() => {
  loadSystems();
});

function loadSystems() {
  axios.get('/api/all-connected-systems')
    .then(response => {
      systems.value = response.data;
    })
    .catch(error => {
      console.error('Error loading systems:', error);
      toast.error('حدث خطأ أثناء تحميل الأنظمة', {
        timeout: 3000,
        position: "bottom-right",
        rtl: true,
      });
    });
}

function openAddModal() {
  editingSystem.value = null;
  formData.value = {
    name: '',
    domain: '',
    api_key: '',
    is_active: true
  };
  showModal.value = true;
}

function openEditModal(system) {
  editingSystem.value = system;
  formData.value = {
    name: system.name,
    domain: system.domain,
    api_key: system.api_key,
    is_active: system.is_active
  };
  showModal.value = true;
}

function closeModal() {
  showModal.value = false;
  editingSystem.value = null;
  formData.value = {
    name: '',
    domain: '',
    api_key: '',
    is_active: true
  };
}

function saveSystem() {
  if (!formData.value.name || !formData.value.domain || !formData.value.api_key) {
    toast.error('يرجى ملء جميع الحقول المطلوبة', {
      timeout: 3000,
      position: "bottom-right",
      rtl: true,
    });
    return;
  }

  if (editingSystem.value) {
    // تحديث
    axios.put(`/api/connected-systems/${editingSystem.value.id}`, formData.value)
      .then(response => {
        toast.success('تم تحديث النظام بنجاح', {
          timeout: 2000,
          position: "bottom-right",
          rtl: true,
        });
        loadSystems();
        closeModal();
      })
      .catch(error => {
        console.error('Error updating system:', error);
        toast.error('حدث خطأ أثناء تحديث النظام', {
          timeout: 3000,
          position: "bottom-right",
          rtl: true,
        });
      });
  } else {
    // إضافة
    axios.post('/api/connected-systems', formData.value)
      .then(response => {
        toast.success('تم إضافة النظام بنجاح', {
          timeout: 2000,
          position: "bottom-right",
          rtl: true,
        });
        loadSystems();
        closeModal();
      })
      .catch(error => {
        console.error('Error adding system:', error);
        toast.error('حدث خطأ أثناء إضافة النظام', {
          timeout: 3000,
          position: "bottom-right",
          rtl: true,
        });
      });
  }
}

function deleteSystem(system) {
  if (!confirm(`هل أنت متأكد من حذف النظام "${system.name}"؟`)) {
    return;
  }

  axios.delete(`/api/connected-systems/${system.id}`)
    .then(response => {
      toast.success('تم حذف النظام بنجاح', {
        timeout: 2000,
        position: "bottom-right",
        rtl: true,
      });
      loadSystems();
    })
    .catch(error => {
      console.error('Error deleting system:', error);
      toast.error('حدث خطأ أثناء حذف النظام', {
        timeout: 3000,
        position: "bottom-right",
        rtl: true,
      });
    });
}

function toggleActive(system) {
  axios.put(`/api/connected-systems/${system.id}`, {
    is_active: !system.is_active
  })
    .then(response => {
      toast.success(`تم ${system.is_active ? 'تعطيل' : 'تفعيل'} النظام بنجاح`, {
        timeout: 2000,
        position: "bottom-right",
        rtl: true,
      });
      loadSystems();
    })
    .catch(error => {
      console.error('Error toggling system:', error);
      toast.error('حدث خطأ أثناء تحديث حالة النظام', {
        timeout: 3000,
        position: "bottom-right",
        rtl: true,
      });
    });
}
</script>

<template>
  <Head title="إدارة الأنظمة المتصلة" />
  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
        إدارة الأنظمة المتصلة
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 text-gray-900 dark:text-gray-100">
            <div class="mb-4 flex justify-between items-center">
              <h3 class="text-lg font-semibold">قائمة الأنظمة المتصلة</h3>
              <button
                @click="openAddModal"
                class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
              >
                إضافة نظام جديد
              </button>
            </div>

            <div class="overflow-x-auto">
              <table class="w-full text-sm text-right text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                  <tr>
                    <th class="px-4 py-3">الاسم</th>
                    <th class="px-4 py-3">الدومين</th>
                    <th class="px-4 py-3">API Key</th>
                    <th class="px-4 py-3">الحالة</th>
                    <th class="px-4 py-3">تنفيذ</th>
                  </tr>
                </thead>
                <tbody>
                  <tr
                    v-for="system in systems"
                    :key="system.id"
                    class="bg-white border-b dark:bg-gray-800 dark:border-gray-700"
                  >
                    <td class="px-4 py-3">{{ system.name }}</td>
                    <td class="px-4 py-3">{{ system.domain }}</td>
                    <td class="px-4 py-3">
                      <code class="text-xs bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded">
                        {{ system.api_key.substring(0, 10) }}...
                      </code>
                    </td>
                    <td class="px-4 py-3">
                      <span
                        :class="system.is_active ? 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'"
                        class="px-2 py-1 text-xs font-semibold rounded"
                      >
                        {{ system.is_active ? 'مفعل' : 'معطل' }}
                      </span>
                    </td>
                    <td class="px-4 py-3">
                      <div class="flex gap-2">
                        <button
                          @click="toggleActive(system)"
                          :class="system.is_active ? 'bg-yellow-500 hover:bg-yellow-600' : 'bg-green-500 hover:bg-green-600'"
                          class="px-3 py-1 text-white text-sm rounded"
                        >
                          {{ system.is_active ? 'تعطيل' : 'تفعيل' }}
                        </button>
                        <button
                          @click="openEditModal(system)"
                          class="px-3 py-1 bg-blue-500 text-white text-sm rounded hover:bg-blue-600"
                        >
                          تعديل
                        </button>
                        <button
                          @click="deleteSystem(system)"
                          class="px-3 py-1 bg-red-500 text-white text-sm rounded hover:bg-red-600"
                        >
                          حذف
                        </button>
                      </div>
                    </td>
                  </tr>
                  <tr v-if="systems.length === 0">
                    <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                      لا توجد أنظمة متصلة
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal -->
    <div
      v-if="showModal"
      class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
      @click.self="closeModal"
    >
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
        <div class="mt-3">
          <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
            {{ editingSystem ? 'تعديل النظام' : 'إضافة نظام جديد' }}
          </h3>
          
          <div class="mb-4">
            <InputLabel for="name" value="اسم النظام" />
            <TextInput
              id="name"
              v-model="formData.name"
              type="text"
              class="mt-1 block w-full"
              placeholder="مثال: نظام أربيل"
            />
          </div>

          <div class="mb-4">
            <InputLabel for="domain" value="الدومين" />
            <TextInput
              id="domain"
              v-model="formData.domain"
              type="text"
              class="mt-1 block w-full"
              placeholder="https://system2.example.com"
            />
          </div>

          <div class="mb-4">
            <InputLabel for="api_key" value="API Key" />
            <TextInput
              id="api_key"
              v-model="formData.api_key"
              type="text"
              class="mt-1 block w-full"
              placeholder="alaa"
            />
          </div>

          <div class="mb-4">
            <label class="flex items-center">
              <input
                v-model="formData.is_active"
                type="checkbox"
                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
              />
              <span class="mr-2 text-sm text-gray-600 dark:text-gray-400">نشط</span>
            </label>
          </div>

          <div class="flex justify-end gap-2">
            <button
              @click="closeModal"
              class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600"
            >
              إلغاء
            </button>
            <button
              @click="saveSystem"
              class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
            >
              حفظ
            </button>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

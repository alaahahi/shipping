<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/inertia-vue3';
import debounce from 'lodash/debounce';
import { useToast } from 'vue-toastification';
import axios from 'axios';
import { ref, onMounted } from 'vue';
import TrashIcon from '@/Components/icon/trash.vue';

const toast = useToast();

const docs = ref([]);
const q = ref('');
const from = ref('');
const to = ref('');
const page = ref(1);
const lastPage = ref(1);
const loading = ref(false);
const selected = ref(null);
const viewLoading = ref(false);
const toDelete = ref(null);
const deleting = ref(false);

function formatDate(value) {
  if (!value) return '';
  return String(value).includes('T') ? String(value).split('T')[0] : String(value);
}

async function load(targetPage = 1) {
  loading.value = true;
  try {
    const { data } = await axios.get('/api/driving-authorizations', {
      params: {
        page: targetPage,
        per_page: 25,
        q: q.value || undefined,
        from: from.value || undefined,
        to: to.value || undefined,
      },
    });

    docs.value = data.data ?? [];
    page.value = data.meta?.current_page ?? targetPage;
    lastPage.value = data.meta?.last_page ?? 1;
  } catch (error) {
    toast.error(error?.response?.data?.message || 'تعذر جلب البيانات', {
      timeout: 3000,
      position: 'bottom-right',
      rtl: true,
    });
  } finally {
    loading.value = false;
  }
}

const debouncedSearch = debounce(() => load(1), 400);

function printDoc(doc) {
  window.open(doc.print_url, '_blank');
}

async function openView(doc) {
  selected.value = { ...doc, rendered_note: '' };
  viewLoading.value = true;
  try {
    const { data } = await axios.get(`/api/driving-authorizations/${doc.id}`);
    if (selected.value && selected.value.id === doc.id) {
      selected.value = { ...selected.value, ...(data.data ?? data) };
    }
  } catch (error) {
    toast.error(error?.response?.data?.message || 'تعذر جلب تفاصيل التخويل', {
      timeout: 3000,
      position: 'bottom-right',
      rtl: true,
    });
  } finally {
    viewLoading.value = false;
  }
}

async function confirmDelete() {
  if (!toDelete.value || deleting.value) return;

  deleting.value = true;
  try {
    const { data } = await axios.post('/api/driving-authorizations/delete', { id: toDelete.value.id });
    docs.value = docs.value.filter((d) => d.id !== toDelete.value.id);
    toDelete.value = null;
    toast.success(data?.message || 'تم حذف تخويل القيادة بنجاح', {
      timeout: 2500,
      position: 'bottom-right',
      rtl: true,
    });
  } catch (error) {
    toast.error(error?.response?.data?.message || 'تعذر حذف التخويل', {
      timeout: 3000,
      position: 'bottom-right',
      rtl: true,
    });
  } finally {
    deleting.value = false;
  }
}

function exportExcel() {
  const rows = [
    ['رقم', 'الاسم', 'نوع السيارة', 'رقم الشاصي', 'الموديل', 'اللون', 'رقم السيارة', 'التاريخ'],
    ...docs.value.map((d) => [
      d.id, d.name, d.car_type, d.vin, d.year, d.color, d.car_number, formatDate(d.created),
    ]),
  ];

  const csv = rows.map((r) => r.map((c) => `"${String(c ?? '').replace(/"/g, '""')}"`).join(',')).join('\n');
  const blob = new Blob([`\uFEFF${csv}`], { type: 'text/csv;charset=utf-8;' });
  const link = document.createElement('a');
  link.href = URL.createObjectURL(blob);
  link.download = 'driving-authorizations.csv';
  link.click();
  URL.revokeObjectURL(link.href);
}

onMounted(() => load(1));
</script>

<template>
  <Head title="تخويلات القيادة" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
        تخويلات القيادة
      </h2>
    </template>

    <div class="py-6" dir="rtl">
      <div class="max-w-8xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-900 overflow-hidden shadow-sm sm:rounded-lg p-4">
          <div class="flex flex-wrap items-end gap-3 mb-4">
            <label class="block">
              <span class="text-sm font-semibold dark:text-gray-200">بحث</span>
              <input
                v-model="q"
                type="text"
                class="mt-1 block rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600"
                placeholder="اسم / شاصي / نوع / رقم السيارة"
                @input="debouncedSearch"
              />
            </label>
            <label class="block">
              <span class="text-sm font-semibold dark:text-gray-200">من</span>
              <input
                v-model="from"
                type="date"
                class="mt-1 block rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600"
                @change="load(1)"
              />
            </label>
            <label class="block">
              <span class="text-sm font-semibold dark:text-gray-200">إلى</span>
              <input
                v-model="to"
                type="date"
                class="mt-1 block rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600"
                @change="load(1)"
              />
            </label>
            <button
              type="button"
              class="px-4 py-2 rounded-md bg-emerald-600 text-white font-semibold hover:bg-emerald-700"
              @click="exportExcel"
            >
              تصدير Excel
            </button>
          </div>

          <div class="overflow-x-auto">
            <table class="w-full text-sm text-right text-gray-600 dark:text-gray-300">
              <thead class="text-xs uppercase bg-gray-50 dark:bg-gray-800 dark:text-gray-400">
                <tr>
                  <th class="px-3 py-3">رقم</th>
                  <th class="px-3 py-3">الاسم</th>
                  <th class="px-3 py-3">نوع السيارة</th>
                  <th class="px-3 py-3">رقم الشاصي</th>
                  <th class="px-3 py-3">الموديل</th>
                  <th class="px-3 py-3">اللون</th>
                  <th class="px-3 py-3">رقم السيارة</th>
                  <th class="px-3 py-3">التاريخ</th>
                  <th class="px-3 py-3">إجراءات</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="doc in docs"
                  :key="doc.id"
                  class="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800/60 transition-colors"
                >
                  <td class="px-3 py-2">{{ doc.id }}</td>
                  <td class="px-3 py-2 font-semibold text-gray-800 dark:text-gray-100">{{ doc.name }}</td>
                  <td class="px-3 py-2">{{ doc.car_type }}</td>
                  <td class="px-3 py-2">{{ doc.vin }}</td>
                  <td class="px-3 py-2">{{ doc.year }}</td>
                  <td class="px-3 py-2">{{ doc.color }}</td>
                  <td class="px-3 py-2">{{ doc.car_number }}</td>
                  <td class="px-3 py-2">{{ formatDate(doc.created) }}</td>
                  <td class="px-3 py-2">
                    <div class="flex gap-2">
                      <button
                        type="button"
                        class="px-3 py-1 rounded bg-slate-600 text-white hover:bg-slate-700"
                        @click="openView(doc)"
                      >
                        عرض
                      </button>
                      <button
                        type="button"
                        class="px-3 py-1 rounded bg-rose-600 text-white hover:bg-rose-700"
                        @click="printDoc(doc)"
                      >
                        طباعة
                      </button>
                      <button
                        v-if="$page.props.auth.user.type_id == 1"
                        type="button"
                        class="px-3 py-1 rounded bg-red-700 text-white hover:bg-red-800 flex items-center"
                        title="حذف"
                        @click="toDelete = doc"
                      >
                        <TrashIcon />
                      </button>
                    </div>
                  </td>
                </tr>
                <tr v-if="!loading && !docs.length">
                  <td colspan="9" class="px-3 py-6 text-center text-gray-500">لا توجد بيانات</td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="flex items-center justify-between mt-4">
            <button
              type="button"
              class="px-3 py-2 rounded bg-gray-200 dark:bg-gray-700 dark:text-gray-200 disabled:opacity-50"
              :disabled="page <= 1 || loading"
              @click="load(page - 1)"
            >
              السابق
            </button>
            <span class="text-sm dark:text-gray-300">صفحة {{ page }} من {{ lastPage }}</span>
            <button
              type="button"
              class="px-3 py-2 rounded bg-gray-200 dark:bg-gray-700 dark:text-gray-200 disabled:opacity-50"
              :disabled="page >= lastPage || loading"
              @click="load(page + 1)"
            >
              التالي
            </button>
          </div>
        </div>
      </div>
    </div>

    <Transition name="modal">
      <div v-if="selected" class="modal-mask">
        <div class="modal-wrapper">
          <div
            class="modal-container dark:bg-gray-900 w-full max-w-full sm:max-w-2xl overflow-y-auto max-h-[85vh]"
            dir="rtl"
          >
            <div class="modal-header text-center py-4 dark:text-gray-200">
              تخويل قيادة رقم {{ selected.id }}
            </div>

            <div class="modal-body px-4 sm:px-5 space-y-4">
              <p v-if="viewLoading" class="text-center text-gray-500 py-4">جاري التحميل...</p>
              <p v-else class="leading-8 text-gray-700 dark:text-gray-200 whitespace-pre-line break-words">
                {{ selected.rendered_note }}
              </p>

              <table class="w-full text-sm text-right text-gray-600 dark:text-gray-300">
                <tbody>
                  <tr class="border-b dark:border-gray-700">
                    <td class="py-2">الاسم</td><td class="py-2 font-bold">{{ selected.name }}</td>
                  </tr>
                  <tr class="border-b dark:border-gray-700">
                    <td class="py-2">نوع السيارة</td><td class="py-2 font-bold">{{ selected.car_type }}</td>
                  </tr>
                  <tr class="border-b dark:border-gray-700">
                    <td class="py-2">رقم الشاصي</td><td class="py-2 font-bold">{{ selected.vin }}</td>
                  </tr>
                  <tr class="border-b dark:border-gray-700">
                    <td class="py-2">الموديل</td><td class="py-2 font-bold">{{ selected.year }}</td>
                  </tr>
                  <tr class="border-b dark:border-gray-700">
                    <td class="py-2">اللون</td><td class="py-2 font-bold">{{ selected.color }}</td>
                  </tr>
                  <tr class="border-b dark:border-gray-700">
                    <td class="py-2">رقم السيارة</td><td class="py-2 font-bold">{{ selected.car_number }}</td>
                  </tr>
                  <tr>
                    <td class="py-2">التاريخ</td><td class="py-2 font-bold">{{ formatDate(selected.created) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div class="modal-footer my-2 px-3">
              <div class="flex flex-row w-full">
                <div class="basis-1/2 px-2">
                  <button type="button" class="w-full py-3 bg-gray-500 text-white rounded" @click="selected = null">
                    إغلاق
                  </button>
                </div>
                <div class="basis-1/2 px-2">
                  <button
                    type="button"
                    class="w-full py-3 bg-rose-600 text-white rounded"
                    @click="printDoc(selected)"
                  >
                    طباعة
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </Transition>

    <Transition name="modal">
      <div v-if="toDelete" class="modal-mask">
        <div class="modal-wrapper">
          <div class="modal-container dark:bg-gray-900" dir="rtl">
            <div class="modal-header text-center py-4 dark:text-gray-200">
              حذف تخويل القيادة
            </div>

            <div class="modal-body px-5 text-center dark:text-gray-200">
              <p>
                هل أنت متأكد من حذف تخويل القيادة رقم {{ toDelete.id }} الخاص بـ
                <span class="font-bold">{{ toDelete.name }}</span>؟
              </p>
              <p class="text-sm text-gray-500 mt-2">الحذف قابل للاسترجاع ويتم تسجيله في سجل النظام.</p>
            </div>

            <div class="modal-footer my-2 px-3">
              <div class="flex flex-row w-full">
                <div class="basis-1/2 px-2">
                  <button
                    type="button"
                    class="w-full py-3 bg-gray-500 text-white rounded"
                    :disabled="deleting"
                    @click="toDelete = null"
                  >
                    إغلاق
                  </button>
                </div>
                <div class="basis-1/2 px-2">
                  <button
                    type="button"
                    class="w-full py-3 bg-rose-600 text-white rounded disabled:opacity-50"
                    :disabled="deleting"
                    @click="confirmDelete"
                  >
                    حذف
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </AuthenticatedLayout>
</template>

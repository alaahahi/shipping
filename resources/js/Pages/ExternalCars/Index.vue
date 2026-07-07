<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/inertia-vue3';
import ModalExternalCarForm from '@/Components/ModalExternalCarForm.vue';
import ModalDelCar from '@/Components/ModalDelCar.vue';
import edit from '@/Components/icon/edit.vue';
import trash from '@/Components/icon/trash.vue';
import InfiniteLoading from 'v3-infinite-loading';
import 'v3-infinite-loading/lib/style.css';
import debounce from 'lodash/debounce';
import { useToast } from 'vue-toastification';
import axios from 'axios';
import { ref } from 'vue';

const toast = useToast();

const cars = ref([]);
const q = ref('');
const from = ref('');
const to = ref('');
const page = ref(1);
const resetData = ref(0);
const totalPaidDollar = ref(0);
const totalPaidDinar = ref(0);
const showFormModal = ref(false);
const showDelModal = ref(false);
const formData = ref({});

function getTodayDate() {
  const today = new Date();
  const year = today.getFullYear();
  const month = String(today.getMonth() + 1).padStart(2, '0');
  const day = String(today.getDate()).padStart(2, '0');
  return `${year}-${month}-${day}`;
}

function emptyForm() {
  return {
    dealer_name: '',
    vin: '',
    car_type: '',
    year: '',
    car_color: '',
    car_number: '',
    paid_dollar: 0,
    paid_dinar: 0,
    note: '',
    date: getTodayDate(),
  };
}

function formatNumber(value) {
  return new Intl.NumberFormat('en-US').format(Number(value) || 0);
}

function formatDate(value) {
  if (!value) return '';
  return String(value).includes('T') ? String(value).split('T')[0] : String(value);
}

function refresh() {
  page.value = 1;
  cars.value = [];
  resetData.value += 1;
}

const debouncedRefresh = debounce(refresh, 400);

function openAddModal() {
  formData.value = emptyForm();
  showFormModal.value = true;
}

function openEditModal(car) {
  formData.value = {
    id: car.id,
    dealer_name: car.dealer_name ?? '',
    vin: car.vin ?? '',
    car_type: car.car_type ?? '',
    year: car.year ?? '',
    car_color: car.car_color ?? '',
    car_number: car.car_number ?? '',
    paid_dollar: car.paid_dollar ?? 0,
    paid_dinar: car.paid_dinar ?? 0,
    note: car.note ?? '',
    date: formatDate(car.date) || getTodayDate(),
  };
  showFormModal.value = true;
}

function openDeleteModal(car) {
  formData.value = { id: car.id };
  showDelModal.value = true;
}

async function getResults($state) {
  try {
    const response = await axios.get('/api/getIndexExternalCars', {
      params: {
        limit: 50,
        page: page.value,
        q: q.value || undefined,
        from: from.value || undefined,
        to: to.value || undefined,
      },
    });

    const json = response.data;
    totalPaidDollar.value = json.total_paid_dollar ?? 0;
    totalPaidDinar.value = json.total_paid_dinar ?? 0;

    const batch = json.data ?? [];
    if (batch.length < 50) {
      cars.value.push(...batch);
      $state.complete();
    } else {
      cars.value.push(...batch);
      $state.loaded();
    }

    page.value += 1;
  } catch (error) {
    toast.error(error?.response?.data?.error || 'تعذر جلب البيانات', {
      timeout: 3000,
      position: 'bottom-right',
      rtl: true,
    });
    $state.complete();
  }
}

function saveCar(payload) {
  const url = payload.id ? '/api/updateExternalCar' : '/api/storeExternalCar';

  axios.post(url, payload)
    .then(() => {
      showFormModal.value = false;
      toast.success(payload.id ? 'تم التعديل بنجاح' : 'تمت الإضافة بنجاح', {
        timeout: 2500,
        position: 'bottom-right',
        rtl: true,
      });
      refresh();
    })
    .catch((error) => {
      const msg = error?.response?.data?.message
        || error?.response?.data?.error
        || 'تعذر الحفظ';
      toast.error(msg, { timeout: 3000, position: 'bottom-right', rtl: true });
    });
}

function confirmDelete(payload) {
  axios.post('/api/deleteExternalCar', payload)
    .then(() => {
      showDelModal.value = false;
      toast.success('تم الحذف بنجاح', { timeout: 2500, position: 'bottom-right', rtl: true });
      refresh();
    })
    .catch((error) => {
      toast.error(error?.response?.data?.error || 'تعذر الحذف', {
        timeout: 3000,
        position: 'bottom-right',
        rtl: true,
      });
    });
}
</script>

<template>
  <Head title="السيارات الخارجية" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
        السيارات الخارجية
      </h2>
    </template>

    <div class="py-6">
      <div class="max-w-8xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-900 overflow-hidden shadow-sm sm:rounded-lg p-4">
          <div class="flex flex-wrap items-end gap-3 mb-4">
            <label class="block">
              <span class="text-sm font-semibold dark:text-gray-200">بحث</span>
              <input
                v-model="q"
                type="text"
                class="mt-1 block rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600"
                placeholder="تاجر / نوع / رقم"
                @input="debouncedRefresh"
              />
            </label>
            <label class="block">
              <span class="text-sm font-semibold dark:text-gray-200">من</span>
              <input
                v-model="from"
                type="date"
                class="mt-1 block rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600"
                @change="refresh"
              />
            </label>
            <label class="block">
              <span class="text-sm font-semibold dark:text-gray-200">إلى</span>
              <input
                v-model="to"
                type="date"
                class="mt-1 block rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600"
                @change="refresh"
              />
            </label>
            <button
              type="button"
              class="px-4 py-2 rounded-md bg-emerald-600 text-white font-semibold hover:bg-emerald-700"
              @click="openAddModal"
            >
              + إضافة سيارة خارجية
            </button>
          </div>

          <div class="grid grid-cols-2 gap-3 mb-4">
            <div class="rounded-lg border border-blue-200 dark:border-blue-800 bg-blue-50 dark:bg-blue-950/40 p-3 text-center font-bold">
              مجموع المدفوع دولار: {{ formatNumber(totalPaidDollar) }} $
            </div>
            <div class="rounded-lg border border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-950/40 p-3 text-center font-bold">
              مجموع المدفوع دينار: {{ formatNumber(totalPaidDinar) }} د
            </div>
          </div>

          <div class="overflow-x-auto">
            <table class="w-full text-sm border-collapse">
              <thead>
                <tr class="bg-gray-100 dark:bg-gray-800">
                  <th class="border dark:border-gray-700 px-2 py-2">التاريخ</th>
                  <th class="border dark:border-gray-700 px-2 py-2">شانصي</th>
                  <th class="border dark:border-gray-700 px-2 py-2">تاجر</th>
                  <th class="border dark:border-gray-700 px-2 py-2">نوع السيارة</th>
                  <th class="border dark:border-gray-700 px-2 py-2">السنة</th>
                  <th class="border dark:border-gray-700 px-2 py-2">اللون</th>
                  <th class="border dark:border-gray-700 px-2 py-2">الرقم</th>
                  <th class="border dark:border-gray-700 px-2 py-2">مدفوع $</th>
                  <th class="border dark:border-gray-700 px-2 py-2">مدفوع د</th>
                  <th class="border dark:border-gray-700 px-2 py-2">إجراءات</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="car in cars" :key="car.id" class="hover:bg-gray-50 dark:hover:bg-gray-800/60">
                  <td class="border dark:border-gray-700 px-2 py-2 text-center">{{ formatDate(car.date) }}</td>
                  <td class="border dark:border-gray-700 px-2 py-2 text-center font-mono text-xs" dir="ltr">{{ car.vin || '-' }}</td>
                  <td class="border dark:border-gray-700 px-2 py-2 text-center">{{ car.dealer_name }}</td>
                  <td class="border dark:border-gray-700 px-2 py-2 text-center">{{ car.car_type }}</td>
                  <td class="border dark:border-gray-700 px-2 py-2 text-center">{{ car.year || '-' }}</td>
                  <td class="border dark:border-gray-700 px-2 py-2 text-center">{{ car.car_color || '-' }}</td>
                  <td class="border dark:border-gray-700 px-2 py-2 text-center">{{ car.car_number }}</td>
                  <td class="border dark:border-gray-700 px-2 py-2 text-center">{{ formatNumber(car.paid_dollar) }}</td>
                  <td class="border dark:border-gray-700 px-2 py-2 text-center">{{ formatNumber(car.paid_dinar) }}</td>
                  <td class="border dark:border-gray-700 px-2 py-2 text-center">
                    <div class="flex justify-center gap-2">
                      <button type="button" class="text-blue-600" @click="openEditModal(car)">
                        <edit />
                      </button>
                      <button type="button" class="text-red-600" @click="openDeleteModal(car)">
                        <trash />
                      </button>
                    </div>
                  </td>
                </tr>
                <tr v-if="cars.length === 0">
                  <td colspan="10" class="border dark:border-gray-700 px-2 py-6 text-center text-gray-500">
                    لا توجد سيارات خارجية
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <InfiniteLoading :key="resetData" @infinite="getResults" />
        </div>
      </div>
    </div>

    <ModalExternalCarForm
      :show="showFormModal"
      :form-data="formData"
      @save="saveCar"
      @close="showFormModal = false"
    />

    <ModalDelCar
      :show="showDelModal"
      :form-data="formData"
      @a="confirmDelete"
      @close="showDelModal = false"
    />
  </AuthenticatedLayout>
</template>

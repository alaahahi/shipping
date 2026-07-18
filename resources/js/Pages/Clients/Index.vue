<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ModalAddClient from '@/Components/ModalAddClient.vue';
import ModalEditClient from '@/Components/ModalEditClient.vue';
import ModalDelClient from '@/Components/ModalDelCar.vue';
import UiSwitch from '@/Components/UiSwitch.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import show from '@/Components/icon/show.vue';
import wallet from '@/Components/icon/wallet.vue';
import trash from '@/Components/icon/trash.vue';
import edit from '@/Components/icon/edit.vue';
import { Head, Link } from '@inertiajs/inertia-vue3';
import InfiniteLoading from 'v3-infinite-loading';
import 'v3-infinite-loading/lib/style.css';
import { ref, watch } from 'vue';
import debounce from 'lodash/debounce';
import axios from 'axios';
import { useToast } from 'vue-toastification';

const toast = useToast();

const showModalAddTrader = ref(false);
const showModalAddBox = ref(false);
const showModalEditClient = ref(false);
const showModalDelClient = ref(false);
const saving = ref(false);
const togglingId = ref(null);

const laravelData = ref([]);
const formData = ref({});
const from = ref('');
const to = ref('');
const q = ref('');
const category = ref('0');
const resetData = ref(false);
let page = 1;
let controller = new AbortController();

const refresh = () => {
  page = 1;
  laravelData.value = [];
  resetData.value = !resetData.value;
};

const apiQuery = () => (category.value && category.value !== '0' ? category.value : q.value);

const getResultsCar = async ($state) => {
  try {
    const response = await axios.get('api/getIndexClients', {
      params: {
        limit: 25,
        page,
        q: apiQuery(),
        from: from.value || 0,
        to: to.value || 0,
      },
      signal: controller.signal,
    });

    const json = response.data;
    const rows = json?.data || [];

    if (rows.length < 25) {
      laravelData.value.push(...rows);
      $state.complete();
    } else {
      laravelData.value.push(...rows);
      $state.loaded();
    }

    page += 1;
  } catch (error) {
    if (error?.name !== 'CanceledError' && error?.code !== 'ERR_CANCELED') {
      console.error(error);
      $state.error();
    }
  }
};

const abortRequest = () => {
  if (controller) controller.abort();
  controller = new AbortController();
};

const debouncedRefresh = debounce(() => {
  abortRequest();
  refresh();
}, 400);

watch([q, category, from, to], () => {
  debouncedRefresh();
});

function openModalAddTrader() {
  showModalAddTrader.value = true;
}

function openModalAddBox() {
  showModalAddBox.value = true;
}

function openModalEditClient(form = {}) {
  formData.value = { ...form };
  showModalEditClient.value = true;
}

function openModalDelClient(form = {}) {
  formData.value = form;
  showModalDelClient.value = true;
}

async function confirmAddClient(payload) {
  saving.value = true;
  try {
    await axios.post('/api/clientsStore', {
      name: payload.name,
      phone: payload.phone,
      show_in_dashboard: !!payload.show_in_dashboard,
    });
    showModalAddTrader.value = false;
    showModalAddBox.value = false;
    toast.success(payload.mode === 'box' ? 'تم إضافة القاسة' : 'تم إضافة التاجر', {
      timeout: 2200,
      position: 'bottom-right',
      rtl: true,
    });
    refresh();
  } catch (error) {
    const msg =
      error?.response?.data?.errors?.name?.[0] ||
      error?.response?.data?.message ||
      'فشل إضافة الحساب';
    toast.error(msg, { timeout: 3000, position: 'bottom-right', rtl: true });
  } finally {
    saving.value = false;
  }
}

async function confirmEditClient(payload) {
  saving.value = true;
  try {
    await axios.post('/api/clientsEdit', payload);
    showModalEditClient.value = false;
    toast.success('تم حفظ التعديلات', {
      timeout: 2200,
      position: 'bottom-right',
      rtl: true,
    });
    refresh();
  } catch (error) {
    toast.error(error?.response?.data?.message || 'فشل حفظ التعديلات', {
      timeout: 3000,
      position: 'bottom-right',
      rtl: true,
    });
  } finally {
    saving.value = false;
  }
}

async function confirmDelClient(payload) {
  try {
    await axios.post('/api/delClient', payload);
    showModalDelClient.value = false;
    toast.success('تم الحذف', { timeout: 2000, position: 'bottom-right', rtl: true });
    refresh();
  } catch (error) {
    toast.error('فشل الحذف', { timeout: 3000, position: 'bottom-right', rtl: true });
  }
}

const isFlagOn = (value) => value === true || value === 1 || value === '1';

async function toggleInternalSalesQuick(user, nextValue = null) {
  if (!user?.id || togglingId.value === `sales-${user.id}`) return;

  const next = nextValue === null ? !isFlagOn(user.has_internal_sales) : !!nextValue;
  const prev = user.has_internal_sales;
  user.has_internal_sales = next;
  togglingId.value = `sales-${user.id}`;

  try {
    const response = await axios.post('/api/toggleInternalSales', {
      client_id: user.id,
      has_internal_sales: next,
    });
    user.has_internal_sales = isFlagOn(response.data.has_internal_sales);
    toast.success('تم تحديث حالة المبيعات الداخلية', {
      timeout: 1800,
      position: 'bottom-right',
      rtl: true,
    });
  } catch (error) {
    user.has_internal_sales = prev;
    toast.error(error?.response?.data?.message || 'فشل تحديث المبيعات الداخلية', {
      timeout: 3000,
      position: 'bottom-right',
      rtl: true,
    });
  } finally {
    togglingId.value = null;
  }
}

async function toggleShowInDashboardQuick(user, nextValue = null) {
  if (!user?.id || togglingId.value === `dash-${user.id}`) return;

  const next = nextValue === null ? !isFlagOn(user.show_in_dashboard) : !!nextValue;
  const prev = user.show_in_dashboard;
  user.show_in_dashboard = next;
  togglingId.value = `dash-${user.id}`;

  try {
    const response = await axios.post('/api/toggleShowInDashboard', {
      client_id: user.id,
      show_in_dashboard: next,
    });
    user.show_in_dashboard = isFlagOn(response.data.show_in_dashboard);
    toast.success('تم تحديث العرض في لوحة التحكم', {
      timeout: 1800,
      position: 'bottom-right',
      rtl: true,
    });
  } catch (error) {
    user.show_in_dashboard = prev;
    toast.error(error?.response?.data?.message || 'فشل تحديث العرض في لوحة التحكم', {
      timeout: 3000,
      position: 'bottom-right',
      rtl: true,
    });
  } finally {
    togglingId.value = null;
  }
}

const printUrl = () =>
  `api/getIndexClients?from=${from.value || 0}&to=${to.value || 0}&print=1&q=${encodeURIComponent(apiQuery() || '')}`;
</script>

<template>
  <Head title="التجار" />
  <AuthenticatedLayout>
    <ModalAddClient
      :show="showModalAddTrader"
      mode="trader"
      @confirm="confirmAddClient"
      @close="showModalAddTrader = false"
    />

    <ModalAddClient
      :show="showModalAddBox"
      mode="box"
      @confirm="confirmAddClient"
      @close="showModalAddBox = false"
    />

    <ModalEditClient
      :show="showModalEditClient"
      :form-data="formData"
      @confirm="confirmEditClient"
      @close="showModalEditClient = false"
    />

    <ModalDelClient
      :show="!!showModalDelClient"
      :form-data="formData"
      @a="confirmDelClient($event)"
      @close="showModalDelClient = false"
    >
      <template #header>
        <h2 class="mb-5 text-center dark:text-white">
          هل متأكد من حذف التاجر {{ formData.name }}؟
        </h2>
      </template>
    </ModalDelClient>

    <template #header>
      <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
          <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
            التجار والقاسات
          </h2>
          <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            إدارة التجار والقاسات مع خيار العرض في لوحة التحكم
          </p>
        </div>
        <div class="flex flex-wrap gap-2">
          <button
            type="button"
            class="rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-rose-700"
            @click="openModalAddTrader"
          >
            إضافة تاجر
          </button>
          <button
            type="button"
            class="rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-amber-700"
            @click="openModalAddBox"
          >
            إضافة قاسة
          </button>
        </div>
      </div>
    </template>

    <div class="py-8">
      <div class="mx-auto sm:px-6 lg:px-8">
        <div class="overflow-hidden rounded-xl bg-white shadow-sm dark:bg-gray-900">
          <div class="border-b border-gray-100 p-4 dark:border-gray-800 md:p-6">
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-6">
              <div class="lg:col-span-2">
                <InputLabel for="simple-search" value="بحث" class="mb-1" />
                <div class="relative">
                  <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                    <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                      <path
                        fill-rule="evenodd"
                        d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                        clip-rule="evenodd"
                      />
                    </svg>
                  </div>
                  <input
                    id="simple-search"
                    v-model="q"
                    type="text"
                    placeholder="اسم أو هاتف..."
                    class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 pl-10 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                  />
                </div>
              </div>

              <div>
                <InputLabel value="تحديد الفئة" class="mb-1" />
                <select
                  v-model="category"
                  class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-red-500 focus:ring-red-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                >
                  <option value="0">{{ $t('allOwners') }}</option>
                  <option value="debit">يوجد دين</option>
                  <option value="box_movement">حركة على القاسة</option>
                </select>
              </div>

              <div>
                <InputLabel for="from" :value="$t('from_date')" class="mb-1" />
                <TextInput id="from" v-model="from" type="date" class="mt-0 block w-full" />
              </div>

              <div>
                <InputLabel for="to" :value="$t('to_date')" class="mb-1" />
                <TextInput id="to" v-model="to" type="date" class="mt-0 block w-full" />
              </div>

              <div>
                <InputLabel value="طباعة" class="mb-1" />
                <a
                  :href="printUrl()"
                  target="_blank"
                  class="inline-flex w-full items-center justify-center rounded-lg bg-orange-500 px-4 py-2.5 text-sm font-bold text-white hover:bg-orange-600"
                >
                  طباعة
                </a>
              </div>
            </div>
          </div>

          <div class="relative overflow-x-auto">
            <table class="w-full text-center text-sm">
              <thead class="bg-slate-200 text-xs uppercase text-slate-800 dark:bg-slate-800 dark:text-slate-100">
                <tr>
                  <th class="px-2 py-3">#</th>
                  <th class="px-2 py-3">{{ $t('name') }}</th>
                  <th class="px-2 py-3">{{ $t('phoneNumber') }}</th>
                  <th class="px-2 py-3">{{ $t('debt') }}</th>
                  <th class="px-2 py-3">المبيعات الداخلية</th>
                  <th class="px-2 py-3">عرض في لوحة التحكم</th>
                  <th class="px-2 py-3">{{ $t('execute') }}</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="(user, i) in laravelData"
                  :key="user?.id || i"
                  class="border-b border-slate-200 dark:border-slate-700"
                  :class="
                    Number(user.balance) <= 0
                      ? 'bg-emerald-100 text-slate-900 dark:bg-emerald-900 dark:text-emerald-50'
                      : 'bg-rose-100 text-slate-900 dark:bg-rose-900 dark:text-rose-50'
                  "
                >
                  <template v-if="user?.id">
                    <td class="px-2 py-2 font-medium">{{ i + 1 }}</td>
                    <td class="px-2 py-2 text-base font-extrabold text-slate-900 dark:text-white">
                      {{ user.name }}
                    </td>
                    <td class="px-2 py-2 font-medium text-slate-800 dark:text-slate-100">
                      {{ user.phone || '—' }}
                    </td>
                    <td class="px-2 py-2 font-bold text-slate-900 dark:text-white">
                      {{ user.balance }} $
                    </td>
                    <td class="px-2 py-2">
                      <div class="inline-flex flex-col items-center gap-1">
                        <UiSwitch
                          :model-value="user.has_internal_sales"
                          on-color="bg-emerald-500"
                          :disabled="togglingId === `sales-${user.id}`"
                          @change="(v) => toggleInternalSalesQuick(user, v)"
                        />
                        <span class="text-[11px] font-semibold">
                          {{ isFlagOn(user.has_internal_sales) ? 'مفعل' : 'معطل' }}
                        </span>
                      </div>
                    </td>
                    <td class="px-2 py-2">
                      <div class="inline-flex flex-col items-center gap-1">
                        <UiSwitch
                          :model-value="user.show_in_dashboard"
                          on-color="bg-orange-500"
                          :disabled="togglingId === `dash-${user.id}`"
                          @change="(v) => toggleShowInDashboardQuick(user, v)"
                        />
                        <span class="text-[11px] font-semibold">
                          {{ isFlagOn(user.show_in_dashboard) ? 'مفعل' : 'معطل' }}
                        </span>
                      </div>
                    </td>
                    <td class="px-2 py-2">
                      <div class="inline-flex flex-wrap items-center justify-center gap-1">
                        <Link
                          class="inline-flex rounded bg-blue-500 px-1 py-1 text-white"
                          :href="route('showClients', user.id)"
                        >
                          <show />
                        </Link>
                        <button
                          type="button"
                          class="rounded bg-slate-500 px-1 py-1 text-white"
                          @click="openModalEditClient(user)"
                        >
                          <edit />
                        </button>
                        <button
                          v-if="Number(user.balance) === 0"
                          type="button"
                          class="rounded bg-orange-500 px-1 py-1 text-white"
                          @click="openModalDelClient(user)"
                        >
                          <trash />
                        </button>
                        <Link
                          class="inline-flex rounded bg-purple-900 px-1 py-1 text-white"
                          :href="route('wallet', { id: user.id })"
                        >
                          <wallet />
                        </Link>
                        <Link
                          v-if="user.has_internal_sales"
                          class="inline-flex rounded bg-purple-600 px-2 py-1 text-white"
                          :href="`/internalSales/${user.id}`"
                          title="المبيعات الداخلية"
                        >
                          💰
                        </Link>
                      </div>
                    </td>
                  </template>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="mt-3 p-4 text-center" style="direction: ltr">
            <InfiniteLoading
              :laravel-data="laravelData"
              :identifier="resetData"
              @infinite="getResultsCar"
            />
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

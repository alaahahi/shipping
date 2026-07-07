<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/inertia-vue3';
import ModalAddCarExpensesFav from "@/Components/ModalAddCarExpensesFav.vue";
import ModalAddCarExpenses from "@/Components/ModalAddCarExpenses.vue";
import ModalArchiveCar from "@/Components/ModalArchiveCar.vue";
import ModalArchiveCarBack from "@/Components/ModalArchiveCarBack.vue";
import ModalArchiveCarLink from "@/Components/ModalArchiveCarLink.vue";
import ModalUnlinkCar from "@/Components/ModalUnlinkCar.vue";
import ModalDelCar from "@/Components/ModalDelCar.vue";
import ModalExternalCarForm from "@/Components/ModalExternalCarForm.vue";


import { useToast } from "vue-toastification";
import axios from 'axios';
import { ref, computed } from 'vue';
import { useI18n } from "vue-i18n";
import newContracts from "@/Components/icon/new.vue";
import show from "@/Components/icon/show.vue";
import trash from "@/Components/icon/trash.vue";
import edit from "@/Components/icon/edit.vue";
import print from "@/Components/icon/print.vue";

import InfiniteLoading from "v3-infinite-loading";
import "v3-infinite-loading/lib/style.css";
import debounce from 'lodash/debounce';
const {t} = useI18n();
const props = defineProps({
  client:Array,
});
const formData = ref({});
const toast = useToast();
let searchTerm = ref('');
let showModalAddCarExpensesFav =  ref(false);
let showModalAddCarExpenses =  ref(false);
let showModalArchiveCarExpenses=  ref(false);
let showModalArchiveCarExpensesBack=  ref(false);
let showModalArchiveCarLink = ref(false);
let showModalUnlinkCar = ref(false);
let showModalDelCar = ref(false);
let showModalExternalCarForm = ref(false);
let externalDeleteMode = ref(false);

let car = ref([]);
function openwModalAddCarExpensesFav(form={}) {
  showModalAddCarExpensesFav.value = true;
}
function openwshowModalAddCarExpenses(form={}) {
  formData.value=form
  showModalAddCarExpenses.value = true;
}
function openwshowModalArchiveCarExpenses(form={}) {
  formData.value=form
  showModalArchiveCarExpenses.value = true;
}
function openwshowModalArchiveCarExpensesBack(form={}) {
  formData.value=form
  showModalArchiveCarExpensesBack.value = true;
}
function openModalArchiveCarLink(form={}) {
  formData.value = form;
  showModalArchiveCarLink.value = true;
}

const listTab = ref('work');
const isSubmittingExpense = ref(false);
const isArchivingAll = ref(false);
const isLinkingCar = ref(false);
const isUnlinkingCar = ref(false);
const unlinkCarPending = ref(null);
let resetData = ref(false);
let user_id = 0;
let page = 1;
let q = '';

function carHaveExpensesParam() {
  if (listTab.value === 'work') return 1;
  if (listTab.value === 'archive') return 2;
  if (listTab.value === 'linked') return 4;
  return 0;
}

const isWorkTab = computed(() => listTab.value === 'work');
const isArchiveTab = computed(() => listTab.value === 'archive');
const isLinkedTab = computed(() => listTab.value === 'linked');
const isExternalTab = computed(() => listTab.value === 'external');
const expensesTotalDollar = ref(0);
const expensesTotalDinar = ref(0);
const externalTotalPaidDollar = ref(0);
const externalTotalPaidDinar = ref(0);

function formatNumber(n) {
  return new Intl.NumberFormat('en-US').format(Number(n) || 0);
}

const refresh = () => {
  page = 1;
  car.value = [];
  resetData.value = !resetData.value;
};
const debouncedGetResultsCar = debounce(refresh, 500); // Adjust the debounce delay (in milliseconds) as needed


const getResultsCar = async ($state) => {
  try {
    if (isExternalTab.value) {
      const response = await axios.get('/api/getIndexExternalCars', {
        params: {
          limit: 50,
          page,
          q: q || undefined,
        },
      });

      const json = response.data;
      externalTotalPaidDollar.value = Number(json.total_paid_dollar) || 0;
      externalTotalPaidDinar.value = Number(json.total_paid_dinar) || 0;

      const rows = Array.isArray(json?.data) ? json.data : [];
      if (rows.length < 50) {
        car.value.push(...rows);
        $state.complete();
      } else {
        car.value.push(...rows);
        $state.loaded();
      }

      page++;
      return;
    }

    const response = await axios.get(`/getIndexCar`, {
      params: {
        limit: 100,
        page: page,
        q: q,
        user_id: user_id,
        car_have_expenses: carHaveExpensesParam()
      }
    });

    const json = response.data;
    const rows = Array.isArray(json?.data) ? json.data : [];

    if (isWorkTab.value) {
      expensesTotalDollar.value = Number(json.expensesTotalDollar) || 0;
      expensesTotalDinar.value = Number(json.expensesTotalDinar) || 0;
    }

    if (rows.length < 100) {
      car.value.push(...rows);
      $state.complete();
    } else {
      car.value.push(...rows);
      $state.loaded();
    }

    page++;
  } catch (error) {
    console.error(error);
    $state.error();
  }
};

 

function confirmExpensesCar(V) {
  if (isSubmittingExpense.value) return;
  isSubmittingExpense.value = true;

  axios.post('/api/confirmExpensesCar', {
    id: V.id,
    amountDollar: Number(V.amountDollar) || 0,
    amountDinar: Number(V.amountDinar) || 0,
    amountNote: V.amountNote || '',
  })
  .then(() => {
    showModalAddCarExpenses.value = false;
    toast.success("تم إضافة الدفعة بنجاح", {
        timeout: 3000,
        position: "bottom-right",
        rtl: true
      });
    refresh();
  })
  .catch((error) => {
    console.error(error);
  })
  .finally(() => {
    isSubmittingExpense.value = false;
  });
}


function getTodayDate() {
  const today = new Date();
  const year = today.getFullYear();
  const month = String(today.getMonth() + 1).padStart(2, '0');
  const day = String(today.getDate()).padStart(2, '0');
  return `${year}-${month}-${day}`;
}
function  calculateSum(carexpenses) {
      return (carexpenses ?? []).reduce((sum, expense) => sum + (Number(expense.amount_dollar) || 0), 0);
    }
function  calculateSumDinar(carexpenses) {
      return (carexpenses ?? []).reduce((sum, expense) => sum + (Number(expense.amount_dinar) || 0), 0);
    }

const summaryPrintUrl = computed(() => {
  const params = new URLSearchParams();
  if (user_id) params.set('user_id', user_id);
  if (q) params.set('q', q);
  const query = params.toString();
  return `/api/getIndexExpensesSummaryPrint${query ? `?${query}` : ''}`;
});

    function confirmCar (car){
  axios.post('/api/addCarFavorite',car)
  .then(response => {
    showModalAddCarExpenses.value = false;
    toast.success( "تم إضافة السيارة بنجاح ", {
        timeout: 3000,
        position: "bottom-right",
        rtl: true

      });
      refresh()
      showModalAddCarExpensesFav.value = false;

  })
  .catch(error => {
    const msg = error?.response?.data?.error || 'تعذر إضافة السيارة';
    toast.error(msg, { timeout: 3500, position: 'bottom-right', rtl: true });
  })
}

function confirmArchiveCar(car){
  axios.post('/api/confirmArchiveCar',car)
  .then(response => {
    showModalAddCarExpenses.value = false;
    toast.success( "تم نقل السيارة بنجاح ", {
        timeout: 3000,
        position: "bottom-right",
        rtl: true

      });
      refresh()
      showModalArchiveCarExpenses.value = false;

  })
  .catch(error => {
    console.error(error);
  })
}
function confirmArchiveCarBack(car){
  axios.post('/api/confirmArchiveCarBack',car)
  .then(response => {
    showModalArchiveCarExpensesBack.value = false;
    toast.success( "تم نقل السيارة بنجاح ", {
        timeout: 3000,
        position: "bottom-right",
        rtl: true

      });
      refresh()
      showModalArchiveCarExpenses.value = false;

  })
  .catch(error => {
    console.error(error);
  })
}

function confirmLinkArchiveCar(payload) {
  if (isLinkingCar.value) return;
  isLinkingCar.value = true;

  axios.post('/api/confirmLinkArchiveCar', payload)
    .then(() => {
      showModalArchiveCarLink.value = false;
      toast.success('تم ربط السيارة وإضافة المصاريف بنجاح', {
        timeout: 3000,
        position: 'bottom-right',
        rtl: true,
      });
      refresh();
    })
    .catch((error) => {
      const msg = error?.response?.data?.error || 'فشل الربط';
      toast.error(msg, { timeout: 4000, position: 'bottom-right', rtl: true });
    })
    .finally(() => {
      isLinkingCar.value = false;
    });
}

function confirmArchiveAllCars() {
  if (isArchivingAll.value) return;
  const confirmed = window.confirm('هل تريد أرشفة جميع السيارات في قيد العمل (حسب الفلتر الحالي)؟');
  if (!confirmed) return;

  isArchivingAll.value = true;
  axios.post('/api/confirmArchiveAllCars', { user_id, q })
    .then((response) => {
      const count = response.data?.count ?? 0;
      toast.success(`تم أرشفة ${count} سيارة`, {
        timeout: 3000,
        position: 'bottom-right',
        rtl: true,
      });
      refresh();
    })
    .catch((error) => {
      const msg = error?.response?.data?.error || 'فشل أرشفة الكل';
      toast.error(msg, { timeout: 4000, position: 'bottom-right', rtl: true });
    })
    .finally(() => {
      isArchivingAll.value = false;
    });
}
function swiptab(tab){
  listTab.value = tab
  expensesTotalDollar.value = 0
  expensesTotalDinar.value = 0
  externalTotalPaidDollar.value = 0
  externalTotalPaidDinar.value = 0
  refresh()
}

function emptyExternalForm() {
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

function formatExternalDate(value) {
  if (!value) return '';
  return String(value).includes('T') ? String(value).split('T')[0] : String(value);
}

function openExternalCarForm(row = null) {
  if (row) {
    formData.value = {
      id: row.id,
      dealer_name: row.dealer_name ?? '',
      vin: row.vin ?? '',
      car_type: row.car_type ?? '',
      year: row.year ?? '',
      car_color: row.car_color ?? '',
      car_number: row.car_number ?? '',
      paid_dollar: row.paid_dollar ?? 0,
      paid_dinar: row.paid_dinar ?? 0,
      note: row.note ?? '',
      date: formatExternalDate(row.date) || getTodayDate(),
    };
  } else {
    formData.value = emptyExternalForm();
  }
  showModalExternalCarForm.value = true;
}

function saveExternalCar(payload) {
  const url = payload.id ? '/api/updateExternalCar' : '/api/storeExternalCar';
  axios.post(url, payload)
    .then(() => {
      showModalExternalCarForm.value = false;
      toast.success(payload.id ? 'تم التعديل بنجاح' : 'تمت إضافة السيارة الخارجية', {
        timeout: 2500,
        position: 'bottom-right',
        rtl: true,
      });
      refresh();
    })
    .catch((error) => {
      toast.error(error?.response?.data?.error || 'تعذر الحفظ', {
        timeout: 3000,
        position: 'bottom-right',
        rtl: true,
      });
    });
}

function openExternalDeleteModal(row) {
  externalDeleteMode.value = true;
  formData.value = { id: row.id };
  showModalDelCar.value = true;
}

function openModalDelCar(form={}) {
  externalDeleteMode.value = false;
  formData.value=form
  showModalDelCar.value = true;
}

function confirmDeleteExternalCar(payload) {
  const id = payload.id;
  axios.post('/api/deleteExternalCar', payload)
    .then(() => {
      car.value = car.value.filter((c) => c.id !== id);
      showModalDelCar.value = false;
      externalDeleteMode.value = false;
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

function handleAddButton() {
  if (isExternalTab.value) {
    openExternalCarForm();
    return;
  }
  openwModalAddCarExpensesFav();
}

function getCarLinkExchangeRate(car) {
  if (car?.link_exchange_rate) {
    const preset = Number(car.link_exchange_rate);
    if (Number.isInteger(preset) && String(preset).length === 6) {
      return preset;
    }
  }

  if (car?.registration_exchange_rate) {
    const stored = Number(car.registration_exchange_rate);
    if (Number.isInteger(stored) && String(stored).length === 6) {
      return stored;
    }
  }

  const expenses = car?.carexpenses ?? [];
  for (const expense of expenses) {
    const note = expense?.note ?? '';
    const matches = note.match(/\[مربوط@(\d+)\]/g) ?? [];
    for (const tag of matches) {
      const rateMatch = tag.match(/(\d+)/);
      const rate = Number(rateMatch?.[1] ?? 0);
      if (Number.isInteger(rate) && String(rate).length === 6) {
        return rate;
      }
    }
  }

  return null;
}

function openModalUnlinkCar(form = {}) {
  formData.value = form;
  unlinkCarPending.value = form;
  showModalUnlinkCar.value = true;
}

function requestUnlinkArchiveCar(car, exchangeRate = null) {
  if (isUnlinkingCar.value) return;

  isUnlinkingCar.value = true;
  const payload = { id: car.id };
  if (exchangeRate != null) {
    payload.exchangeRate = exchangeRate;
  }

  axios.post('/api/confirmUnlinkArchiveCar', payload)
    .then(() => {
      showModalUnlinkCar.value = false;
      unlinkCarPending.value = null;
      toast.success('تم إلغاء الربط بنجاح', {
        timeout: 3000,
        position: 'bottom-right',
        rtl: true,
      });
      refresh();
    })
    .catch((error) => {
      const msg = error?.response?.data?.error || 'فشل إلغاء الربط';
      const needsRate = Boolean(error?.response?.data?.needs_exchange_rate);

      if (needsRate) {
        openModalUnlinkCar({
          ...car,
          link_exchange_rate: getCarLinkExchangeRate(car),
        });
        return;
      }

      toast.error(msg, { timeout: 4000, position: 'bottom-right', rtl: true });
    })
    .finally(() => {
      isUnlinkingCar.value = false;
    });
}

function confirmUnlinkArchiveCar(car) {
  if (isUnlinkingCar.value) return;
  const confirmed = window.confirm('هل تريد إلغاء ربط هذه السيارة؟ سيتم التراجع عن المصاريف المضافة للليست وإعادتها للأرشيف.');
  if (!confirmed) return;

  const knownRate = getCarLinkExchangeRate(car);
  if (!knownRate) {
    openModalUnlinkCar(car);
    return;
  }

  requestUnlinkArchiveCar(car);
}

function confirmUnlinkWithRate(payload) {
  const car = unlinkCarPending.value ?? { id: payload.id };
  requestUnlinkArchiveCar(car, payload.exchangeRate);
}


function confirmDelCarFav(V) {
  const id = V.id;
  axios.post('/api/confirmDelCarFav',V)
  .then(response => {
    car.value = car.value.filter((c) => c.id !== id);
    showModalDelCar.value = false;
    toast.success("حذف السيارة بنجاح", {
        timeout: 3000,
        position: "bottom-right",
        rtl: true

      });
    refresh();
  })
  .catch(error => {
    const msg = error?.response?.data?.error || 'تعذر الحذف';
    toast.error(msg, { timeout: 3500, position: 'bottom-right', rtl: true });
  })


}

</script>

<template>
    <Head title="Dashboard" />
    <ModalArchiveCar
            :formData="formData"
            :show="showModalArchiveCarExpenses ? true : false"
            @a="confirmArchiveCar($event)"
            @close="showModalArchiveCarExpenses = false"
            >
        <template #header>
          </template>
    </ModalArchiveCar>
    <ModalArchiveCarBack
            :formData="formData"
            :show="showModalArchiveCarExpensesBack ? true : false"
            @a="confirmArchiveCarBack($event)"
            @close="showModalArchiveCarExpensesBack = false"
            >
        <template #header>
          </template>
    </ModalArchiveCarBack>
    <ModalArchiveCarLink
            :formData="formData"
            :show="showModalArchiveCarLink ? true : false"
            @a="confirmLinkArchiveCar($event)"
            @close="showModalArchiveCarLink = false"
            >
        <template #header>
          </template>
    </ModalArchiveCarLink>
    <ModalUnlinkCar
            :formData="formData"
            :show="showModalUnlinkCar ? true : false"
            @a="confirmUnlinkWithRate($event)"
            @close="showModalUnlinkCar = false; unlinkCarPending = null"
            >
        <template #header>
          </template>
    </ModalUnlinkCar>
    <ModalAddCarExpensesFav
            :formData="formData"
            :show="showModalAddCarExpensesFav ? true : false"
            :client="client"
            @a="confirmCar($event)"
            @close="showModalAddCarExpensesFav = false"
            >
        <template #header>
          </template>
    </ModalAddCarExpensesFav>
    <ModalAddCarExpenses
            :formData="formData"
            :show="showModalAddCarExpenses ? true : false"
            :currentWork="isWorkTab"
            @a="confirmExpensesCar($event)"
            @close="showModalAddCarExpenses = false"
            >
        <template #header>
          </template>
    </ModalAddCarExpenses>
    <ModalDelCar
      :show="showModalDelCar ? true : false"
      :formData="formData"
      @a="externalDeleteMode ? confirmDeleteExternalCar($event) : confirmDelCarFav($event)"
      @close="showModalDelCar = false; externalDeleteMode = false"
    >
      <template #header>
        <h2 class="mb-5 dark:text-gray-400 text-center">
          {{ externalDeleteMode ? 'هل متأكد من حذف السيارة الخارجية؟' : 'هل متأكد من حذف السيارة ؟' }}
        </h2>
      </template>
    </ModalDelCar>

    <ModalExternalCarForm
      :show="showModalExternalCarForm"
      :form-data="formData"
      @save="saveExternalCar"
      @close="showModalExternalCarForm = false"
    />

    <AuthenticatedLayout>
        <div class="py-2" v-if="$page.props.auth.user.type_id==1||$page.props.auth.user.type_id==7">
          <ul class="sm:px-6 lg:px-8 text-sm font-medium text-center rounded-lg flex dark:divide-gray-700">
            <li class="w-full">
                <button @click="swiptab('work')" class="inline-block w-full p-4 border-r border-gray-200 dark:border-gray-700 hover:text-gray-800 hover:bg-gray-50 focus:outline-none dark:hover:bg-gray-700 dark:hover:text-white" :class="listTab === 'work' ? 'bg-white dark:bg-gray-900 text-gray-900 dark:text-white font-semibold' : 'text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-800'" >قيد العمل </button>
            </li>
            <li class="w-full">
                <button @click="swiptab('archive')" class="inline-block w-full p-4 border-r border-gray-200 dark:border-gray-700 hover:text-gray-800 hover:bg-gray-50 focus:outline-none dark:hover:bg-gray-700 dark:hover:text-white" :class="listTab === 'archive' ? 'bg-white dark:bg-gray-900 text-gray-900 dark:text-white font-semibold' : 'text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-800'" >السيارة المكتملة</button>
            </li>
            <li class="w-full">
                <button @click="swiptab('linked')" class="inline-block w-full p-4 border-r border-gray-200 dark:border-gray-700 hover:text-gray-800 hover:bg-gray-50 focus:outline-none dark:hover:bg-gray-700 dark:hover:text-white" :class="listTab === 'linked' ? 'bg-white dark:bg-gray-900 text-gray-900 dark:text-white font-semibold' : 'text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-800'" >السيارات المربوطة</button>
            </li>
            <li class="w-full">
                <button @click="swiptab('external')" class="inline-block w-full p-4 hover:text-gray-800 hover:bg-gray-50 focus:outline-none dark:hover:bg-gray-700 dark:hover:text-white" :class="listTab === 'external' ? 'bg-white dark:bg-gray-900 text-gray-900 dark:text-white font-semibold' : 'text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-800'" >السيارات الخارجية</button>
            </li>
     
          </ul>

        <div class="max-w-9xl mx-auto sm:px-6 lg:px-8 ">
            <div class="bg-white overflow-hidden shadow-sm ">
                <div class="p-6  dark:bg-gray-900">
                    <div class="flex flex-col">
                      <div class="grid grid-cols-2 md:grid-cols-5 lg:grid-cols-5 gap-2 lg:gap-1">
                        <div>
                          <form class="flex items-center max-w-5xl">
                            <label  class="dark:text-gray-200" for="simple-search"  ></label>
                            <div class="relative w-full">
                              <div
                                class="
                                  absolute
                                  inset-y-0
                                  left-0
                                  flex
                                  items-center
                                  pl-3
                                  pointer-events-none
                                "
                              >
                                <svg
                                  aria-hidden="true"
                                  class="w-5 h-5 text-gray-500 dark:text-gray-200 dark:text-gray-400"
                                  fill="currentColor"
                                  viewBox="0 0 20 20"
                                  xmlns="http://www.w3.org/2000/svg"
                                >
                                  <path
                                    fill-rule="evenodd"
                                    d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                                    clip-rule="evenodd"
                                  ></path>
                                </svg>
                              </div>
                              <input
                                v-model="q"
                                @input="debouncedGetResultsCar"
                                type="text"
                                id="simple-search"
                                class="
                                  bg-gray-50
                                  border border-gray-300
                                  text-gray-900 text-sm
                                  rounded-lg
                                  focus:ring-blue-500 focus:border-blue-500
                                  block
                                  w-full
                                  pl-10
                                  p-2.5
                                  dark:bg-gray-700
                                  dark:border-gray-600
                                  dark:placeholder-gray-400
                                  dark:text-white
                                  dark:focus:ring-blue-500
                                  dark:focus:border-blue-500
                                "
                                placeholder="بحث"
                                required
                              />
                            </div>
                          </form>
                        </div>
                        <div class="text-center">
                            <button
                              type="button"
                              @click="handleAddButton()"
                              style="min-width:150px;"
                              className="px-6 mb-12 mx-2 py-2 font-bold text-white bg-green-500 rounded">
                              {{ isExternalTab ? 'إضافة سيارة خارجية' : $t('addCar') }}
                            </button>
                          </div>
                        <div v-if="isWorkTab" class="text-center">
                            <button
                              type="button"
                              @click="confirmArchiveAllCars()"
                              :disabled="isArchivingAll"
                              style="min-width:150px;"
                              class="px-6 mb-12 mx-2 py-2 font-bold text-white bg-pink-700 rounded disabled:opacity-50"
                            >
                              أرشفة الكل
                            </button>
                          </div>
                        <!-- <div>
                          <button
                            type="button"
                            @click="openAddGenExpenses()"
                            style="min-width:150px;"
                            className="px-6 mb-12 mx-2 py-2 font-bold text-white bg-red-500 rounded">
                               {{ $t('genExpenses') }}
                          </button>
                        </div> -->
                        <!-- <div>
                          <button
                            type="button"
                            @click="openAddCar()"
                            style="min-width:150px;"
                            className="px-6 mb-12 mx-2 py-2 font-bold text-white bg-green-500 rounded">
                            {{ $t('addCar') }} 
                          </button>
                        </div> -->
                        <!-- <div>
                          <a
                            type="button"
                            :href="route('FormRegistrationCompleted')"
                            style="min-width:150px;"
                            className="px-6 mb-12 text-center mx-2 py-2 font-bold text-white bg-blue-600 rounded">
                            {{ $t('allCars') }}
                          </a>
                        </div> -->
                        <!-- <div>
                          <button
                            type="button"
                            @click="openAddTransfers()"
                            style="min-width:150px;"
                            className="px-6 mb-12 mx-2 py-2 font-bold text-white bg-indigo-600 rounded">
                            {{ $t('transfers') }} 
                          </button>
                        </div> -->
                        <div>
                            <select
                              v-if="!isExternalTab"
                              @change="refresh()"
                              v-model="user_id"
                              id="default"
                              class="pr-8 bg-gray-50 border border-gray-300 text-gray-900 mb-6 text-sm rounded-lg focus:ring-red-500 focus:border-red-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-red-500 dark:focus:border-red-500"
                            >
                              <option value="0" disabled> {{ $t("selectCustomer") }}</option>
                              <option value="">{{ $t("allOwners") }}</option>
                              <option v-for="(user, index) in client" :key="index" :value="user.id">{{ user.name }}</option>
                            </select>
                        </div>
                        <!--         
                        <div class="text-center">
                          <button
                            type="button"
                            @click="openAddToBox()"
                            style="min-width:150px;"
                            className="px-6 mb-12 mx-2 py-2 font-bold text-white bg-purple-600 rounded">
                            {{ $t('addToTheFund') }}  
                          </button>
                        </div>
                        <div  class="text-center">
                          <button
                            type="button"
                            @click="openAddFromBox()"
                            style="min-width:150px;"
                            className="px-6 mb-12 mx-2 py-2 font-bold text-white bg-pink-600 rounded">
                            {{ $t('withdrawFromTheFund') }}   
                          </button>
                        </div> -->

                      </div>

                      <div
                        v-if="isExternalTab"
                        class="car-expenses-summary flex flex-wrap items-center justify-between gap-3 mt-4 p-3 rounded-lg border"
                      >
                        <div class="car-expenses-summary-text text-sm font-bold">
                          <span class="car-expenses-summary-dollar">مجموع المدفوع دولار: {{ formatNumber(externalTotalPaidDollar) }} $</span>
                          <span class="car-expenses-summary-sep mx-2">|</span>
                          <span class="car-expenses-summary-dinar">مجموع المدفوع دينار: {{ formatNumber(externalTotalPaidDinar) }} د</span>
                        </div>
                        <p class="text-xs font-semibold text-gray-600 dark:text-gray-300">
                          خارج جدول السيارات — بدون ربط
                        </p>
                      </div>

                      <div
                        v-if="isWorkTab"
                        class="car-expenses-summary flex flex-wrap items-center justify-between gap-3 mt-4 p-3 rounded-lg border"
                      >
                        <div class="car-expenses-summary-text text-sm font-bold">
                          <span class="car-expenses-summary-dollar">مجموع الدولار: {{ formatNumber(expensesTotalDollar) }} $</span>
                          <span class="car-expenses-summary-sep mx-2">|</span>
                          <span class="car-expenses-summary-dinar">مجموع الدينار: {{ formatNumber(expensesTotalDinar) }} د</span>
                        </div>
                        <a
                          target="_blank"
                          :href="summaryPrintUrl"
                          class="inline-flex items-center gap-1 px-3 py-2 text-sm font-bold text-white bg-blue-700 rounded hover:bg-blue-800 shadow-sm"
                        >
                          طباعة مختصرة
                          <print />
                        </a>
                      </div>

                      <div>
                        <div>
                        </div>
                        <div class="overflow-x-auto shadow-md sm:rounded-lg mt-4 mb-5" v-if="isExternalTab">
                        <table class="w-full text-sm text-right text-gray-600 dark:text-gray-200 text-center divide-y divide-gray-200 dark:divide-gray-700">
                          <thead class="text-xs uppercase bg-gray-50 dark:bg-gray-800">
                            <tr class="bg-amber-600 dark:bg-amber-800 text-white">
                              <th scope="col" class="px-3 py-2 sm:px-4 sm:py-2">التاريخ</th>
                              <th scope="col" class="px-3 py-2 sm:px-4 sm:py-2">شانصي</th>
                              <th scope="col" class="px-3 py-2 sm:px-4 sm:py-2">تاجر</th>
                              <th scope="col" class="px-3 py-2 sm:px-4 sm:py-2">{{ $t('car_type') }}</th>
                              <th scope="col" class="px-3 py-2 sm:px-4 sm:py-2">{{ $t('year') }}</th>
                              <th scope="col" class="px-3 py-2 sm:px-4 sm:py-2">{{ $t('color') }}</th>
                              <th scope="col" class="px-3 py-2 sm:px-4 sm:py-2">{{ $t('car_number') }}</th>
                              <th scope="col" class="px-3 py-2 sm:px-4 sm:py-2">مدفوع دولار</th>
                              <th scope="col" class="px-3 py-2 sm:px-4 sm:py-2">مدفوع دينار</th>
                              <th scope="col" class="px-3 py-2 sm:px-4 sm:py-2">{{ $t('execute') }}</th>
                            </tr>
                          </thead>
                          <tbody>
                            <tr v-if="!car?.length">
                              <td colspan="10" class="car-expenses-empty-msg py-8">
                                لا توجد سيارات خارجية
                              </td>
                            </tr>
                            <tr
                              v-for="item in car"
                              :key="`external-${item.id}`"
                              class="bg-white border-b dark:bg-gray-900 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800"
                            >
                              <td class="px-3 py-2 sm:px-4 sm:py-2 text-center text-gray-800 dark:text-gray-100">{{ formatExternalDate(item.date) }}</td>
                              <td class="px-3 py-2 sm:px-4 sm:py-2 text-center text-gray-800 dark:text-gray-100 font-mono text-xs" dir="ltr">{{ item.vin || '-' }}</td>
                              <td class="px-3 py-2 sm:px-4 sm:py-2 text-center text-gray-800 dark:text-gray-100">{{ item.dealer_name }}</td>
                              <td class="px-3 py-2 sm:px-4 sm:py-2 text-center text-gray-800 dark:text-gray-100">{{ item.car_type }}</td>
                              <td class="px-3 py-2 sm:px-4 sm:py-2 text-center text-gray-800 dark:text-gray-100">{{ item.year || '-' }}</td>
                              <td class="px-3 py-2 sm:px-4 sm:py-2 text-center text-gray-800 dark:text-gray-100">{{ item.car_color || '-' }}</td>
                              <td class="px-3 py-2 sm:px-4 sm:py-2 text-center text-gray-800 dark:text-gray-100">{{ item.car_number }}</td>
                              <td class="px-3 py-2 sm:px-4 sm:py-2 text-center car-expenses-cell-dollar font-semibold">{{ formatNumber(item.paid_dollar) }}</td>
                              <td class="px-3 py-2 sm:px-4 sm:py-2 text-center car-expenses-cell-dinar font-semibold">{{ formatNumber(item.paid_dinar) }}</td>
                              <td class="px-3 py-2 sm:px-4 sm:py-2 text-center">
                                <button
                                  type="button"
                                  class="px-2 py-1 text-white mx-1 bg-blue-600 rounded"
                                  @click="openExternalCarForm(item)"
                                >
                                  <edit />
                                </button>
                                <button
                                  type="button"
                                  class="px-2 py-1 text-white mx-1 bg-orange-500 rounded"
                                  @click="openExternalDeleteModal(item)"
                                >
                                  <trash />
                                </button>
                              </td>
                            </tr>
                          </tbody>
                        </table>
                        </div>

                        <div class="overflow-x-auto shadow-md sm:rounded-lg mt-4 mb-5" v-else>
                        <table class="w-full text-sm text-right text-gray-600 dark:text-gray-200 text-center divide-y divide-gray-200 dark:divide-gray-700">
                          <thead class="text-xs uppercase bg-gray-50 dark:bg-gray-800">
                            <tr class="bg-emerald-600 dark:bg-emerald-800 text-white">
                                      <th scope="col" class="px-3 py-2 sm:px-4 sm:py-2	" >
                                        {{ $t('no') }}  
                                      </th>
                                      <th scope="col" class="px-3 py-2 sm:px-4 sm:py-2	">
                                        {{ $t('car_owner') }}
                                      </th>
                                      <th scope="col" class="px-3 py-2 sm:px-4 sm:py-2">
                                        {{ $t('car_type') }}
                                      </th>
                                      <th scope="col" class="px-3 py-2 sm:px-4 sm:py-2">
                                        {{ $t('year') }}
                                      </th>
                                      <th scope="col" class="px-3 py-2 sm:px-4 sm:py-2">
                                        {{ $t('color') }}
                                      </th>
                                      <th scope="col" class="px-3 py-2 sm:px-4 sm:py-2">
                                        {{ $t('vin') }}
                                      </th>
                                      <th scope="col" class="px-3 py-2 sm:px-4 sm:py-2">
                                        {{ $t('car_number') }}
                                      </th>
                                      <th scope="col" class="px-3 py-2 sm:px-4 sm:py-2">
                                        مدفوع دولار
                                      </th>
                                      <th scope="col" class="px-3 py-2 sm:px-4 sm:py-2">
                                        مدفوع دينار
                                      </th>
                                      <th scope="col" class="px-3 py-2 sm:px-4 sm:py-2" style="width: 240px;">
                                        {{ $t('execute') }}
                                      </th>
                                  </tr>
                              </thead>
                              <tbody>
                                <tr v-if="!car?.length">
                                  <td colspan="10" class="car-expenses-empty-msg py-8">
                                    لا توجد سيارات في هذا القسم
                                  </td>
                                </tr>

                                <tr v-for="car in car" :key="car.id" :class="car.results == 0 ?'':car.results == 1 ?'bg-red-100 dark:bg-red-950/50':'bg-green-100 dark:bg-green-950/50'"  class="bg-white border-b dark:bg-gray-900 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800">
                                    <td className="px-3 py-2 sm:px-4 sm:py-2 text-center text-gray-800 dark:text-gray-100">{{ car.no }}</td>
                                    <td className="px-3 py-2 sm:px-4 sm:py-2 text-center text-gray-800 dark:text-gray-100">{{ car.client?.name }}</td>
                                    <td className="px-3 py-2 sm:px-4 sm:py-2 text-center text-gray-800 dark:text-gray-100">{{ car.car_type}}</td>
                                    <td className="px-3 py-2 sm:px-4 sm:py-2 text-center text-gray-800 dark:text-gray-100">{{ car.year}}</td>
                                    <td className="px-3 py-2 sm:px-4 sm:py-2 text-center text-gray-800 dark:text-gray-100">{{ car.car_color }}</td>
                                    <td className="px-3 py-2 sm:px-4 sm:py-2 text-center text-gray-800 dark:text-gray-100">{{ car.vin }}</td>
                                    <td className="px-3 py-2 sm:px-4 sm:py-2 text-center text-gray-800 dark:text-gray-100">{{ car.car_number }}</td> 
                                    <td className="px-3 py-2 sm:px-4 sm:py-2 text-center car-expenses-cell-dollar font-semibold">{{ formatNumber(calculateSum(car.carexpenses)) }}</td>
                                    <td className="px-3 py-2 sm:px-4 sm:py-2 text-center car-expenses-cell-dinar font-semibold">{{ formatNumber(calculateSumDinar(car.carexpenses)) }}</td>
                                    <td className="px-3 py-2 sm:px-4 sm:py-2 text-center">
                                    <button
                                      tabIndex="1"
                                      class="px-2 py-1  text-white mx-1 bg-emerald-500 rounded"
                                      @click="openwshowModalAddCarExpenses(car)"
                                    >
                                     <newContracts v-if="isWorkTab" />
                                     <show v-else-if="isArchiveTab" />
                                     <show v-else />
                                    </button>
                                    <button
                                    v-if="isWorkTab"
                                      tabIndex="1"
                                      class="px-2 py-1  text-white mx-1 bg-pink-600 rounded mt-3 sm:mt-0"
                                      @click="openwshowModalArchiveCarExpenses(car)"
                                    >
                                    <svg class="w-6 h-6 text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 16">
                                      <path stroke="currentColor" stroke-linejoin="round" stroke-width="2" d="M8 8v1h4V8m4 7H4a1 1 0 0 1-1-1V5h14v9a1 1 0 0 1-1 1ZM2 1h16a1 1 0 0 1 1 1v2a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1Z"></path>
                                    </svg>
                                    </button>
                                    <button
                                    v-if="isArchiveTab"
                                      tabIndex="1"
                                      class="px-2 py-1  text-white mx-1 bg-pink-600 rounded mt-3 sm:mt-0"
                                      @click="openwshowModalArchiveCarExpensesBack(car)"
                                    >
                                    <svg class="w-6 h-6 text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 16 16">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h11m0 0-4-4m4 4-4 4m-5 3H3a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h3"></path>
                                    </svg>
                                    </button>
                                    <button
                                    v-if="isArchiveTab"
                                      tabIndex="1"
                                      class="px-2 py-1 text-white mx-1 bg-indigo-600 rounded mt-3 sm:mt-0 text-xs font-bold"
                                      @click="openModalArchiveCarLink(car)"
                                    >
                                      ربط
                                    </button>
                                    <button
                                    v-if="isLinkedTab"
                                      tabIndex="1"
                                      class="px-2 py-1 text-white mx-1 bg-orange-600 rounded mt-3 sm:mt-0 text-xs font-bold disabled:opacity-50"
                                      :disabled="isUnlinkingCar"
                                      @click="confirmUnlinkArchiveCar(car)"
                                    >
                                      إلغاء الربط
                                    </button>
                                    <button
                                    v-if="isWorkTab"
                                      tabIndex="1"
                                      
                                      class="px-2 py-1  text-white mx-1 bg-orange-500 rounded  mt-3 sm:mt-0"
                                      @click="openModalDelCar(car)"
                                    >
                                      <trash />
                                    </button>
                                    <a  target="_blank"
                                      style="display: inline-flex;"
                                      :href="`/api/getIndexExpensesPrint?car_id=${car.id}`"
                                      tabIndex="1"
                                      class="px-2 py-1  text-white  m-1 bg-blue-500 rounded"
                                      >
                                      <print />
                                      </a>

                                    </td> 
                                </tr>
                              </tbody>
                          </table>
                        </div>
                        <div class="spaner car-expenses-infinite">
                          <InfiniteLoading :car="car" @infinite="getResultsCar" :identifier="resetData">
                            <template #complete>
                              <p class="car-expenses-empty-msg">لا توجد سيارات أخرى</p>
                            </template>
                            <template #error>
                              <p class="car-expenses-empty-msg car-expenses-error-msg">تعذر تحميل المزيد</p>
                            </template>
                          </InfiniteLoading>
                        </div>
                      </div>

                      </div>
                    </div>
                    </div>
                </div>
            </div>
        <div >
  
    </div>   
    </AuthenticatedLayout>
</template>

<style scoped>
.car-expenses-summary {
  background: #f8fafc;
  border-color: #94a3b8;
}

.car-expenses-summary-dollar {
  color: #14532d !important;
}

.car-expenses-summary-dinar {
  color: #1e3a8a !important;
}

.car-expenses-summary-sep {
  color: #475569 !important;
}

.car-expenses-cell-dollar {
  color: #15803d !important;
}

.car-expenses-cell-dinar {
  color: #1d4ed8 !important;
}

.car-expenses-empty-msg {
  padding: 1rem;
  text-align: center;
  font-size: 0.875rem;
  font-weight: 600;
  color: #e5e7eb !important;
}

.car-expenses-error-msg {
  color: #fca5a5 !important;
}

:global(.dark) .car-expenses-summary {
  background: #1f2937;
  border-color: #6b7280;
}

:global(.dark) .car-expenses-summary-dollar {
  color: #86efac !important;
}

:global(.dark) .car-expenses-summary-dinar {
  color: #93c5fd !important;
}

:global(.dark) .car-expenses-summary-sep {
  color: #d1d5db !important;
}

:global(.dark) .car-expenses-cell-dollar {
  color: #4ade80 !important;
}

:global(.dark) .car-expenses-cell-dinar {
  color: #60a5fa !important;
}

.car-expenses-infinite :deep(.container) {
  color: #e5e7eb !important;
}

.car-expenses-infinite :deep(.state-error) {
  color: #fca5a5 !important;
}
</style>
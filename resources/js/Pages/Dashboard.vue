<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DashboardWidgets from '@/Components/Dashboard/DashboardWidgets.vue';
import axios from 'axios';
import { ref, watch } from 'vue';
import { useI18n } from "vue-i18n";
import { Head, Link } from "@inertiajs/inertia-vue3";

const props = defineProps({
  auth: Object,
  apiKey: String,
  company_name: String,
  showBrokerage: { type: Boolean, default: false }
});

const {t} = useI18n();
let userType = ref(props.auth.user.type_id)

function selectUser(v){
  if(v==1 || v==6){
    return 'getIndexClients'
  }
  if(v==8){
    return 'getIndexClientsContract'
  }
}

let data = ref({});
 

const laravelData = ref({});
const laravelDataO = ref({});

const laravelData1 = ref({});
const laravelData2 = ref({});
const completedContractsThisMonth = ref(0);
const completedContractsLastMonth = ref(0);
const completedContractsThisYear = ref(0);
const completedContractsThisMonthCompany = ref(0);
const completedContractsThisMonthExternal = ref(0);
const completedContractsLastMonthCompany = ref(0);
const completedContractsLastMonthExternal = ref(0);
const completedContractsThisYearCompany = ref(0);
const completedContractsThisYearExternal = ref(0);
const recentContracts = ref([]);
let  controller = new AbortController(); // Create a new AbortController

const getResults = async (page = 1) => {
  axios.get(`api/${selectUser(userType.value)}?page=${page}&q=debit`)
  .then(response => {
    if(userType.value==1 || userType.value==6){
      try {
      laravelData.value =  response.data.data
    } catch (error) {
      laravelData.value =  response.data.data

    }
  }
  if(userType.value==8){
    laravelData1.value =  response.data.data1
    laravelData2.value =  response.data.data2
    completedContractsThisMonth.value = response.data.completedContractsThisMonth ?? 0
    completedContractsLastMonth.value = response.data.completedContractsLastMonth ?? 0
    completedContractsThisYear.value = response.data.completedContractsThisYear ?? 0
    completedContractsThisMonthCompany.value = response.data.completedContractsThisMonthCompany ?? 0
    completedContractsThisMonthExternal.value = response.data.completedContractsThisMonthExternal ?? 0
    completedContractsLastMonthCompany.value = response.data.completedContractsLastMonthCompany ?? 0
    completedContractsLastMonthExternal.value = response.data.completedContractsLastMonthExternal ?? 0
    completedContractsThisYearCompany.value = response.data.completedContractsThisYearCompany ?? 0
    completedContractsThisYearExternal.value = response.data.completedContractsThisYearExternal ?? 0
    recentContracts.value = response.data.recentContracts ?? []
  }


  laravelDataO.value =laravelData.value
  })
  .catch(error => {
    console.error(error);
  })
  
}
getResults();

let expenses_type_id = ref(0);
let showModal = ref(false);
let searchTerm = ref('');
let searchQuery = ref('');


let onlineContracts= ref(0)
let howler= ref(0)
let shippingCoc= ref(0)
let border= ref(0)
let iran= ref(0)
let dubai= ref(0)
let debtOnlineContracts= ref(0)
let onlineContractsDinar= ref(0)
let debtOnlineContractsDinar= ref(0)
let purchasesCost = ref(0)
let clientPaid = ref(0)
let clientDebit  = ref(0)
let mainBoxDollar = ref(0)
let mainBoxDinar = ref(0)

let allCars= ref(0)

function openModal() {
  showModal.value = true;
}
const formData = ref({});
const car = ref([]);

import { debounce } from 'lodash';


const debouncedGetResultsCarSearch = debounce(async (q = '', page = 1) => {
  if(!q){
    q='debit'
  }
    try {
        const response = await axios.get(`api/${selectUser(userType.value)}?page=${page}&q=${q}`,{      signal: controller.signal // Pass the signal to abort the request if needed
});
        laravelData.value = response.data.data
    } catch (error) {
        console.error(error);
    }
}, 300); 

const getResultsCarSearch = (q = '', page = 1) => {
  searchQuery.value=''
  debouncedGetResultsCarSearch(q, page);
}
//
const getcountTotalInfo = async () => {
  axios.get('/api/totalInfo')
  .then(response => {
    onlineContracts.value=  response.data.data.onlineContracts
    howler.value=  response.data.data.howler
    shippingCoc.value=  response.data.data.shippingCoc
    border.value=  response.data.data.border
    iran.value=  response.data.data.iran
    dubai.value=  response.data.data.dubai
    debtOnlineContracts.value=  response.data.data.debtOnlineContracts
    onlineContractsDinar.value =response.data.data.onlineContractsDinar
    debtOnlineContractsDinar.value = response.data.data.debtOnlineContractsDinar
    allCars.value =response.data.data.allCars;
    purchasesCost.value =response.data.data.purchasesCost
    clientPaid.value =response.data.data.clientPaid
    clientDebit.value =response.data.data.clientDebit
    mainBoxDollar.value =response.data.data.mainBoxDollar
    mainBoxDinar.value =response.data.data.mainBoxDinar

  })
  .catch(error => {
    console.error(error);
  })
  
    
}
const abortRequest = () => {
  if (controller) {
    controller.abort(); // Abort previous request if it exists
  }
  controller = new AbortController(); // Create a new AbortController
};

watch([searchTerm], () => {
  abortRequest(); // Abort previous request
  debouncedGetResultsCarSearch(); // Call debounced function to fetch new results
});

getcountTotalInfo()

/** Debt severity accent for client cards (border + amount color). */
function debtTone(total) {
  const amount = Number(total) || 0;
  if (amount < 0) {
    return {
      card: 'border-emerald-500/40 hover:border-emerald-400/70',
      bar: 'bg-emerald-500',
      amount: 'text-emerald-400',
      badge: 'bg-emerald-500/15 text-emerald-300',
    };
  }
  if (amount >= 30000) {
    return {
      card: 'border-rose-500/40 hover:border-rose-400/70',
      bar: 'bg-rose-500',
      amount: 'text-rose-400',
      badge: 'bg-rose-500/15 text-rose-300',
    };
  }
  if (amount >= 15000) {
    return {
      card: 'border-orange-500/40 hover:border-orange-400/70',
      bar: 'bg-orange-500',
      amount: 'text-orange-400',
      badge: 'bg-orange-500/15 text-orange-300',
    };
  }
  if (amount >= 5000) {
    return {
      card: 'border-amber-500/40 hover:border-amber-400/70',
      bar: 'bg-amber-500',
      amount: 'text-amber-400',
      badge: 'bg-amber-500/15 text-amber-300',
    };
  }
  return {
    card: 'border-slate-600 hover:border-slate-500',
    bar: 'bg-sky-500',
    amount: 'text-sky-300',
    badge: 'bg-sky-500/15 text-sky-300',
  };
}

function changeColor(total) {
  return debtTone(total).card;
}
function updateResults(input) {
  // Ensure the input is a number
  if (typeof input !== 'number') {
    // Try converting the input to a number
    input = parseFloat(input) || 0;
  }
  
  // Use toLocaleString to format the number with commas
  return input.toLocaleString();
}

function getResultsCarSearchLocal () {
  searchTerm.value = ''
  laravelData.value = laravelDataO.value.filter(item =>
    item.name.toLowerCase().includes(searchQuery.value.toLowerCase())
  );
};
</script>

<template>
    <Head title="Dashboard" />
    <AuthenticatedLayout >
        <div class="py-2"  v-if="$page.props.auth.user.type_id==1||$page.props.auth.user.type_id==6">
        <div class="max-w-9xl mx-auto sm:px-6 lg:px-8 ">
            <div class="bg-white overflow-hidden shadow-sm ">
                <div class="p-6  dark:bg-gray-900">
                    <div class="flex flex-col">
                      <DashboardWidgets class="mb-4">
                        <div class="flex h-full flex-col gap-2.5">
                          <div class="flex flex-1 items-start rounded-xl dark:bg-gray-600 dark:text-gray-300 bg-white p-3 shadow-lg">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-orange-100 bg-orange-50">
                              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                              </svg>
                            </div>
                            <div class="mr-3 min-w-0">
                              <h2 class="font-semibold text-sm">دين التجار</h2>
                              <p class="mt-1 text-sm text-gray-500 dark:text-gray-200">{{ updateResults(clientDebit) }} دولار</p>
                            </div>
                          </div>
                          <div class="flex flex-1 items-start rounded-xl dark:bg-gray-600 dark:text-gray-300 bg-white p-3 shadow-lg">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-orange-100 bg-orange-50">
                              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                              </svg>
                            </div>
                            <div class="mr-3 min-w-0">
                              <h2 class="font-semibold text-sm">الصندوق</h2>
                              <p class="mt-1 text-sm text-gray-500 dark:text-gray-200">{{ updateResults(mainBoxDollar) }} دولار</p>
                            </div>
                          </div>
                          <div class="flex flex-1 items-start rounded-xl dark:bg-gray-600 dark:text-gray-300 bg-white p-3 shadow-lg">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-orange-100 bg-orange-50">
                              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                              </svg>
                            </div>
                            <div class="mr-3 min-w-0">
                              <h2 class="font-semibold text-sm">الصندوق</h2>
                              <p class="mt-1 text-sm text-gray-500 dark:text-gray-200">{{ updateResults(mainBoxDinar) }} دينار</p>
                            </div>
                          </div>
                        </div>
                      </DashboardWidgets>

                      <!-- Search row: two columns -->
                      <div class="mb-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div class="relative w-full">
                          <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <svg aria-hidden="true" class="h-5 w-5 text-gray-500 dark:text-gray-400" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                              <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"></path>
                            </svg>
                          </div>
                          <input
                            v-model="searchTerm"
                            @input="getResultsCarSearch(searchTerm)"
                            type="text"
                            class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 pl-10 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400"
                            placeholder="بحث"
                          />
                        </div>
                        <div class="relative w-full">
                          <input
                            v-model="searchQuery"
                            @input="getResultsCarSearchLocal(searchQuery)"
                            type="text"
                            class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400"
                            placeholder="بحث بالاسم فقط"
                          />
                        </div>
                      </div>

                      <div>
                        <div class="mt-2 grid grid-cols-1 gap-3 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6">
                          <Link
                            v-for="(user, i) in laravelData"
                            :key="user.id || i"
                            :href="route('showClients', { id: user.id, q: searchTerm })"
                            class="group relative overflow-hidden rounded-xl border bg-slate-900/80 p-3.5 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:shadow-md dark:bg-slate-900"
                            :class="debtTone(user.balance).card"
                          >
                            <span
                              class="absolute inset-y-0 right-0 w-1 rounded-s-full"
                              :class="debtTone(user.balance).bar"
                              aria-hidden="true"
                            />
                            <div class="pr-2 text-right">
                              <p class="truncate text-sm font-semibold text-slate-100" :title="user.name">
                                {{ user.name }}
                              </p>
                              <p
                                class="mt-2 font-mono text-base font-bold tabular-nums tracking-tight"
                                :class="debtTone(user.balance).amount"
                                dir="ltr"
                              >
                                ${{ updateResults(user.balance) }}
                              </p>
                            </div>
                          </Link>
                        </div>
                      </div>
                    </div>
                </div>
            </div>
        </div>
        </div>
        <div v-if="$page.props.auth.user.type_id==8">
          <div class="max-w-9xl mx-auto sm:px-6 lg:px-8 ">
            <div class="bg-white overflow-hidden shadow-sm ">
                <div class="p-6  dark:bg-gray-900">
                    <div class="flex flex-col">
                      <div>
                          <!-- إحصائيات وعقود حديثة -->
                          <div class="mb-6 p-4 rounded-lg bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-200 dark:border-indigo-800">
                            <h2 class="text-lg font-semibold dark:text-white mb-3">إحصائيات العقود</h2>
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">
                              <div class="rounded-lg bg-white dark:bg-gray-800 p-4 border border-sky-200 dark:border-sky-800">
                                <h3 class="font-semibold text-sky-700 dark:text-sky-300 mb-3">عقود الشركة</h3>
                                <div class="flex flex-wrap items-center gap-4">
                                  <div><span class="text-xl font-bold text-sky-600">{{ completedContractsThisMonthCompany }}</span> <span class="text-sm text-gray-500">هذا الشهر</span></div>
                                  <div><span class="text-xl font-bold text-blue-600">{{ completedContractsLastMonthCompany }}</span> <span class="text-sm text-gray-500">الشهر الماضي</span></div>
                                  <div><span class="text-xl font-bold text-green-600">{{ completedContractsThisYearCompany }}</span> <span class="text-sm text-gray-500">هذه السنة</span></div>
                                </div>
                              </div>
                              <div class="rounded-lg bg-white dark:bg-gray-800 p-4 border border-violet-200 dark:border-violet-800">
                                <h3 class="font-semibold text-violet-700 dark:text-violet-300 mb-3">{{ t('nav.external_car_contract') }}</h3>
                                <div class="flex flex-wrap items-center gap-4">
                                  <div><span class="text-xl font-bold text-violet-600">{{ completedContractsThisMonthExternal }}</span> <span class="text-sm text-gray-500">هذا الشهر</span></div>
                                  <div><span class="text-xl font-bold text-purple-600">{{ completedContractsLastMonthExternal }}</span> <span class="text-sm text-gray-500">الشهر الماضي</span></div>
                                  <div><span class="text-xl font-bold text-fuchsia-600">{{ completedContractsThisYearExternal }}</span> <span class="text-sm text-gray-500">هذه السنة</span></div>
                                </div>
                              </div>
                            </div>
                            <div class="flex flex-wrap items-center gap-6 text-sm text-gray-500 dark:text-gray-400">
                              <span>الإجمالي هذا الشهر: <strong class="text-indigo-600">{{ completedContractsThisMonth }}</strong></span>
                              <span>الإجمالي الشهر الماضي: <strong class="text-blue-600">{{ completedContractsLastMonth }}</strong></span>
                              <span>الإجمالي هذه السنة: <strong class="text-green-600">{{ completedContractsThisYear }}</strong></span>
                            </div>
                            <div v-if="recentContracts && recentContracts.length" class="mt-4">
                              <h3 class="text-sm font-medium dark:text-gray-300 mb-2">آخر العقود</h3>
                              <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-4">
                                <Link v-for="c in recentContracts" :key="c.id" :href="`/contract/${c.id}?type=${c.contract_type || 'company'}`" class="flex items-center justify-between p-3 rounded-lg bg-white dark:bg-gray-800 shadow-sm hover:shadow-md transition">
                                  <div class="text-right">
                                    <div class="flex items-center gap-2 justify-end">
                                      <span v-if="c.contract_type === 'external'" class="text-[10px] px-2 py-0.5 rounded-full bg-violet-100 text-violet-700">{{ t('externalContract.badgeExternal') }}</span>
                                      <span v-else class="text-[10px] px-2 py-0.5 rounded-full bg-sky-100 text-sky-700">شركة</span>
                                      <div class="font-medium dark:text-white">{{ c.car_name || c.no || '—' }}</div>
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ c.name_seller }} → {{ c.name_buyer }}</div>
                                    <div class="text-xs text-indigo-600 dark:text-indigo-400">{{ c.car_price }} · {{ c.created }}</div>
                                  </div>
                                </Link>
                              </div>
                            </div>
                          </div>
                          <template v-if="showBrokerage">
                          <h2 class="my-3 dark:text-white" v-if="laravelData1">دين البائع</h2>
                          <div class="mt-2 grid grid-cols-1 gap-3 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
                          <Link v-for="(user,i) in laravelData1" :key="i" class="relative overflow-hidden rounded-xl border bg-slate-900/80 p-3.5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"  :href="route('car_contract', {   q:  user.name_seller })"   :class="debtTone(user.tex_seller -  user.tex_seller_paid).card">
                            <span class="absolute inset-y-0 right-0 w-1 rounded-s-full" :class="debtTone(user.tex_seller - user.tex_seller_paid).bar" aria-hidden="true" />
                            <div class="pr-2 text-right">
                              <h2 class="truncate text-sm font-semibold text-slate-100">{{ user.name_seller}}</h2>
                              <p class="mt-2 font-mono text-sm font-bold tabular-nums" :class="debtTone(user.tex_seller - user.tex_seller_paid).amount" dir="ltr">
                                ${{ user.tex_seller -  user.tex_seller_paid }}
                                · IQD {{ user.tex_seller_dinar -  user.tex_seller_dinar_paid }}
                              </p>
                            </div>
                          </Link>

 
                        </div>
                        <h2 class="my-3 dark:text-white" v-if="laravelData2">دين المشتري</h2>
                          <div class="mt-2 grid grid-cols-1 gap-3 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
                          <Link v-for="(user,i) in laravelData2" :key="i" class="relative overflow-hidden rounded-xl border bg-slate-900/80 p-3.5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"  :href="route('car_contract', {q:  user.name_buyer })"   :class="debtTone(user.tex_buyer -  user.tex_buyer_paid).card">
                            <span class="absolute inset-y-0 right-0 w-1 rounded-s-full" :class="debtTone(user.tex_buyer - user.tex_buyer_paid).bar" aria-hidden="true" />
                            <div class="pr-2 text-right">
                              <h2 class="truncate text-sm font-semibold text-slate-100">{{ user.name_buyer}}</h2>
                              <p class="mt-2 font-mono text-sm font-bold tabular-nums" :class="debtTone(user.tex_buyer - user.tex_buyer_paid).amount" dir="ltr">
                                ${{ user.tex_buyer -  user.tex_buyer_paid }}
                                · IQD {{ user.tex_buyer_dinar -  user.tex_buyer_dinar_paid }}
                              </p>
                            </div>
                          </Link>

 
                        </div>
                          </template>
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
<style>
.Vue-Toastification__container {
width: unset !important;
}
.duet-date__dialog {
  direction: ltr;
    right: 0;
    top: 44px;
}
.header-rgRow{
  text-align: center;
}
.rgRow > div {
  text-align: center !important;
}
.rgCell.disabled {
    background-color: unset !important;
}
.rgCell{
  padding-top: 7px !important;
}

body::-webkit-scrollbar {
  width: 12px;
}

body::-webkit-scrollbar-track {
  background: #f1f1f1;
}

body::-webkit-scrollbar-thumb {
  background: #888;
  border-radius: 6px;
}

/* Style the scrollbars for Firefox */
body {
  scrollbar-width: thin;
  scrollbar-color: #888 #f1f1f1;
}

.border-4 {
    border-width: 6px;
}
</style>

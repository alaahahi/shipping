<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Modal from "@/Components/Modal.vue";
import { Head, Link, useForm } from "@inertiajs/inertia-vue3";
import { ref } from "vue";
import ModalAddSales from "@/Components/ModalAddSales.vue";
 import ModalAddExpensesWallet from "@/Components/ModalAddExpensesWallet.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import ModalAddGenExpenses from "@/Components/ModalAddGenExpenses.vue";
import ModalConvertDollarDinar from "@/Components/ModalConvertDollarDinar.vue";
import ModalConvertDinarDollar from "@/Components/ModalConvertDinarDollar.vue";
import ModalDel from "@/Components/ModalDel.vue";
import ModalUploader from "@/Components/ModalUploader.vue";


import axios from 'axios';
import show from "@/Components/icon/show.vue";
import pay from "@/Components/icon/pay.vue";
import trash from "@/Components/icon/trash.vue";
import edit from "@/Components/icon/edit.vue";
import imags from "@/Components/icon/imags.vue";
import print from "@/Components/icon/print.vue";

import InfiniteLoading from "v3-infinite-loading";
import "v3-infinite-loading/lib/style.css";
import debounce from 'lodash/debounce';
import { formatBaghdadTimestamp } from "@/utils/datetime";


const laravelData = ref({});
const searchTerm = ref('');
let showModalAddSales = ref(false);
let showModaldebtSales = ref(false);
let showModalAddExpensesWallet = ref(false);
let showModalAddGenExpenses = ref(false);
let showModalConvertDollarDinar = ref(false);
let showModalConvertDinarDollar = ref(false);
let showModalDel = ref(false);
let showModalUploader = ref(false);
let showModalAddSalesAmanah = ref(false);
let showModaldebtSalesAmanah = ref(false);
let transactions= ref([]);
let expenses_type_id = ref(0);
let tranId =ref({});
let formData = ref({});
let GenExpenses = ref({});
let isLoading=ref(false);
let from = ref('');
let to = ref('');
let mainAccount= ref(0)
let onlineContracts= ref(0)
let howler= ref(0)
let shippingCoc= ref(0)
let border= ref(0)
let iran= ref(0)
let dubai= ref(0)
let debtOnlineContracts= ref(0)
let allCars= ref(0)
let onlineContractsDinar = ref(0)
let debtOnlineContractsDinar = ref(0)
let resetData = ref(false);
let user_id = 0;
let page = 1;
let q = ref('');
const refresh = () => {
  page = 0;
  transactions.value.length = 0;
  resetData.value = !resetData.value;


};
const debouncedGetResultsCar = debounce(refresh, 500);

const getResults = async ($state) => {
  try {
    const response = await axios.get(`/getIndexAccounting`, {
      params: {
        limit: 1000,
        page: page,
        q: q.value,
        user_id: props.boxes.id,
        type: 'wallet'
      }
    });

    const json = response.data;


    if (json.transactions.data.length < 1000){
      transactions.value.push(...json.transactions.data);
      $state.complete();
    } 
    else {
      transactions.value.push(...json.transactions.data);
       $state.loaded();
    }

    laravelData.value = json;
    page++;
  } catch (error) {
    console.log(error);
    //$state.error();
  }
};
 

function openAddSales() {
  showModalAddSales.value = true;
}
function opendebtSales() {
  showModaldebtSales.value = true;
}
function openAddExpenses(){
  showModalAddExpensesWallet.value = true;
}
function openAddSalesAmanah() {
  showModalAddSalesAmanah.value = true;
}
function opendebtSalesAmanah() {
  showModaldebtSalesAmanah.value = true;
}
function openConvertDollarDinar(){
  showModalConvertDollarDinar.value = true;
}
function openConvertDinarDollar(){
  showModalConvertDinarDollar.value = true;
}
function openModalDel(tran){
  tranId.value = tran
  showModalDel.value = true;
}
function openModalUploader(tran){
  tranId.value = tran
  showModalUploader.value = true;
}

const props = defineProps({
  url: String,
  boxes:Object,
});
const search = async (q) => {
  user_id.value=0;
  laravelData.value = [];
  const response = await fetch(`/livesearchAppointment?q=${q}`);
  laravelData.value = await response.json();
};
const form = useForm();

let showModal = ref(false);
const come = async (id) => {
  const response = await fetch(`/appointmentCome?id=${id}`);
  refresh();

};
const cancel = async (id) => {
  const response = await fetch(`/appointmentCancel?id=${id}`);
  refresh();

};

 
 
const errors = ref(0);
 
 
 
function confirm(V) {
  V.id=props.boxes.id;
  axios.post('/api/receiptArrivedUser',V)
  .then(response => {
    showModalAddSales.value=false;
    window.location.reload();

  })
  .catch(error => {

    errors.value = error.response.data.errors
  })
}
function confirmdebt(V) {
  axios.post('/api/salesDebtUser',V)
  .then(response => {
    showModaldebtSales.value=false;
    showModalAddExpensesWallet.value = false;
    window.location.reload();

  })
  .catch(error => {

    errors.value = error.response.data.errors
  })
}
function confirmAmanah(V) {
  V.id=props.boxes.id;
  axios.post('/api/receiptArrivedUserAmanah',V)
  .then(response => {
    showModalAddSalesAmanah.value=false;
    window.location.reload();

  })
  .catch(error => {

    errors.value = error.response.data.errors
  })
}
function confirmdebtAmanah(V) {
  axios.post('/api/salesDebtUserAmanah',V)
  .then(response => {
    showModaldebtSalesAmanah.value=false;
    window.location.reload();

  })
  .catch(error => {

    errors.value = error.response.data.errors
  })
}
function confirmConvertDollarDinar(V) {
  axios.post('/api/convertDollarDinar',V)
  .then(response => {
    refresh();
    showModalConvertDollarDinar.value=false;

  })
  .catch(error => {

    errors.value = error.response.data.errors
  })
}
function confirmConvertDinarDollar(V) {
  axios.post('/api/convertDinarDollar',V)
  .then(response => {
    refresh();
    showModalConvertDinarDollar.value=false;

  })
  .catch(error => {

    errors.value = error.response.data.errors
  })
}



function getTodayDate() {
  const today = new Date();
  const year = today.getFullYear();
  const month = String(today.getMonth() + 1).padStart(2, '0');
  const day = String(today.getDate()).padStart(2, '0');
  return `${year}-${month}-${day}`;
}
function delTransactions(id){
  axios.post(`/api/delTransactions?id=${id.id}`)
  .then(response => {
    refresh();
    showModalDel.value=false;
  })
  .catch(error => {

    errors.value = error.response.data.errors
  })
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

function getImageUrl(name) {
  // Provide the base URL for your images
  return `/public/uploadsResized/${name}`;
}

function getDownloadUrl(name) {
  // Provide the base URL for downloading images
  return `/public/uploads/${name}`;
}

// حساب الرصيد التراكمي
function calculateBalance(transaction, index) {
  let balance = 0;
  // نحتاج لحساب الرصيد من أقدم معاملة حتى هذه المعاملة
  // المعاملات مرتبة من الأحدث للأقدم، لذا نحتاج لعكس الترتيب في الحساب
  
  // إنشاء نسخة مرتبة من المعاملات حسب التاريخ والـ ID من الأقدم للأحدث
  const sortedTransactions = [...transactions.value]
    .filter(t => t && t.currency === transaction.currency)
    .sort((a, b) => {
      // الترتيب حسب التاريخ أولاً
      const dateA = new Date(a.created_at || a.created || 0);
      const dateB = new Date(b.created_at || b.created || 0);
      const dateDiff = dateA.getTime() - dateB.getTime();
      
      // إذا كانت التواريخ متساوية، نرتب حسب ID (الأقدم أولاً - ID أصغر)
      if (dateDiff === 0) {
        return (a.id || 0) - (b.id || 0);
      }
      return dateDiff;
    });
  
  // العثور على موضع هذه المعاملة في القائمة المرتبة
  const transactionId = transaction.id || 0;
  
  for (let i = 0; i < sortedTransactions.length; i++) {
    const tran = sortedTransactions[i];
    
    // نأخذ فقط معاملات الصندوق (ليس الأمانة) لحساب الرصيد
    if (tran.type === 'inUser') {
      balance += parseFloat(tran.amount) || 0;
    } else if (tran.type === 'outUser') {
      balance -= Math.abs(parseFloat(tran.amount) || 0);
    }
    // نتجاهل معاملات الأمانة لأنها لا تؤثر على balance
    
    // إذا وصلنا إلى هذه المعاملة، نتوقف
    if (tran.id === transactionId) {
      break;
    }
  }
  
  return balance;
}

function conGenfirmExpenses(V) {
  axios.post(`/api/GenExpenses?amount=${V.amount??0}&expenses_type_id=${expenses_type_id.value}&factor=${V.factor??1}&note=${V.note??''}`)
  .then(response => {
    refresh();
    showModalAddGenExpenses.value = false;
    console.log(response.data);
    window.open(`/api/getIndexAccountsSelas?user_id=${response.data.morphed_id}&print=3&transactions_id=${response.data.id}`, '_blank');
    window.location.reload();
  })
  .catch(error => {

    errors.value = error.response.data.errors
  })


}

function printAmanah() {
  if(props.boxes?.id) {
    window.open(`/getIndexAccounting?user_id=${props.boxes.id}&type=wallet&print=7`, '_blank');
  }
}

function printWallet() {
  if(props.boxes?.id) {
    window.open(`/getIndexAccounting?user_id=${props.boxes.id}&type=wallet&print=8`, '_blank');
  }
}

</script>

<template>
  <Head title="Dashboard" />
  <AuthenticatedLayout>
    <template #header>
 
    </template>
    <ModalDel
            :show="showModalDel ? true : false"
            :formData="tranId"
            @a="delTransactions($event)"
            @close="showModalDel = false"
            >
          <template #header>
            <h2 class=" mb-5 dark:text-white text-center">

          هل متأكد من الحذف 
          ؟
          </h2>
          </template>
    </ModalDel>

    <ModalUploader
            :show="showModalUploader ? true : false"
            :formData="tranId"
            @a="refresh()"
            @close="showModalUploader = false"
            >
          <template #header>
            <h2 class=" mb-5 dark:text-white text-center">
              مرفقات الحركة
          </h2>
          </template>
    </ModalUploader>

    <ModalAddSales
            :show="showModalAddSales ? true : false"
            @a="confirm($event)"
            @close="showModalAddSales = false"
            >
          <template #header>
            <h3 class="text-center">المحاسبة</h3>
            
           </template>
      </ModalAddSales>
 
      <ModalAddExpensesWallet 
            :show="showModalAddExpensesWallet ? true : false"
            :boxes="boxes"
            :sum_transactions="laravelData.sum_transactions"
            :sum_transactions_dinar="laravelData.sum_transactions_dinar"
            @a="confirmdebt($event)"
            @close="showModalAddExpensesWallet = false"
            >
          <template #header>
            <h3 class="text-center"> سحب  من القاسه</h3>
            
           </template>
      </ModalAddExpensesWallet>
      <ModalAddSales
            :show="showModalAddSalesAmanah ? true : false"
            @a="confirmAmanah($event)"
            @close="showModalAddSalesAmanah = false"
            >
          <template #header>
            <h3 class="text-center">أمانة - إيداع</h3>
            
           </template>
      </ModalAddSales>
 
      <ModalAddExpensesWallet 
            :show="showModaldebtSalesAmanah ? true : false"
            :boxes="boxes"
            :sum_transactions="laravelData.sum_transactions"
            :sum_transactions_dinar="laravelData.sum_transactions_dinar"
            @a="confirmdebtAmanah($event)"
            @close="showModaldebtSalesAmanah = false"
            >
          <template #header>
            <h3 class="text-center">أمانة - سحب</h3>
            
           </template>
      </ModalAddExpensesWallet>
      <ModalConvertDollarDinar 
            :show="showModalConvertDollarDinar ? true : false"
            :boxes="boxes"
            @a="confirmConvertDollarDinar($event)"
            @close="showModalConvertDollarDinar = false"
            >
          <template #header>
            <h3 class="text-center">تحويل من الدولار للدينار</h3>
            
           </template>
      </ModalConvertDollarDinar>
      <ModalConvertDinarDollar 
            :show="showModalConvertDinarDollar ? true : false"
            :boxes="boxes"
            @a="confirmConvertDinarDollar ($event)"
            @close="showModalConvertDinarDollar = false"
            >
          <template #header>
            <h3 class="text-center">تحويل من الدينار للدولار</h3>
           </template>
      </ModalConvertDinarDollar>
    <div v-if="$page.props.success">
      <div
        id="alert-2"
        class="p-4 mb-4 bg-red-100 rounded-lg dark:bg-red-200 text-center"
        role="alert"
      >
        <div class="ml-3 font-medium text-red-700 dark:text-red-800">
          {{ $page.props.success }}
        </div>
      </div>
    </div>
    <div>
      <div class="max-w-9xl mx-auto sm:px-6 lg:px-8">
        <div class="overflow-hidden shadow-sm sm:rounded-lg">
          <div class=" border-b border-gray-200">
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3 lg:gap-3 mb-4">
              <div class="pt-5 print:hidden">
                <button style="width: 100%; margin-top: 4px;" v-if="$page.props.auth.user.type_id==1 || $page.props.auth.user.type_id==2 || $page.props.auth.user.type_id==5" 
                        className="px-4 py-2 text-white bg-green-800 rounded-md focus:outline-none hover:bg-green-900 transition"
                        @click="openAddSales()">
                  وصل قبض (إضافة)
                </button>
              </div>

              <div class="pt-5 print:hidden">
                <button style="width: 100%; margin-top: 4px;" v-if="$page.props.auth.user.type_id==1 || $page.props.auth.user.type_id==2|| $page.props.auth.user.type_id==5" 
                        className="px-4 py-2 text-white bg-red-800 rounded-md focus:outline-none hover:bg-red-900 transition"
                        @click="openAddExpenses()">
                  وصل صرف (سحب)
                </button>
              </div>

              <div class="pt-5 print:hidden">
                <button style="width: 100%; margin-top: 4px;" v-if="$page.props.auth.user.type_id==1 || $page.props.auth.user.type_id==2 || $page.props.auth.user.type_id==5" 
                        className="px-4 py-2 text-white bg-green-600 rounded-md focus:outline-none border-2 border-green-300 hover:bg-green-700 transition"
                        @click="openAddSalesAmanah()">
                  أمانة - إيداع
                </button>
              </div>

              <div class="pt-5 print:hidden">
                <button style="width: 100%; margin-top: 4px;" v-if="$page.props.auth.user.type_id==1 || $page.props.auth.user.type_id==2|| $page.props.auth.user.type_id==5" 
                        className="px-4 py-2 text-white bg-red-600 rounded-md focus:outline-none border-2 border-red-300 hover:bg-red-700 transition"
                        @click="opendebtSalesAmanah()">
                  أمانة - سحب
                </button>
              </div>

              <div class="pt-5 print:hidden">
                <button style="width: 100%; margin-top: 4px;" 
                        className="px-4 py-2 text-white bg-blue-600 rounded-md focus:outline-none hover:bg-blue-700 transition font-semibold"
                        @click="printAmanah()">
                  طباعة الأمانة
                </button>
              </div>

              <div class="pt-5 print:hidden">
                <button style="width: 100%; margin-top: 4px;" 
                        className="px-4 py-2 text-white bg-orange-600 rounded-md focus:outline-none hover:bg-orange-700 transition font-semibold"
                        @click="printWallet()">
                  طباعة الصندوق
                </button>
              </div>
            </div>
            <div class="grid grid-cols-4 md:grid-cols-4 lg:grid-cols-7 gap-3 lg:gap-3" v-if="false">
                        <div>
                          <button
                            type="button"
                            @click="openConvertDollarDinar()"
                            style="min-width:150px;"
                            className="px-6 mb-12 w-full py-2 font-bold text-white bg-teal-500 rounded">
                             تحويل دولار دينار  
                          </button>
                        </div>
                        <div>
                          <button
                            type="button"
                            @click="openConvertDinarDollar()"
                            style="min-width:150px;"
                            className="px-6 mb-12 w-full py-2 font-bold text-white bg-yellow-500 rounded">
                             تحويل دينار دولار  
                          </button>
                        </div>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-5 lg:grid-cols-6 gap-3 lg:gap-3">
              <div class=" px-4">
                            <div >
                              <InputLabel for="to" :value="`حساب ${boxes.name} بالدولار`" />
                              <TextInput
                                id="to"
                                type="number"
                                disabled
                                class="mt-1 block w-full"
                                :value="laravelData.sumInTransactionsUser-laravelData.sumOutTransactionsUser"
                              />
                            </div>
              </div>


              <div class=" px-4">
                            <div >
                              <InputLabel for="to" :value="`حساب ${boxes.name} بالدينار العراقي`" />
                              <TextInput
                                id="to"
                                type="number"
                                disabled
                                class="mt-1 block w-full"
                                :value="laravelData.sumInTransactionsDinarUser-laravelData.sumOutTransactionsDinarUser"
                              />
                            </div>
              </div>
              <div class=" px-4">
                            <div >
                              <InputLabel for="to" :value="`أمانة ${boxes.name} بالدولار`" />
                              <TextInput
                                id="to"
                                type="number"
                                disabled
                                class="mt-1 block w-full bg-blue-50"
                                :value="(laravelData.sumInTransactionsUserAmanah||0)-(laravelData.sumOutTransactionsUserAmanah||0)"
                              />
                            </div>
              </div>
              <div class=" px-4">
                            <div >
                              <InputLabel for="to" :value="`أمانة ${boxes.name} بالدينار العراقي`" />
                              <TextInput
                                id="to"
                                type="number"
                                disabled
                                class="mt-1 block w-full bg-blue-50"
                                :value="(laravelData.sumInTransactionsDinarUserAmanah||0)-(laravelData.sumOutTransactionsDinarUserAmanah||0)"
                              />
                            </div>
              </div>
      
            </div>
          
            <!-- مربع البحث -->
            <div class="mt-4 mb-4 px-4">
              <div class="max-w-md">
                <InputLabel for="search" value="بحث في الدفعات والأمانات" />
                <TextInput
                  id="search"
                  type="text"
                  class="mt-1 block w-full"
                  v-model="q"
                  placeholder="ابحث برقم الوصل أو الوصف..."
                  @input="debouncedGetResultsCar"
                />
              </div>
            </div>

            <div class="overflow-x-auto shadow-md mt-5">
              <table class="w-full text-right text-gray-500   dark:text-gray-400 text-center">
                <thead
                  class="text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400 text-center"
                >
                  <tr class="rounded-l-lg mb-2 sm:mb-0">
                    <th className="px-2 py-2">رقم الوصل</th>
                    <!-- <th className="px-2 py-2">الحساب</th> -->
                    <th className="px-2 py-2">التاريخ</th>
                    <th className="px-2 py-2">الوصف</th>
                    <th className="px-2 py-2">ايداع</th>
                    <th className="px-2 py-2">سحب</th>
                    <th className="px-2 py-2">الرصيد</th>
                    <th className="px-2 py-2">المرفقات</th>
                    <th className="px-2 py-2">تنفيذ</th>

                  </tr>
                </thead>
                <tbody>
         
                  <tr v-for="(tran, index) in   transactions" :key="tran.id" 
                      :class="[
                        tran.type == 'inUserAmanah' ? 'bg-blue-100 dark:bg-blue-900 border-l-4 border-blue-500' :
                        tran.type == 'outUserAmanah' ? 'bg-orange-100 dark:bg-orange-900 border-l-4 border-orange-500' :
                        tran.type != 'inUser' ? 'bg-red-100 dark:bg-red-900' : 'bg-green-100 dark:bg-green-900'
                      ]"  
                      class="bg-white border-b dark:bg-gray-900 dark:border-gray-900 hover:bg-gray-50 dark:hover:bg-gray-600">

                  <td className="border dark:border-gray-800 text-center px-2 py-1">
                    {{ tran.id }}
                    <span v-if="tran.type == 'inUserAmanah' || tran.type == 'outUserAmanah'" class="text-xs text-blue-600 dark:text-blue-300 font-bold">(أمانة)</span>
                  </td>
                  <!-- <td className="border dark:border-gray-800 text-center px-2 py-1">{{ tran.morphed?.name }}</td> -->

                  
                  <td className="border dark:border-gray-800 text-center px-2 py-1">{{ formatBaghdadTimestamp(tran?.created_at) }}</td>
                  <th className="border dark:border-gray-800 text-center px-2 py-1">{{ tran.description }}</th>
                  <td className="border dark:border-gray-800 text-center px-2 py-1">
                    {{ (tran.type == 'inUser' || tran.type == 'inUserAmanah') ? tran.amount+' '+tran.currency : '' }}
                  </td>
                  <td className="border dark:border-gray-800 text-center px-2 py-1">
                    {{ (tran.type == 'outUser' || tran.type == 'outUserAmanah') ? tran.amount+' '+tran.currency : '' }}
                  </td>
                  <td className="border dark:border-gray-800 text-center px-2 py-1">
                    <span v-if="tran.type == 'inUser' || tran.type == 'outUser'">
                      {{ updateResults(calculateBalance(tran, index)) }} {{ tran.currency }}
                    </span>
                    <span v-else class="text-gray-400">-</span>
                  </td>
                  <td className="border dark:border-gray-800 text-center px-2 py-1">
                    <div class="flex flex-wrap justify-center gap-1">
                      <a
                        v-for="(image, index) in tran.transactions_images || []"
                        :key="index"
                        :href="getDownloadUrl(image.name)"
                        style="cursor: pointer;"
                        target="_blank"
                        class="inline-block"
                      >
                        <img 
                          :src="getImageUrl(image.name)" 
                          alt="" 
                          class="rounded" 
                          style="max-width: 50px; max-height: 50px; display: inline;" 
                        />
                      </a>
                      <span v-if="!tran.transactions_images || tran.transactions_images.length === 0" class="text-gray-400 text-xs">
                        لا يوجد
                      </span>
                    </div>
                  </td>
                  <td className="border dark:border-gray-800 text-center px-2 py-1">
                    <div class="action-group">
                      <button 
                        class="action-btn action-btn--upload"
                        @click="openModalUploader(tran)" 
                        title="مرفقات الحركة"
                      >
                        <imags />
                      </button>
                      <a
                        v-if="tran.type == 'outUser'"
                        :href="`/getIndexAccounting?user_id=${props.boxes.id}&print=10&transactions_id=${tran.id}`"
                        target="_blank"
                        class="action-btn action-btn--print"
                        title="طباعة سند الصرف"
                      >
                        <print />
                      </a>
                      <a
                        v-if="tran.type == 'inUser'"
                        :href="`/getIndexAccounting?user_id=${props.boxes.id}&print=9&transactions_id=${tran.id}`"
                        target="_blank"
                        class="action-btn action-btn--print"
                        title="طباعة سند القبض"
                      >
                        <print />
                      </a>
                      <a
                        v-if="tran.type == 'outUserAmanah'"
                        :href="`/getIndexAccounting?user_id=${props.boxes.id}&print=12&transactions_id=${tran.id}`"
                        target="_blank"
                        class="action-btn action-btn--print"
                        title="طباعة سند صرف أمانة"
                      >
                        <print />
                      </a>
                      <a
                        v-if="tran.type == 'inUserAmanah'"
                        :href="`/getIndexAccounting?user_id=${props.boxes.id}&print=11&transactions_id=${tran.id}`"
                        target="_blank"
                        class="action-btn action-btn--print"
                        title="طباعة سند قبض أمانة"
                      >
                        <print />
                      </a>
                      <button 
                        class="action-btn action-btn--delete"
                        @click="openModalDel(tran)" 
                        title="حذف الحركة"
                      >
                        <trash />
                      </button>
                    </div>
                  </td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div class="spaner">
                          <InfiniteLoading :car="car" @infinite="getResults" :identifier="resetData" />

                      </div>

          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<style>
.td {
  max-width: 200px; /* can be 100% ellipsis will happen when contents exceed it */
  text-overflow: ellipsis;
  overflow: hidden;
  white-space: nowrap;
}

.action-group {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 0.5rem;
}

.action-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 2rem;
  height: 2rem;
  min-width: 2rem;
  min-height: 2rem;
  padding: 0.5rem;
  border-radius: 0.5rem;
  color: #fff;
  cursor: pointer;
  transition: transform 0.2s ease, filter 0.2s ease;
  border: none;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);
}

.action-btn:hover {
  transform: translateY(-1px);
  filter: brightness(1.05);
}

.action-btn:disabled,
.action-btn[disabled] {
  opacity: 0.5;
  cursor: not-allowed;
}

.action-btn svg {
  width: 1.25rem;
  height: 1.25rem;
  display: block;
  stroke: currentColor;
  fill: none;
}

.action-btn--upload {
  background: linear-gradient(135deg, #8b5cf6, #7c3aed);
}

.action-btn--delete {
  background: linear-gradient(135deg, #f43f5e, #e11d48);
}
</style>
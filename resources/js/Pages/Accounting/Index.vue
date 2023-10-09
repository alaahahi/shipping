<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Modal from "@/Components/Modal.vue";
import { Head, Link, useForm } from "@inertiajs/inertia-vue3";
import { ref } from "vue";
import ModalAddSales from "@/Components/ModalAddSales.vue";
import ModalAddDebt from "@/Components/ModalAddDebt.vue";
import ModalAddExpenses from "@/Components/ModalAddExpenses.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import ModalAddGenExpenses from "@/Components/ModalAddGenExpenses.vue";

import axios from 'axios';
import show from "@/Components/icon/show.vue";
import pay from "@/Components/icon/pay.vue";
import trash from "@/Components/icon/trash.vue";
import edit from "@/Components/icon/edit.vue";

const laravelData = ref({});
const user_id = ref(0);
const searchTerm = ref('');
let showModalAddSales = ref(false);
let showModaldebtSales = ref(false);
let showModalAddExpenses = ref(false);
let showModalAddGenExpenses = ref(false);
let expenses_type_id = ref(0);
let formData = ref({});

let isLoading=ref(false);
let from = ref(0);
let to = ref(0);
const getResults = async (page = 1) => {
  searchTerm.value = '';
  const response = await fetch(`/getIndexAccounting?page=${page}&user_id=${props.boxes[0].id}&from=${from.value}&to=${to.value}`);
  laravelData.value = await response.json();
};
function openAddSales() {
  showModalAddSales.value = true;
}
function opendebtSales() {
  showModaldebtSales.value = true;
}
function openAddExpenses(){
  showModalAddExpenses.value = true;
}
getResults();

const props = defineProps({
  url: String,
  users:Array,
  accounts:Array,
  boxes:Array,
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
      getResults();

};
const cancel = async (id) => {
  const response = await fetch(`/appointmentCancel?id=${id}`);
      getResults();

};

const results = (id) => {
  if(id==0){
        return 'إنتظار تسليم الصندوق';
    }
    if(id==1){
        return 'تم التسليم';
    }
  if (id == 2) {
    return "مكتمل";
  }
};
function sendToCourt(id) {
  showModal.value = id;
}
function method1(id) {
  form.get(route("sentToCourt", id));
  getResults();
  showModal.value = false;
}
const errors = ref(0);

const dateValue = ref({
    startDate: '',
    endDate: ''
})
const countComp = ref()
const formatter = ref({
  date: 'D/MM/YYYY',
  month: 'MM'
})
const options = ref({
  shortcuts: {
    today: 'اليوم',
    yesterday: 'البارحة',
    past: period => period + ' قبل يوم',
    currentMonth: 'الشهر الحالي',
    pastMonth: 'الشهر السابق'
  },
  footer: {
    apply: 'Terapkan',
    cancel: 'Batal'
  }
})
const dDate = (date) => {
  return date >= new Date() ;
}

function confirm(V) {
  axios.post('/api/receiptArrived',V)
  .then(response => {
    showModalAddSales.value=false;
    //getResults();
    window.location.reload();
  })
  .catch(error => {

    errors.value = error.response.data.errors
  })
}
function confirmdebt(V) {
  axios.post('/api/salesDebt',V)
  .then(response => {
    getResults();
    showModaldebtSales.value=false;
    showModalAddExpenses.value = false;
    window.location.reload();

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
  axios.post(`/api/delTransactions?id=${id}`)
  .then(response => {
    getResults();
    showModaldebtSales.value=false;
    showModalAddExpenses.value = false;
  })
  .catch(error => {

    errors.value = error.response.data.errors
  })
}
function openAddGenExpenses(v) {
    expenses_type_id.value=v
    showModalAddGenExpenses.value = true;
}
function conGenfirmExpenses(V) {
  fetch(`api/GenExpenses?amount=${V.amount??0}&expenses_type_id=${expenses_type_id.value}&factor=${V.factor??1}&note=${V.note??''}`)
    .then(() => {
      showModalAddGenExpenses.value = false;
      window.location.reload();

    })
    .catch((error) => {
      
      console.error(error);
    });
}
</script>

<template>
  <Head title="Dashboard" />
  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        المحاسبة
      </h2>
    </template>
    <ModalAddGenExpenses
            :formData="formData"
            :show="showModalAddGenExpenses ? true : false"
            :expenses_type_id="expenses_type_id"
            :user="user"
            @a="conGenfirmExpenses($event)"
            @close="showModalAddGenExpenses = false"
            >
        <template #header>
          </template>
    </ModalAddGenExpenses>
    <ModalAddSales
            :show="showModalAddSales ? true : false"
            :data="users"
            :accounts="accounts"
            @a="confirm($event)"
            @close="showModalAddSales = false"
            >
          <template #header>
            <h3 class="text-center">المحاسبة</h3>
            
           </template>
      </ModalAddSales>
      <ModalAddDebt
            :show="showModaldebtSales ? true : false"
            :data="users"
            :accounts="accounts"
            @a="confirmdebt($event)"
            @close="showModaldebtSales = false"
            >
          <template #header>
            
           </template>
      </ModalAddDebt>
      <ModalAddExpenses 
            :show="showModalAddExpenses ? true : false"
            :boxes="boxes"
            @a="confirmdebt($event)"
            @close="showModalAddExpenses = false"
            >
          <template #header>
            <h3 class="text-center">ادخال مصاريف اليومية</h3>
            
           </template>
      </ModalAddExpenses>
    <div v-if="$page.props.success">
      <div
        id="alert-2"
        class="p-4 mb-4 bg-red-100 rounded-lg dark:bg-red-200 text-center"
        role="alert"
      >
        <div class="ml-3 text-sm font-medium text-red-700 dark:text-red-800">
          {{ $page.props.success }}
        </div>
      </div>
    </div>
    <div class="py-12">
      <div class="max-w-9xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 bg-white border-b border-gray-200">
            <div class="grid grid-cols-4 md:grid-cols-4 lg:grid-cols-7 gap-3 lg:gap-3">
              <div class="pt-5  print:hidden">
              <button style=" width: 100%; margin-top: 4px;" v-if="$page.props.auth.user.type_id==1 || $page.props.auth.user.type_id==2 || $page.props.auth.user.type_id==5" className="px-4 py-2 text-white bg-rose-500 rounded-md focus:outline-none"
                                            @click="openAddSales()">
                                            وصل قبض
              </button>
              </div>

              <div class="pt-5  hidden">
              <button style=" width: 100%; margin-top: 4px;"  v-if="$page.props.auth.user.type_id==1 || $page.props.auth.user.type_id==2|| $page.props.auth.user.type_id==5" className="px-4 py-2 text-white bg-yellow-500 rounded-md focus:outline-none"
                                            @click="opendebtSales()">
                                             تحويل لحساب 
              </button>
              </div>
              
              <div class="pt-5  print:hidden">
              <button  style=" width: 100%; margin-top: 4px;"  v-if="$page.props.auth.user.type_id==1 || $page.props.auth.user.type_id==2|| $page.props.auth.user.type_id==5" className="px-4 py-2 text-white bg-blue-500 rounded-md focus:outline-none"
                                            @click="openAddExpenses()">
                                             اضافة مصاريف
              </button>
       
              </div>
              <div  class="pt-5">
                <a
                            class="px-2 mb-12 py-2 mt-1 font-bold text-white bg-pink-500 rounded" style="display: block;text-align: center;"
                            :href="route('transfers')"
                            target="_blank"
                            >
                            
                            <span v-if="!isLoading">الأرشيف</span>
                            <span v-else>جاري الحفظ...</span>
                          </a>
              </div>
              <div class=" px-4">
                          <div >
                              <InputLabel for="from" value="من تاريخ" />
                              <TextInput
                                id="from"
                                type="date"
                                class="mt-1 block w-full"
                                v-model="from"
                                
                              />
                            </div>
              </div>
              <div class=" px-4">
                            <div >
                              <InputLabel for="to" value="حتى تاريخ" />
                              <TextInput
                                id="to"
                                type="date"
                                class="mt-1 block w-full"
                                v-model="to"
                              />
                            </div>
              </div>
              <div className=" mr-5 print:hidden">
                            <InputLabel for="pay" value="فلترة" />
                            <button
                            @click.prevent="getResults()"
                            class="px-6 mb-12 py-2 mt-1 font-bold text-white bg-gray-500 rounded" style="width: 100%">
                            <span v-if="!isLoading">فلترة</span>
                            <span v-else>جاري الحفظ...</span>
                          </button>
              </div>
              <div className=" mr-5 print:hidden" >
                            <InputLabel for="pay" value="طباعة" />
                            <a
                            class="px-6 mb-12 py-2 mt-1 font-bold text-white bg-orange-500 rounded" style="display: block;text-align: center;"
                            :href="`/getIndexAccounting?user_id=${laravelData?.user?.id}&from=${from}&to=${to}&print=1`"
                            target="_blank"
                            >
                            
                            <span v-if="!isLoading">طباعة</span>
                            <span v-else>جاري الحفظ...</span>
                          </a>
              </div>

              
            </div>
            <div class="grid grid-cols-4 md:grid-cols-4 lg:grid-cols-7 gap-3 lg:gap-3">
              <div>
                          <button
                            type="button"
                            @click="openAddGenExpenses(1)"
                            style="min-width:150px;"
                            className="px-6 mb-12 py-2 font-bold text-white bg-red-500 rounded  w-full">
                               {{ $t('genExpenses') }}
                          </button>
                        </div>
                        <div>
                          <button
                            type="button"
                            @click="openAddGenExpenses(2)"
                            style="min-width:150px;"
                            className="px-6 mb-12 text-center py-2 font-bold text-white bg-blue-600 rounded  w-full">
                            {{ $t('dubai') }}
                          </button>
                        </div>
                        <div>
                          <button
                            type="button"
                            @click="openAddGenExpenses(3)"
                            style="min-width:150px;"
                            className="px-6 mb-12 text-center w-full py-2 font-bold text-white bg-blue-600 rounded">
                            {{ $t('iran') }}
                          </button>
                        </div>
                       <div>
                          <button
                            type="button"
                            @click="openAddGenExpenses(4)"
                            style="min-width:150px;"
                            className="px-6 mb-12 w-full py-2 font-bold text-white bg-indigo-600 rounded">
                            {{ $t('border') }} 
                          </button>
                        </div> 
                        <div>
                          <button
                            type="button"
                            @click="openAddGenExpenses(5)"
                            style="min-width:150px;"
                            className="px-6 mb-12 w-full py-2 font-bold text-white bg-pink-600 rounded">
                            {{ $t('shipping_coc') }} 
                          </button>
                        </div>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-5 lg:grid-cols-5 gap-3 lg:gap-3">
              <div class=" px-4">
                            <div >
                              <InputLabel for="to" value="حساب الصندوق بالدولار" />
                              <TextInput
                                id="to"
                                type="number"
                                disabled
                                class="mt-1 block w-full"
                                :value="laravelData.sum_transactions"
                              />
                            </div>
              </div>

              <div class=" px-4">
                            <div >
                              <InputLabel for="to" value="مسحوبات الصندوق بالدولار" />
                              <TextInput
                                id="to"
                                type="number"
                                disabled
                                class="mt-1 block w-full"
                                :value="laravelData.sum_transactions_debit"
                              />
                            </div>
              </div>

              <div class=" px-4">
                            <div >
                              <InputLabel for="to" value="دخل الصندوق بالدولار" />
                              <TextInput
                                id="to"
                                type="number"
                                disabled
                                class="mt-1 block w-full"
                                :value="laravelData.sum_transactions_in"
                              />
                            </div>
              </div>

              <div >
                              <InputLabel for="to" value="رصيد الصندوق بالدولار" />
                              <TextInput
                                id="to"
                                type="number"
                                disabled
                                class="mt-1 block w-full"
                                :value="laravelData?.user?.wallet.balance"
                              />
                </div>

            </div>
            <div class="grid grid-cols-2 md:grid-cols-5 lg:grid-cols-5 gap-3 lg:gap-3 pt-3">
              <div class=" px-4">
                            <div >
                              <InputLabel for="to" value="حساب الصندوق بالدينار العراقي" />
                              <TextInput
                                id="to"
                                type="number"
                                disabled
                                class="mt-1 block w-full"
                                :value="laravelData.sum_transactions_dinar"
                              />
                            </div>
              </div>
              <div class=" px-4">
                            <div >
                              <InputLabel for="to" value="مسحوبات الصندوق بالدينار العراقي" />
                              <TextInput
                                id="to"
                                type="number"
                                disabled
                                class="mt-1 block w-full"
                                :value="laravelData.sum_transactions_debit_dinar"
                              />
                            </div>
              </div>
              <div class=" px-4">
                            <div >
                              <InputLabel for="to" value="دخل الصندوق بالدينار العراقي" />
                              <TextInput
                                id="to"
                                type="number"
                                disabled
                                class="mt-1 block w-full"
                                :value="laravelData.sum_transactions_in_dinar"
                              />
                            </div>
              </div>
              <div >
                              <InputLabel for="to" value="رصيد الصندوق بالدينار العراقي" />
                              <TextInput
                                id="to"
                                type="number"
                                disabled
                                class="mt-1 block w-full"
                                :value="laravelData?.user?.wallet.balance_dinar"
                              />
                </div>
            </div>
            <!-- <div class="flex flex-row">
              <div class="basis-1/2 ">
                <select @change="getResults()" v-model="user_id" id="default" class="pr-8 bg-gray-50 border border-gray-300 text-gray-900 mb-6 text-sm rounded-lg focus:ring-red-500 focus:border-red-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-red-500 dark:focus:border-red-500">
                  <option value="0">الجميع</option>
                  <option v-for="(user, index) in users" :key="index" :value="user.id">{{ user.name }}</option>
                </select>
              </div>
            </div> -->

            <div class="overflow-x-auto shadow-md">
              <table class="w-full my-5">
                <thead
                  class="700 bg-rose-500 text-white text-center rounded-l-lg"
                >
                  <tr class="bg-rose-500 rounded-l-lg mb-2 sm:mb-0">
                    <th className="px-2 py-2">رقم الوصل</th>
                    <th className="px-2 py-2">التاريخ</th>
                    <th className="px-2 py-2">الوصف</th>
                    <th className="px-2 py-2">المبلغ</th>
                    <th className="px-2 py-2">تنفيذ</th>

                  </tr>
                </thead>
                <tbody>
                  <tr
                    v-for="user in laravelData.transactions"
                    :key="user.id"
                    class="hover:bg-gray-100 text-center"
                  >
                  <td className="border px-2 py-1">{{ user.id }}</td>
                  <td className="border px-2 py-1">{{ user?.created }}</td>
                  <th className="border px-2 py-1">{{ user.description }}</th>
                  <td className="border px-2 py-1">{{ user.amount+' '+user.currency  }}</td>
                  <td className="border px-2 py-1">
                    <button class="px-1 py-1 text-white bg-rose-500 rounded-md focus:outline-none hidden" @click="delTransactions(user.id)" >
                      <trash />
                    </button>
                  </td>
                  </tr>
                </tbody>
              </table>
            </div>
            <!-- <div class="mt-3 text-center" style="direction: ltr;">
              <TailwindPagination
                :data="laravelData"
                @pagination-change-page="getResults"
                :limit ="2"
              />
            </div> -->
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
</style>
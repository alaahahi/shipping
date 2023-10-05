<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import VueTailwindDatepicker from 'vue-tailwind-datepicker'
import Modal from "@/Components/Modal.vue";
import ModalAddCar from "@/Components/ModalAddCars.vue";
import ModalAddSale from "@/Components/ModalAddSale.vue";
import ModalAddExpenses from "@/Components/ModalAddExpenses.vue";
import ModalAddGenExpenses from "@/Components/ModalAddGenExpenses.vue";
import ModalAddToBox from "@/Components/ModalAddToBox.vue";
import ModalSpanFromBox from "@/Components/ModalSpanFromBox.vue";
import ModalAddTransfers from "@/Components/ModalAddTransfers.vue";
import ModalAddCarPayment from "@/Components/ModalAddCarPayment.vue";
import ModalDelCar from "@/Components/ModalDelCar.vue";
import { TailwindPagination } from "laravel-vue-pagination"
import { Head, Link, useForm } from '@inertiajs/inertia-vue3';
;
import { useToast } from "vue-toastification";
import axios from 'axios';
import { ref } from 'vue';
import { useI18n } from "vue-i18n";
const {t} = useI18n();



const props = defineProps({

  client:Array,

});


let data = ref({});
const laravelData = ref({});

const getResults = async (page = 1) => {
  axios.get(`/getIndexClients?page=${page}&q=debit`)
  .then(response => {
    try {
      laravelData.value =  response.data.Object.values(data)?.sort((a, b) => {
      // First, sort by wallet.balance in descending order
      const balanceComparison = b.wallet.balance - a.wallet.balance;

      // If wallet.balance is the same, sort by car_total_uncomplete in ascending order
  

      return balanceComparison;
    });
    } catch (error) {
      laravelData.value =  response.data.data
    }

  })
  .catch(error => {
    console.error(error);
  })
}
getResults();

let expenses_type_id = ref(0);

const toast = useToast();


let showModal = ref(false);




let searchTerm = ref('');

let showModalCar =  ref(false);
let showModalCarSale =  ref(false);
let showModalAddExpenses =  ref(false);
let showModalAddGenExpenses =  ref(false);
let showModalToBox =  ref(false);
let showModalFromBox =  ref(false);
let showModalAddTransfers =  ref(false);
let showModalAddCarPayment =  ref(false);
let showModalDelCar =  ref(false);
let mainAccount= ref(0)
let onlineContracts= ref(0)
let howler= ref(0)
let shippingCoc= ref(0)
let border= ref(0)
let iran= ref(0)
let dubai= ref(0)
let debtOnlineContracts= ref(0)

let allCars= ref(0)

function openModal() {
  showModal.value = true;
}

function openModalDelCar(form={}) {
  formData.value=form
  showModalDelCar.value = true;
}

function openAddCar(form={}) {
    formData.value=form
    showModalCar.value = true;
}
function openSaleCar(form={}) {
    formData.value=form
    showModalCarSale.value = true;
}
function openAddExpenses(form={}) {
    formData.value=form
    showModalAddExpenses.value = true;
}
function openAddGenExpenses(v) {
    expenses_type_id.value=v
    showModalAddGenExpenses.value = true;
}
function openAddToBox(form={}) {
    formData.value=form
    showModalToBox.value = true;
}
function openAddFromBox(form={}) {
    formData.value=form
    showModalFromBox.value = true;
}
function openAddTransfers(form={}) {
    formData.value=form
    showModalAddTransfers.value = true;
}
function openAddCarPayment(form={}) {
    formData.value=form
    showModalAddCarPayment.value = true;
}
const formData = ref({});
const formGenExpenses = ref({});
const car = ref([]);


const dateValue = ref({
    startDate: '',
    endDate: ''
})
const countComp = ref()
const formatter = ref({
  date: 'D/MM/YYYY',
  month: 'MM'
})
const getResultsCar = async (user_id='',page = 1) => {
    const response = await fetch(`/getIndexCar?page=${page}&user_id=${user_id}`);
    car.value = await response.json();
}
const getResultsCarSearch = async (q='',page = 1) => {
    const response = await fetch(`/getIndexCarSearch?page=${page}&q=${q}`);
    car.value = await response.json();
}
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
const getcountTotalInfo = async () => {
  axios.get('/api/totalInfo')
  .then(response => {
    mainAccount.value = response.data.data.mainAccount;
    onlineContracts.value=  response.data.data.onlineContracts
    howler.value=  response.data.data.howler
    shippingCoc.value=  response.data.data.shippingCoc
    border.value=  response.data.data.border
    iran.value=  response.data.data.iran
    dubai.value=  response.data.data.dubai
    debtOnlineContracts.value=  response.data.data.debtOnlineContracts
    allCars.value =response.data.data.allCars;
  })
  .catch(error => {
    console.error(error);
  })
  
    
}
getcountTotalInfo()
function confirmCar(V) {
  axios.post('/api/addCars',V)
  .then(response => {
    showModalCar.value = false;
    getcountTotalInfo()
  })
  .catch(error => {
    console.error(error);
  })
}
function confirmUpdateCar(V) {
  axios.post('/api/updateCars',V)
  .then(response => {
    showModal.value = false;
    toast.success("تم التعديل بنجاح", {
        timeout: 2000,
        position: "bottom-right",
        rtl: true

      });
      getcountTotalInfo()

  })
  .catch(error => {
    showModal.value = false;

    toast.error("لم التعديل بنجاح", {
        timeout: 2000,
        position: "bottom-right",
        rtl: true

      });
  })
}

function confirmPayCar(V) {
  axios.post('/api/payCar',V)
  .then(response => {
    showModalCarSale.value = false;
      window.location.reload();
  })
  .catch(error => {
    console.error(error);
  })
}
function confirmExpenses(V) {
  fetch(`/addExpenses?car_id=${V.id}&user_id=${V.user_id}&expenses_id=${V.expenses_id}&expens_amount=${V.expens_amount??0}&note=${V.noteExpenses??''}`)
    .then(() => {
      showModalAddExpenses.value = false;
       window.location.reload();
    })
    .catch((error) => {
      console.error(error);
    });
}
function conGenfirmExpenses(V) {
  fetch(`/GenExpenses?amount=${V.amount??0}&expenses_type_id=${expenses_type_id.value}&factor=${V.factor??1}&note=${V.note??''}`)
    .then(() => {
      showModalAddGenExpenses.value = false;
      window.location.reload();

    })
    .catch((error) => {
      
      console.error(error);
    });
}
function conAddTransfers(V) {
  fetch(`/addTransfers?user_id=${V.user_id}&amount=${V.amount??0}&note=${V.note??''}`)
    .then(() => {
      showModalAddTransfers.value = false;
       window.location.reload();
    })
    .catch((error) => {
      console.error(error);
    });
}
function confirmAddToBox(V) {
  fetch(`/addToBox?amount=${V.amount??0}&note=${V.note??''}`)
    .then(() => {
      showModalToBox.value = false;
      window.location.reload();

    })
    .catch((error) => {
      console.error(error);
    });
}
function confirmWithDrawFromBox(V) {
  fetch(`/withDrawFromBox?amount=${V.amount??0}&note=${V.note??''}`)
    .then(() => {
      showModalFromBox.value = false;
      window.location.reload();

    })
    .catch((error) => {
      console.error(error);
    });
}
function confirmAddPayment(V) {
  fetch(`/addPaymentCar?car_id=${V.id}&user_id=${V.user_id}&amount=${V.amountPayment??0}&note=${V.notePayment??''}`)
    .then(() => {
      showModalFromBox.value = false;
      window.location.reload();

    })
    .catch((error) => {
      console.error(error);
    });
}
function confirmDelCar(V) {
  axios.post('/api/DelCar',V)
  .then(response => {
    showModalDelCar.value = false;
      window.location.reload();
  })
  .catch(error => {
    console.error(error);
  })


}
function getDarkModePreference() {
  const darkModePreference = localStorage.getItem('darkMode');
  return darkModePreference==='true' ?'darkCompact':'compact'; // Convert the string to a boolean
}
</script>

<template>
    <Head title="Dashboard" />
    <Modal
            :data="data"
            :show="showModal ? true : false"
            :carModel="carModel"
            @a="confirmUpdateCar($event)"
            @close="showModal = false"
            >
        <template #header>
          <h2 class="text-center" style="font-size:20px;">
            هل متأكد من تعديل البيانات
          </h2>
        </template>
    </Modal>
    <ModalAddCar
            :formData="formData"
            :show="showModalCar ? true : false"
            :client="client"
            :carModel="carModel"
            @a="confirmCar($event)"
            @close="showModalCar = false"
            >
        <template #header>
          </template>
    </ModalAddCar>
    <ModalAddSale
            :formData="formData"
            :show="showModalCarSale ? true : false"
            :company="company"
            :name="name"
            :color="color"
            :carModel="carModel"
            :client="client"
            @a="confirmPayCar($event)"
            @close="showModalCarSale = false"
            >
        <template #header>
          </template>
    </ModalAddSale>
    <ModalAddExpenses
            :formData="formData"
            :expenses="expenses"
            :show="showModalAddExpenses ? true : false"
            :user="user"
            @a="confirmExpenses($event)"
            @close="showModalAddExpenses = false"
            >
        <template #header>
          </template>
    </ModalAddExpenses>
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
    <ModalAddToBox
            :formData="formData"
            :expenses="expenses"
            :show="showModalToBox ? true : false"
            :user="user"
            @a="confirmAddToBox($event)"
            @close="showModalToBox = false"
            >
        <template #header>
          </template>
    </ModalAddToBox>
    <ModalSpanFromBox
            :formData="formData"
            :expenses="expenses"
            :show="showModalFromBox ? true : false"
            :user="user"
            @a="confirmWithDrawFromBox($event)"
            @close="showModalFromBox = false"
            >
        <template #header>
          </template>
    </ModalSpanFromBox>
    <ModalAddTransfers
            :formData="formData"
            :expenses="expenses"
            :show="showModalAddTransfers  ? true : false"
            :user="user"
            @a="conAddTransfers($event)"
            @close="showModalAddTransfers = false"
            >
        <template #header>
          </template>
    </ModalAddTransfers>
    <ModalAddCarPayment
            :formData="formData"
            :show="showModalAddCarPayment ? true : false"
            :user="user"
            @a="confirmAddPayment($event)"
            @close="showModalAddCarPayment = false"
            >
        <template #header>
          </template>
    </ModalAddCarPayment>

    <ModalDelCar
            :show="showModalDelCar ? true : false"
            :formData="formData"
            @a="confirmDelCar($event)"
            @close="showModalDelCar = false"
            >
          <template #header>
          هل متأكد من حذف السيارة
          ؟
          </template>
    </ModalDelCar>
    <AuthenticatedLayout >
        <div class="py-2"  v-if="$page.props.auth.user.type_id==1">
        <div class="max-w-9xl mx-auto sm:px-6 lg:px-8 ">
            <div class="bg-white overflow-hidden shadow-sm ">
                <div class="p-6  dark:bg-gray-900">
                    <div class="flex flex-col">
                      <div class="grid grid-cols-2 md:grid-cols-6 lg:grid-cols-7 gap-2 lg:gap-1" >
                        <!-- <div>
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
                                v-model="searchTerm"
                                @input="getResultsCarSearch(searchTerm)"
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
                        </div> -->
                       <div>
                          <button
                            type="button"
                            @click="openAddGenExpenses(1)"
                            style="min-width:150px;"
                            className="px-6 mb-12 mx-2 py-2 font-bold text-white bg-red-500 rounded">
                               {{ $t('genExpenses') }}
                          </button>
                        </div>
                        <div>
                          <button
                            type="button"
                            @click="openAddGenExpenses(2)"
                            style="min-width:150px;"
                            className="px-6 mb-12 text-center mx-2 py-2 font-bold text-white bg-blue-600 rounded">
                            {{ $t('dubai') }}
                          </button>
                        </div>
                        <div>
                          <button
                            type="button"
                            @click="openAddGenExpenses(3)"
                            style="min-width:150px;"
                            className="px-6 mb-12 text-center mx-2 py-2 font-bold text-white bg-blue-600 rounded">
                            {{ $t('iran') }}
                          </button>
                        </div>
                       <div>
                          <button
                            type="button"
                            @click="openAddGenExpenses(4)"
                            style="min-width:150px;"
                            className="px-6 mb-12 mx-2 py-2 font-bold text-white bg-indigo-600 rounded">
                            {{ $t('border') }} 
                          </button>
                        </div> 
                        <div>
                          <button
                            type="button"
                            @click="openAddGenExpenses(5)"
                            style="min-width:150px;"
                            className="px-6 mb-12 mx-2 py-2 font-bold text-white bg-pink-600 rounded">
                            {{ $t('shipping_coc') }} 
                          </button>
                        </div>
                        <!-- <div class="text-center">
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
                      <div>
                    
                        <div class="relative overflow-x-auto shadow-md sm:rounded-lg" v-if="false">
                
                        </div>
             
                      </div>
                      <div>
                        <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-4 lg:grid-cols-5 xl:grid-cols-7">     
                          
                          <div class="flex items-start rounded-xl dark:bg-gray-600 dark:text-gray-300 bg-white p-4 shadow-lg">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full border border-orange-100 bg-orange-50">
                              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                              </svg>
                            </div>
                            <div class="mr-4" >
                              <h2 class="font-semibold ">{{ $t('capital') }}</h2>
                              <p class="mt-2 text-sm text-gray-500 dark:text-gray-200">{{ mainAccount }}</p>
                            </div>
                          </div>
                          <!-- <div class="flex items-start rounded-xl dark:bg-gray-600 dark:text-gray-300 bg-white p-4 shadow-lg">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full border border-red-100 bg-red-50">
                              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                              </svg>
                            </div>
                      
                            <div class="mr-4">
                              <h2 class="font-semibold"> {{ $t('fundIncome') }} </h2>
                              <p class="mt-2 text-sm text-gray-500 dark:text-gray-200">{{ inAccount.wallet?.balance }}</p>
                            </div>
                          </div> -->
                          <!-- <div class="flex items-start rounded-xl dark:bg-gray-600 dark:text-gray-300 bg-white p-4 shadow-lg">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full border border-red-100 bg-red-50">
                              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                              </svg>
                            </div>
                      
                            <div class="mr-4">
                              <h2 class="font-semibold">{{ $t('cash_out') }}</h2>
                              <p class="mt-2 text-sm text-gray-500 dark:text-gray-200">{{ outAccount.wallet?.balance }}</p>
                            </div>
                          </div>
                          <div class="flex items-start rounded-xl dark:bg-gray-600 dark:text-gray-300 bg-white p-4 shadow-lg">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full border border-red-100 bg-red-50">
                              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                              </svg>
                            </div>
                      
                            <div class="mr-4">
                              <h2 class="font-semibold">{{ $t('debt_to_fund') }}</h2>
                              <p class="mt-2 text-sm text-gray-500 dark:text-gray-200">{{ debtAccount.wallet?.balance }}</p>
                            </div>
                          </div>
                          <div class="flex items-start rounded-xl dark:bg-gray-600 dark:text-gray-300 bg-white p-4 shadow-lg">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full border border-red-100 bg-red-50">
                              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                              </svg>
                            </div>
                      
                            <div class="mr-4">
                              <h2 class="font-semibold">{{ $t('transfer') }}</h2>
                              <p class="mt-2 text-sm text-gray-500 dark:text-gray-200">{{ transfersAccount.wallet?.balance }}</p>
                            </div>
                          </div>
                          <div class="flex items-start rounded-xl dark:bg-gray-600 dark:text-gray-300 bg-white p-4 shadow-lg">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full border border-red-100 bg-red-50">
                              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                              </svg>
                            </div>
                            <div class="mr-4">
                              <h2 class="font-semibold">{{ $t('total_car_count') }}</h2>
                              <p class="mt-2 text-sm text-gray-500 dark:text-gray-200">{{carCount}}</p>
                            </div>
                          </div> -->
                          <div class="flex items-start rounded-xl dark:bg-gray-600 dark:text-gray-300 bg-white p-4 shadow-lg">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full border border-red-100 bg-red-50">
                              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                              </svg>
                            </div>
                            <div class="mr-4">
                              <h2 class="font-semibold">{{ $t('genExpenses') }}</h2>
                              <p class="mt-2 text-sm text-gray-500 dark:text-gray-200">{{howler}}</p>
                            </div>
                          </div>
                          <div class="flex items-start rounded-xl dark:bg-gray-600 dark:text-gray-300 bg-white p-4 shadow-lg">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full border border-red-100 bg-red-50">
                              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                              </svg>
                            </div>
                            <div class="mr-4">
                              <h2 class="font-semibold">{{ $t('dubai') }}</h2>
                              <p class="mt-2 text-sm text-gray-500 dark:text-gray-200">{{dubai}}</p>
                            </div>
                          </div>
                          <div class="flex items-start rounded-xl dark:bg-gray-600 dark:text-gray-300 bg-white p-4 shadow-lg">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full border border-red-100 bg-red-50">
                              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                              </svg>
                            </div>
                            <div class="mr-4">
                              <h2 class="font-semibold">{{ $t('iran') }}</h2>
                              <p class="mt-2 text-sm text-gray-500 dark:text-gray-200">{{iran}}</p>
                            </div>
                          </div>
                          <div class="flex items-start rounded-xl dark:bg-gray-600 dark:text-gray-300 bg-white p-4 shadow-lg">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full border border-red-100 bg-red-50">
                              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                              </svg>
                            </div>
                            <div class="mr-4">
                              <h2 class="font-semibold">{{ $t('border') }}</h2>
                              <p class="mt-2 text-sm text-gray-500 dark:text-gray-200">{{border}}</p>
                            </div>
                          </div>
                          <div class="flex items-start rounded-xl dark:bg-gray-600 dark:text-gray-300 bg-white p-4 shadow-lg">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full border border-red-100 bg-red-50">
                              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                              </svg>
                            </div>
                            <div class="mr-4">
                              <h2 class="font-semibold">{{ $t('shipping_coc') }}</h2>
                              <p class="mt-2 text-sm text-gray-500 dark:text-gray-200">{{shippingCoc}}</p>
                            </div>
                          </div>
                          <div class="flex items-start rounded-xl dark:bg-gray-600 dark:text-gray-300 bg-white p-4 shadow-lg">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full border border-red-100 bg-red-50">
                              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                              </svg>
                            </div>
                            <div class="mr-4">
                              <h2 class="font-semibold">{{ $t('online_contracts') }}</h2>
                              <p class="mt-2 text-sm text-gray-500 dark:text-gray-200">{{onlineContracts}}</p>
                            </div>
                          </div>
                          <Link  v-for="(user,i) in laravelData" :key="i" class="flex items-start rounded-xl text-gray-200  dark:text-gray-300  p-4 shadow-lg"  :href="route('showClients', user.id)"  :class="user.car_total_uncomplete?'bg-red-500  dark:bg-red-500':'bg-green-600  dark:bg-green-600'">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full border border-red-100 bg-red-50">
                              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                              </svg>
                            </div>
                            <div class="mr-4">
                              <h2 class="font-semibold">{{ user.name}}</h2>
                              <p class="mt-2 text-sm text-gray-200  dark:text-gray-200">{{ user.wallet ? '$'+user.wallet['balance']:0  }}
                                <span class="inline-flex items-center justify-center w-4 h-4 ml-2 text-xs font-semibold text-blue-800 bg-blue-200 rounded-full">
                                {{ user.car_total_un_pay}}
                              </span>
                              </p>
                            
                            </div>
                          </Link>


                          <!-- <div class="flex items-start rounded-xl dark:bg-gray-600 dark:text-gray-300 bg-white p-4 shadow-lg">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full border border-red-100 bg-red-50">
                              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                              </svg>
                            </div>
                      
                            <div class="mr-4">
                              <h2 class="font-semibold">{{ $t('supplier_debt') }}</h2>
                              <p class="mt-2 text-sm text-gray-500 dark:text-gray-200">{{ debtSupplier.wallet?.balance }}</p>
                            </div>
                          </div>
                          <div class="flex items-start rounded-xl dark:bg-gray-600 dark:text-gray-300 bg-white p-4 shadow-lg">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full border border-red-100 bg-red-50">
                              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                              </svg>
                            </div>
                      
                            <div class="mr-4">
                              <h2 class="font-semibold">{{ $t('supplier_payments') }}</h2>
                              <p class="mt-2 text-sm text-gray-500 dark:text-gray-200">{{ outSupplier.wallet?.balance }}</p>
                            </div>
                          </div> -->
                        </div>
                      </div>
                      </div>
                    </div>
                    </div>
                </div>
            </div>
          <div >
          <!-- <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                  <div class="p-6  dark:bg-gray-900" style="border-radius: 8px;">
                    <div class="flex flex-row">
                                      <div class="basis-1/4">
                                        <button
                                          type="button"
                                          @click="getcountComp()"
                                          style="width: 70%;"
                                          className="px-6 mb-12 mx-2 py-2 font-bold text-white bg-rose-500 rounded"
                                        >
                                        فلترة
                                        </button>
                                      </div>
                                      <div class="basis-3/4" style="direction: ltr;">
                                        <vue-tailwind-datepicker overlay :options="options" :disable-date="dDate"  i18n="ar"  as-single use-range v-model="dateValue" />
                                      </div>
                    </div>
                    <div class="flex pt-5 items-center">
                    <div class="mx-auto container align-middle">
                          <div class="grid grid-cols-2 gap-2" style="display: flow-root;">
                            <div class="shadow rounded-lg py-3 px-5 bg-white" >
                              <div class="flex flex-row justify-between items-center">
                                <div>
                                  <h6 class="text-2xl">المعاملات المنجزة </h6>
                                  <h4 class="text-black text-4xl font-bold text-rigth">{{countComp}}</h4>
                                </div>
                                <div>
                                  <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-12 w-12"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="#14B8A6"
                                    stroke-width="2"
                                  >
                                    <path
                                      stroke-linecap="round"
                                      stroke-linejoin="round"
                                      d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"
                                    />
                                  </svg>
                                </div>
                              </div>
                              <div class="text-left flex flex-row justify-start items-center">
                                <span class="mr-1">
                                  <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-6 w-6"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="#14B8A6"
                                    stroke-width="2"
                                  >
                                    <path
                                      stroke-linecap="round"
                                      stroke-linejoin="round"
                                      d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"
                                    />
                                  </svg>
                                </span>
                              
                              </div>
                            </div>
                            <div class="shadow rounded-lg py-3 px-5 bg-white" v-if="false">
                              <div class="flex flex-row justify-between items-center">
                                <div>
                                  <h6 class="text-2xl">Serials viewed</h6>
                                  <h4 class="text-black text-4xl font-bold text-left">41</h4>
                                </div>
                                <div>
                                  <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-12 w-12"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="#EF4444"
                                    stroke-width="2"
                                  >
                                    <path
                                      stroke-linecap="round"
                                      stroke-linejoin="round"
                                      d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"
                                    />
                                  </svg>
                                </div>
                              </div>
                              <div class="text-left flex flex-row justify-start items-center">
                                <span class="mr-1">
                                  <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-6 w-6"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="#EF4444"
                                    stroke-width="{2}"
                                  >
                                    <path
                                      stroke-linecap="round"
                                      stroke-linejoin="round"
                                      d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"
                                    />
                                  </svg>
                                </span>
                                <p><span class="text-red-500 font-bold">12%</span> in 7 days</p>
                              </div>
                            </div>
                          </div>
                        </div>
                    </div>
                  </div>
                      </div> -->
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


</style>

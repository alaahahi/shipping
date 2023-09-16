<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/inertia-vue3';
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
import ModalEditCars from "@/Components/ModalEditCar_S.vue";

import RevoGrid  from "@revolist/vue3-datagrid";
import { TailwindPagination } from "laravel-vue-pagination";
import { useToast } from "vue-toastification";
import axios from 'axios';
import { ref } from 'vue';
import Plugin from "@revolist/revogrid-column-date";
import { useI18n } from "vue-i18n";
const {t} = useI18n();

import NumberColumnType from '@revolist/revogrid-column-numeral'; // import library


const props = defineProps({

  client:Array,

});


let data = ref({});
const columnTypes = ref({ 'date': new Plugin(),'numeric': new NumberColumnType('0,0') });
const toast = useToast();
const columns = [
  { prop: "no", name: t('no'),size:50,  },
  { prop: "client", name:  t('car_owner') ,size:150,readonly: true},
  { prop: "car_type", name:  t('car_type') },
  { prop: "year", name:  t('year'), size:70},
  { prop: 'car_color',name:  t('car_color')},
  { prop: "vin", name:  t('vin'),size:150 },
  { prop: "car_number", name:  t('car_number')},
  { prop: "dinar", name:  t('dinar'), columnType: 'numeric'  },
  { prop: "dolar_price", name:  t('dolar_price') ,columnType: 'numeric'   },
  {
    prop: "dolar_custom",
    name:  t('dolar_custom'),
    columnType: 'numeric',
    readonly: true, // Set the column as readonly
    cellTemplate: (createElement,props) => {
      const dinar = props.data[props.rowIndex].dinar || 0;
      const dolar_price = props.data[props.rowIndex].dolar_price || 0;
      return (dinar / dolar_price).toFixed(2) || 0;
    },
  },
  { prop: "shipping_dolar", name:  t('shipping_dolar'),columnType: 'numeric'  },
  { prop: "coc_dolar", name:  t('coc_dolar'),columnType: 'numeric'  },
  { prop: "checkout", name:  t('checkout') ,columnType: 'numeric' },
  { prop: "expenses", name:  t('expenses') ,columnType: 'numeric' },
  {
    prop: "total",
    name:  t('total'),
    columnType: 'numeric',
    readonly: true, // Set the column as readonly
    cellTemplate: (createElement,props) => {
      const checkout = props.data[props.rowIndex].checkout || 0;
      const shipping = props.data[props.rowIndex].shipping_dolar || 0;
      const coc_dolar = props.data[props.rowIndex].coc_dolar || 0;
      const dinar = props.data[props.rowIndex].dinar || 0;
      const dolar_price = props.data[props.rowIndex].dolar_price || 0;
      const expenses = props.data[props.rowIndex].expenses || 0;
      return ((checkout + shipping+coc_dolar+expenses+(dinar/dolar_price)||0).toFixed(0));
    },
  },
  { prop: "paid", name:  t('paid') ,columnType: 'numeric' },
  {
    prop: "profit",
    name:  t('profit'),
    columnType: 'numeric',
    readonly: true, // Set the column as readonly
  },
  { prop: "date", name:  t('date'),columnType: "date",size: 130, },
  { prop: "note", name:  t('note') },

];
  const handleEdit = (event) => {

  const rowIndex = event.detail.rowIndex;
  const colIndex = (event.detail.prop).replace(/["\s]/g, '');
  const newValue = event.detail.val;
  const id =car.value.data[rowIndex].id;
  //car.value.data[rowIndex][colIndex] = newValue;

  // You can save the changes to a backend here if needed
  saveChangesToBackend(id,colIndex,newValue);
};
let showModal = ref(false);

const saveChangesToBackend = (id,colIndex,newValue) => {
  data.value={'id':id,[colIndex]:newValue}
  showModal.value=true;
  // Simulated function to save changes to a backend
};


let searchTerm = ref('');

let showModalCar =  ref(false);
let showModalCarSale =  ref(false);
let showModalAddExpenses =  ref(false);
let showModalAddGenExpenses =  ref(false);
let showModalToBox =  ref(false);
let showModalFromBox =  ref(false);
let showModalAddTransfers =  ref(false);
let showModalAddCarPayment =  ref(false);
let showModalEditCars=ref(false);
let showModalDelCar =  ref(false);
let mainAccount= ref(0)
let allCars= ref(0)

function openModal() {
  showModal.value = true;
}
function openModalEditCars(form={}){
  formData.value=form
  showModalEditCars.value = true;
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
function openAddGenExpenses(form={}) {
    formGenExpenses.value=form
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
const getResultsCar = async (page = 1,user_id='') => {
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
    getResultsCar()
    getcountTotalInfo()
  })
  .catch(error => {
    console.error(error);
  })
}
function confirmUpdateCar(V) {
  showModalEditCars.value = false;

  axios.post('/api/updateCarsS',V)
  .then(response => {
    showModal.value = false;
    toast.success("تم التعديل بنجاح", {
        timeout: 2000,
        position: "bottom-right",
        rtl: true

      });

      getcountTotalInfo()
      getResultsCar();

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
  fetch(`/GenExpenses?user_id=${V.user_id}&amount=${V.amount??0}&reason=${V.reason??''}&note=${V.note??''}`)
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
getResultsCar();

function confirmAddPayment(V) {
  axios.get(`/api/addPaymentCar?car_id=${V.id}&amount=${V.amountPayment??0}&note=${V.notePayment??''}`)
  .then(response => {
    showModalAddCarPayment.value = false;
    toast.success( " تم دفع مبلغ دولار "+V.amountPayment+" بنجاح ", {
        timeout: 3000,
        position: "bottom-right",
        rtl: true

      });
    window.location.reload();


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
    <ModalEditCars
            :formData="formData"
            :show="showModalEditCars ? true : false"
            :client="client"
            :carModel="carModel"
            @a="confirmUpdateCar($event)"
            @close="showModalEditCars = false"
            >
        <template #header>
          </template>
    </ModalEditCars>
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
            <h2 class=" mb-5 dark:text-white text-center">

          هل متأكد من حذف السيارة
          ؟
          </h2>
          </template>
    </ModalDelCar>

    <AuthenticatedLayout>
        <div class="py-2">
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
                            <select @change="getResultsCar(1,user_id)" v-model="user_id" id="default" class="pr-8 bg-gray-50 border border-gray-300 text-gray-900 mb-6 text-sm rounded-lg focus:ring-red-500 focus:border-red-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-red-500 dark:focus:border-red-500">
                              <option value="undefined" disabled> {{ $t("selectCustomer") }}</option>
                              <option value="">{{ $t("allOwners") }}</option>
                              <option v-for="(user, index) in client" :key="index" :value="user.id">{{ user.name }}</option>
                            </select>
                        </div>
        
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
                        </div>

                      </div>
                      <div>
                        <div>
                        <div class="mt-3 text-center" style="direction: ltr;">
                          <TailwindPagination
                            :data="car"
                            @pagination-change-page="getResultsCar"
                            :limit ="10"
                            :item-classes="['bg-white','dark:bg-gray-600','text-gray-500','dark:text-gray-300','border-gray-300','dark:border-gray-900','hover:bg-gray-200']"
                            :activeClasses="[  'bg-rose-50','border-rose-500','text-rose-600',]"
                          />
                        </div>

                        </div>
                        <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                          <table class="w-full text-sm text-right text-gray-500 dark:text-gray-200 dark:text-gray-400 text-center">
                              <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400 text-center" >
                                  <tr>
                                      <th scope="col" class="px-1 py-3 text-base	" >
                                        {{ $t('no') }}  
                                      </th>
                                      <th scope="col" class="px-1 py-3 text-base	">
                                        {{ $t('car_owner') }}
                                      </th>
                                      <th scope="col" class="px-1 py-3 text-base">
                                        {{ $t('car_type') }}
                                      </th>
                                      <th scope="col" class="px-1 py-3 text-base">
                                        {{ $t('year') }}
                                      </th>
                                      <th scope="col" class="px-1 py-3 text-base">
                                        {{ $t('color') }}
                                      </th>
                                      <th scope="col" class="px-1 py-3 text-base">
                                        {{ $t('vin') }}
                                      </th>
                                      <th scope="col" class="px-1 py-3 text-base">
                                        {{ $t('car_number') }}
                                      </th>
                                      <th scope="col" class="px-1 py-3 text-base">
                                        {{ $t('dinar') }}
                                      </th>
                                      <th scope="col" class="px-1 py-3 text-base">
                                        {{ $t('dolar_price') }}
                                      </th>
                                      <th scope="col" class="px-1 py-3 text-base">
                                        {{ $t('dolar_custom') }}
                                      </th>
                                      <th scope="col" class="px-1 py-3 text-base">
                                        {{ $t('note') }}
                                      </th>
                                      <th scope="col" class="px-1 py-3 text-base">
                                        {{ $t('shipping_dolar') }}
                                      </th>
                                      <th scope="col" class="px-1 py-3 text-base">
                                        {{ $t('coc_dolar') }}
                                      </th>
                                      <th scope="col" class="px-1 py-3 text-base">
                                        {{ $t('checkout') }}
                                      </th>
                                      <th scope="col" class="px-1 py-3 text-base">
                                        {{ $t('expenses') }}
                                      </th>
                                      <th scope="col" class="px-1 py-3 text-base">
                                        {{ $t('total') }}
                                      </th>
                                      <th scope="col" class="px-1 py-3 text-base">
                                        {{ $t('paid') }}
                                      </th>
                                  
                                      <th scope="col" class="px-1 py-3 text-base">
                                        {{ $t('date') }}
                                      </th>
                
                                      <th scope="col" class="px-1 py-3 text-base" style="width: 250px;">
                                        {{ $t('execute') }}
                                      </th>
                                  </tr>
                              </thead>
                              <tbody>


                                <tr v-for="car in car.data" :key="car.id" :class="car.results == 0 ?'':car.results == 1 ?'bg-red-100 dark:bg-red-900':'bg-green-100 dark:bg-green-900'"  class="bg-white border-b dark:bg-gray-900 dark:border-gray-900 hover:bg-gray-50 dark:hover:bg-gray-600">
                                    <td className="border dark:border-gray-800 text-center px-2 py-2 ">{{ car.no }}</td>
                                    <td className="border dark:border-gray-800 text-center px-2 py-2 ">{{ car.client?.name }}</td>
                                    <td className="border dark:border-gray-800 text-center px-2 py-2 ">{{ car.car_type}}</td>
                                    <td className="border dark:border-gray-800 text-center px-2 py-2 ">{{ car.year}}</td>
                                    <td className="border dark:border-gray-800 text-center px-2 py-2 ">{{ car.car_color }}</td>
                                    <td className="border dark:border-gray-800 text-center px-2 py-2 ">{{ car.vin }}</td>
                                    <td className="border dark:border-gray-800 text-center px-2 py-2 ">{{ car.car_number }}</td> 
                                    <td className="border dark:border-gray-800 text-center px-2 py-2 ">{{ car.dinar_s  }}</td>
                                    <td className="border dark:border-gray-800 text-center px-2 py-2 ">{{ car.dolar_price_s}}</td>
                                    <td className="border dark:border-gray-800 text-center px-2 py-2 ">{{ ((car.dinar_s/car.dolar_price_s)*100).toFixed(0)||0}}</td> 
                                    <td className="border dark:border-gray-800 text-center px-2 py-2 ">{{ car.note }}</td>
                                    <td className="border dark:border-gray-800 text-center px-2 py-2 ">{{ car.shipping_dolar_s}}</td>
                                    <td className="border dark:border-gray-800 text-center px-2 py-2 ">{{ car.coc_dolar_s  }}</td>
                                    <td className="border dark:border-gray-800 text-center px-2 py-2 ">{{ car.checkout_s}}</td>
                                    <td className="border dark:border-gray-800 text-center px-2 py-2 ">{{ car.expenses}}</td>
                                    <td className="border dark:border-gray-800 text-center px-2 py-2 ">{{ (car.total_s).toFixed(0) }}</td>
                                    <td className="border dark:border-gray-800 text-center px-2 py-2 ">{{ car.paid}}</td>
                                    <td className="border dark:border-gray-800 text-center px-2 py-2 ">{{ car.date  }}</td>
                                     <td className="border dark:border-gray-800 text-start px-2 py-2">
                                    <button
                                      tabIndex="1"
                                      
                                      class="px-2 py-1  text-white mx-1 bg-slate-500 rounded"
                                      @click="openModalEditCars(car)"
                                    >
                                      {{ $t('edit') }}
                                    </button>
                                    <button
                                      tabIndex="1"
                                      
                                      class="px-2 py-1  text-white mx-1 bg-orange-500 rounded"
                                      @click="openModalDelCar(car)"
                                    >
                                      {{ $t('delete') }}
                                    </button>
                                    <button
                                      v-if="car.total_s != car.paid"
                                      tabIndex="1"
                                      class="px-2 py-1  text-white mx-1 bg-green-500 rounded"
                                      @click="openAddCarPayment(car)"
                                    >
                                      {{ $t('complet_pay') }}
                                    </button>
                                    <!-- 
          
                                    <button
                                      tabIndex="1"
                                      class="px-4 py-1  text-white mx-1 bg-purple-500 rounded"
                                      v-if="car.results == 0"
                                      @click="openSaleCar(car)"
                                    >
                                      {{ $t('sell') }}
                                    </button>
                                    <button
                                      tabIndex="1"
                                      class="px-2 py-1  text-white mx-1 bg-blue-600 rounded"
                                      @click="openAddExpenses(car)"
                                    >
                                      {{ $t('expenses') }}
                                    </button>
                                    <button
                                      tabIndex="1"
                                      class="px-2 py-1  text-white mx-1 bg-green-500 rounded"
                                      v-if="car.results != 0 && (car.pay_price - car.paid_amount_pay == 0)"
                                      @click="openAddCarPayment(car)"
                                    >
                                      {{ $t('view_payments') }}
                                    </button>
                                    <button
                                      tabIndex="1"
                                      class="px-2 py-1 text-base text-white mx-1 bg-red-700 rounded"
                                      v-if="car.results == 1 && (car.pay_price - car.paid_amount_pay != 0)"
                                      @click="openAddCarPayment(car)"
                                    >
                                      {{ $t('add_payment') }}
                                    </button>

                                    -->

                                    </td> 
                                </tr>
                              </tbody>
                          </table>
                        </div>
                        <div class="mt-3 text-center" style="direction: ltr;">
                          <TailwindPagination
                            :data="car"
                            @pagination-change-page="getResultsCar"
                            :limit ="10"
                            :item-classes="['bg-white','dark:bg-gray-600','text-gray-500','dark:text-gray-300','border-gray-300','dark:border-gray-900','hover:bg-gray-200']"
                            :activeClasses="[  'bg-rose-50','border-rose-500','text-rose-600',]"
                          />
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
                              <h2 class="font-semibold">{{ $t('all_cars') }}</h2>
                              <p class="mt-2 text-sm text-gray-500 dark:text-gray-200">{{allCars}}</p>
                            </div>
                          </div>

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
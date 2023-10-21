<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/inertia-vue3';
import VueTailwindDatepicker from 'vue-tailwind-datepicker'
import ModalAddCarContracts from "@/Components/ModalAddCarContracts.vue";
import ModalEditCarContracts from "@/Components/ModalEditCarContracts.vue";
import ModalAddExitCar from "@/Components/ModalAddExitCar.vue";
import ModalShowExitCar from "@/Components/ModalShowExitCar.vue";

import { TailwindPagination } from "laravel-vue-pagination";
import { useToast } from "vue-toastification";
import axios from 'axios';
import { ref } from 'vue';
import { useI18n } from "vue-i18n";
import show from "@/Components/icon/show.vue";
import pay from "@/Components/icon/pay.vue";
import trash from "@/Components/icon/trash.vue";
import edit from "@/Components/icon/edit.vue";
import exit from "@/Components/icon/exit.vue";
import newContracts from "@/Components/icon/new.vue";
import InfiniteLoading from "v3-infinite-loading";
import "v3-infinite-loading/lib/style.css";
import debounce from 'lodash/debounce';

const {t} = useI18n();


const props = defineProps({

  client:Array,

});


const toast = useToast();

let searchTerm = ref('');
let showModalAddCarContracts =  ref(false);
let showModalEditCarContracts =  ref(false);
let showModalAddExitCar = ref(false);
let showModalShowExitCar = ref(false);

let onlineContracts= ref(0)
let debtOnlineContracts= ref(0)

let onlineContractsDinar = ref(0)
let debtOnlineContractsDinar = ref(0)
let contarts = ref(0)
let exitCar = ref(0)
let allCars= ref(0)
function openModalAddCarContracts(form={}) {
  formData.value=form

  formData.value.prices=100
  formData.value.price_dinars=50000

  showModalAddCarContracts.value = true;
}
function openModalEditCarContracts(form={}) {
  formData.value=form

  showModalEditCarContracts.value = true;
}
function openModalAddExitCar(form={}) {
  formData.value=form
  formData.value.createdExit = getTodayDate()

  showModalAddExitCar.value = true;
}
function openModalShowExitCar(form={}) {
  formData.value=form
  showModalShowExitCar.value = true;
}
const formData = ref({});
const formGenExpenses = ref({});
const car = ref([]);


let resetData = ref(false);
let user_id = 0;
let page = 1;
let q = '';
const refresh = () => {
  page = 0;
  car.value.length = 0;
  resetData.value = !resetData.value;


};
const debouncedGetResultsCar = debounce(refresh, 500); // Adjust the debounce delay (in milliseconds) as needed


const getResultsCar = async ($state) => {
  try {
    const response = await axios.get(`/getIndexCar`, {
      params: {
        limit: 100,
        page: page,
        q: q,
        user_id: user_id
      }
    });

    const json = response.data;


    if (json.data.length < 100){
      car.value.push(...json.data);
      $state.complete();
    } 
    else {
      car.value.push(...json.data);
       $state.loaded();
    }


    page++;
  } catch (error) {
    console.log(error);
    //$state.error();
  }
};

const getcountTotalInfo = async () => {
  axios.get('/api/totalInfo')
  .then(response => {
    contarts.value = response.data.data.contarts;
    exitCar.value = response.data.data.exitCar;
    onlineContracts.value = response.data.data.onlineContracts;
    onlineContractsDinar.value =response.data.data.onlineContractsDinar
    debtOnlineContractsDinar.value =response.data.data.debtOnlineContractsDinar
    debtOnlineContracts.value = response.data.data.debtOnlineContracts;
    allCars.value =response.data.data.allCars;
  })
  .catch(error => {
    console.error(error);
  })
  
    
}
getcountTotalInfo()
 
 


function confirmAddCarContracts(V) {
  axios.get(`/api/addCarContracts?car_id=${V.id}&price=${V.prices??0}&price_dinar=${V.price_dinars??0}&paid=${V.paids??0}&paid_dinar=${V.paid_dinars??0}&phone=${V.phone??''}&note=${V.note??''}`)
  .then(response => {
    showModalAddCarContracts.value = false;
    toast.success( " تم دفع مبلغ بنجاح ", {
        timeout: 4000,
        position: "bottom-right",
        rtl: true

      });
      refresh();
      getcountTotalInfo()


  })
  .catch(error => {
    showModalAddCarContracts.value = false;

    toast.error("لم التعديل بنجاح", {
        timeout: 2000,
        position: "bottom-right",
        rtl: true

      });

  })
}
function confirmEditCarContracts(V) {
  axios.get(`/api/editCarContracts?car_id=${V.id}&paid=${V.paids??0}&paid_dinar=${V.paid_dinars??0}&note=${V.notePayment??''}`)
  .then(response => {
    showModalEditCarContracts.value = false;
    toast.success( " تم دفع مبلغ دولار "+V.amountPayment+" بنجاح ", {
        timeout: 3000,
        position: "bottom-right",
        rtl: true

      });
      refresh();
      getcountTotalInfo()


  })
  .catch(error => {
    showModalEditCarContracts.value = false;

    toast.error("لم التعديل بنجاح", {
        timeout: 2000,
        position: "bottom-right",
        rtl: true

      });

  })
}

function confirmAddExitCar(v){

  axios.get(`/api/makeCarExit?car_id=${v.id}&created=${v.createdExit}&phone=${v.phoneExit}&note=${v.noteExit}`)
  .then(response => {
    showModalAddExitCar.value = false;
    toast.success( "تم اضافة خروجية للسيارة بنجاح ", {
        timeout: 5000,
        position: "bottom-right",
        rtl: true

      });

      refresh();

  })
  .catch(error => {
    showModalAddExitCar.value = false;

    toast.error("لم التعديل بنجاح", {
        timeout: 2000,
        position: "bottom-right",
        rtl: true

      });

  })
  
}



function getTodayDate() {
  const today = new Date();
  const year = today.getFullYear();
  const month = String(today.getMonth() + 1).padStart(2, '0');
  const day = String(today.getDate()).padStart(2, '0');
  return `${year}-${month}-${day}`;
}

</script>

<template>
    <Head title="Dashboard" />
    <AuthenticatedLayout>

    </AuthenticatedLayout>
</template>
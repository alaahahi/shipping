<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Modal from "@/Components/Modal.vue";
import { Head, Link, useForm } from "@inertiajs/inertia-vue3";
import { Inertia } from "@inertiajs/inertia";
import { onMounted, ref, watch } from 'vue';
import { TailwindPagination } from "laravel-vue-pagination";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import axios from "axios";
import ModalDelCar from "@/Components/ModalDelCar.vue";
import ModalEditCars from "@/Components/ModalEditCar_S.vue";
import ModalAddCarPayment from "@/Components/ModalAddCarPayment.vue";
import ModalAddCarContracts from "@/Components/ModalAddCarContracts.vue";
import ModalEditCarContracts from "@/Components/ModalEditCarContracts.vue";
import ModalAddExitCar from "@/Components/ModalAddExitCar.vue";
import ModalShowExitCar from "@/Components/ModalShowExitCar.vue";
import ModalShowDriving from "@/Components/ModalShowDriving.vue";


import print from "@/Components/icon/print.vue";
import pay from "@/Components/icon/pay.vue";
import trash from "@/Components/icon/trash.vue";
import edit from "@/Components/icon/edit.vue";
import exit from "@/Components/icon/exit.vue";
import show from "@/Components/icon/show.vue";
import document from "@/Components/icon/document.vue";

import newContracts from "@/Components/icon/new.vue";

import { useToast } from "vue-toastification";
import { create } from "lodash";
let toast = useToast();
let sums= ref(0);
let laravelData = ref({});
let isLoading = ref(0);
let from = ref(0);
let to = ref(0);
let indexs=1;
let showPaymentForm = ref(false);
let showModalEditCars = ref(false);
let showModalDelCar = ref(false);
let showModalAddCarPayment = ref(false);
let showErorrAmount = ref(false);
let showTransactions= ref(false);
let showComplatedCars = ref(false);
let showPaymentsInTable = ref(false);
let showModalAddCarContracts =  ref(false);
let showModalEditCarContracts =  ref(false);
let showModalAddExitCar = ref(false);
let showModalShowExitCar = ref(false);
let showModalShowDriving = ref(false);
let total = ref(0);
let formData = ref({});
let formDriving = ref({});

let discount= ref(0);
let note = ref('');
let amount = ref(0);
let runningBalance = ref(0);

let client_Select = ref(0);
let showReceiveBtn = ref(0);
let showModalAddPayFromBalanceCar = ref(false);
let showModalDelPayFromBalanceCar = ref(false);

// يجب تعريف props قبل استخدامها في أي مكان
const props = defineProps({
  url: String,
  clients: Array,
  client_id: String,
  client: Object,
  q:String
});

let getResults = async (page = 1) => {
  axios
    .get(`/api/getIndexAccountsSelas?page=${page}&user_id=${props.client_id}&from=${from.value}&to=${to.value}`)
    .then((response) => {
      laravelData.value = response.data;
      client_Select.value = response.data.client.id
      checkClientBalance(response.data.cars_sum)
    })
    .catch((error) => {
      console.error(error);
    });
};
function calculateTotalFilteredAmount() {
  let filteredTransactions ='';
  let totalAmount ='';
  try {
    filteredTransactions = laravelData.value.transactions.filter(user =>
    user.type === 'out' && user.amount < 0 && user.is_pay === 1
  );

  } catch (error) {

  }
  try {
    totalAmount = filteredTransactions.reduce((sum, user) => sum + user.amount, 0);

  } catch (error) {

  }
  return {  totalAmount };
}

function getPaymentsCount() {
  try {
    return laravelData.value?.transactions?.filter(t => t.type === 'out' && t.amount < 0 && t.is_pay === 1).length || 0;
  } catch (error) {
    return 0;
  }
}
function openModalAddPayFromBalanceCar(form = {}) {
  formData.value = form;
  showModalAddPayFromBalanceCar.value = true;
}
function openModalDelPayFromBalanceCar(form = {}) {
  formData.value = form;
  showModalDelPayFromBalanceCar.value = true;
}

function confirmAddPayFromBalanceCar(V) {
  V.balance  =(((calculateTotalFilteredAmount().totalAmount)*-1)-(laravelData.value?.cars_paid))-(laravelData.value?.cars_discount)
   axios
    .post("/api/AddPayFromBalanceCar", V)
    .then((response) => {
      showModalAddPayFromBalanceCar.value = false;
      getResultsSelect();
    })
    .catch((error) => {
      console.error(error);
    });
}
function confirmDelPayFromBalanceCar(V) {
  axios
    .post("/api/DelPayFromBalanceCar", V)
    .then((response) => {
      showModalDelPayFromBalanceCar.value = false;
      getResultsSelect();
    })
    .catch((error) => {
      console.error(error);
    });
}

const getResultsSelect = async (page = 1) => {

  axios
    .get(`/api/getIndexAccountsSelas?page=${page}&user_id=${client_Select.value}&from=${from.value}&to=${to.value}`)
    .then((response) => {
      laravelData.value = response.data;
      client_Select.value = response.data.client.id


    })
    .catch((error) => {
      console.error(error);
    });
};

// استدعاء getResults عند تحميل الصفحة
onMounted(() => {
  getResults();
});

// مراقبة تغييرات client_id عند التنقل بين الصفحات
watch(() => props.client_id, (newValue, oldValue) => {
  if (newValue && newValue !== oldValue) {
    // إعادة تعيين القيم عند التنقل لعميل جديد
    from.value = 0;
    to.value = 0;
    showPaymentForm.value = false;
    showTransactions.value = false;
    showComplatedCars.value = false;
    showPaymentsInTable.value = false;
    amount.value = 0;
    discount.value = 0;
    note.value = '';
    indexs = 1;
    getResults();
  }
}, { immediate: false });

const form = useForm();

let showModal = ref(false);


function method1(id) {
  form.get(route("sentToCourt", id));
  getResults();
  showModal.value = false;
}
function openModalDelCar(form = {}) {
  formData.value = form;
  showModalDelCar.value = true;
}
function openModalEditCars(form = {}) {
  formData.value = form;
  if(formData.value.dinar_s==0){
    formData.value.dinar_s=formData.value.dinar
  }

  showModalEditCars.value = true;
}

function openAddCarPayment(form = {}) {
  formData.value = form;
  formData.value.notePayment=' بيد '
  showModalAddCarPayment.value = true;
}
function confirmDelCar(V) {
  axios
    .post("/api/DelCar", V)
    .then((response) => {
      showModalDelCar.value = false;
      toast.success("تم حذف السيارة بنجاح", {
        timeout: 2000,
        position: "bottom-right",
        rtl: true,
      });
      getResultsSelect();
    })
    .catch((error) => {
      console.error(error);
      toast.error("فشل حذف السيارة", {
        timeout: 2000,
        position: "bottom-right",
        rtl: true,
      });
    });
}
function confirmUpdateCar(V) {
  showModalEditCars.value = false;

  axios
    .post("/api/updateCarsS", V)
    .then((response) => {
      showModal.value = false;
      toast.success("تم التعديل بنجاح", {
        timeout: 2000,
        position: "bottom-right",
        rtl: true,
      });
      getResultsSelect()

    })
    .catch((error) => {
      
       toast.error("لم التعديل بنجاح", {
        timeout: 2000,
        position: "bottom-right",
        rtl: true,
      });
      getResultsSelect()

    });
}
function confirmAddPayment(V) {
  if(!V.discountPayment){
    V.discountPayment=0
  }
  axios
    .get(
      `/api/addPaymentCar?car_id=${V.id}&discount=${V.discountPayment}&amount=${V.amountPayment ?? 0}&note=${
        V.notePayment ?? ""
      }`
    )
    .then((response) => {
      showModalAddCarPayment.value = false;
      toast.success(" تم دفع مبلغ دولار " + V.amountPayment + " بنجاح ", {
        timeout: 3000,
        position: "bottom-right",
        rtl: true,
      });
      getResultsSelect()
      let transaction=response.data
      window.open(`/api/getIndexAccountsSelas?user_id=${props.client_id}&print=2&transactions_id=${transaction.id}`, '_blank');
    })
    .catch((error) => {
      showModal.value = false;
      console.log(error)
      toast.error("لم التعديل بنجاح", {
        timeout: 2000,
        position: "bottom-right",
        rtl: true,
      });
    });
}
function confirmAddPaymentTotal(amount, client_Select,discount,note) {
  isLoading.value=true
  axios
    .get(
      `/api/addPaymentCarTotal?amount=${amount ?? 0}&discount=${discount ?? 0}&note=${note}&client_id=${ client_Select ?? 0}`
    )
    .then((response) => {
      showModalAddCarPayment.value = false;
      toast.success(" تم دفع مبلغ دولار " + amount + " بنجاح ", {
        timeout: 3000,
        position: "bottom-right",
        rtl: true,
      });
      showPaymentForm.value = false;
      isLoading.value=false
      getResultsSelect()
      resetValuse()
      
      let transaction=response.data

      window.open(`/api/getIndexAccountsSelas?user_id=${props.client_id}&print=2&transactions_id=${transaction.id}`, '_blank');
    })
    .catch((error) => {
      console.log(error)
      showModal.value = false;
      isLoading.value=false

      toast.error("لم التعديل بنجاح", {
        timeout: 2000,
        position: "bottom-right",
        rtl: true,
      });
    });
}
function resetValuse(){
      amount.value=0
      discount.value=0
      note.value='';
}
function showAddPaymentTotal(){
  showPaymentForm.value = true;
  showTransactions.value=false;
}
function hideAddPaymentTotal(){
  showPaymentForm.value = false;
}
function showTransactionsDiv(){
  showTransactions.value=true;
  showPaymentForm.value = false;
}
function hideTransactionsDiv(){
  showTransactions.value=false;
  
}

function openModalAddCarContracts(form={}) {
  formData.value=form

  formData.value.prices=100
  formData.value.price_dinars=50000

  showModalAddCarContracts.value = true;
}
function openModalEditCarContracts(form={}) {
  formData.value=formshowModalShowExitCar

  showModalEditCarContracts.value = true;
}
function openModalAddExitCar(form={}) {
  formData.value=form
  formData.value.createdExit = getTodayDate()

  showModalAddExitCar.value = true;
}
function openModalShowExitCar(form={}) {
  formData.value=form
  .value = true;
}
function openModalShowDriving(form={}) {
  formDriving.value.car_typeDriving = form.car_type
  formDriving.value.yearDriving = form.year
  formDriving.value.car_colorDriving= form.car_color
  formDriving.value.vinDriving= form.vin
  formDriving.value.nameDriving= props.client.name
  formDriving.value.clientIdDriving= client_Select.value
  formDriving.value.car_numberDriving= form.car_number
  formDriving.value.createdDriving=  getTodayDate()
  formDriving.value.noteDriving= `انا كارزان سرهنك محمد (وكيل عام سلام جلال ايوب ) (مدير مفوض شركة سلام جلال ايوب) قد خولت السيد(name) بقيادة السيارة ذات المواصفات ادناه له حق  نقلها  من محافظة الى محافظة اخرى ودفع الرسوم والغرمات بيع وشراء القبض الثمن.`;
  showModalShowDriving.value = true;
}
function calculateAmountDiscount (){
  let need_payment =  laravelData?.value?.client?.wallet?.balance
  amount.value=need_payment- discount.value
}
function calculateAmount(){
  
  let need_payment = laravelData?.value?.client?.wallet?.balance
  console.log(need_payment)
  if(amount.value > need_payment){
    amount.value=need_payment
    showErorrAmount.value = true
    toast.info(" المبلغ اكبر من الدين المطلوب"+" "+amount.value, {
        timeout: 4000,
        position: "bottom-right",
        rtl: true,
      });
      
  }else{
    
    showErorrAmount.value = false
  }

}

function confirmAddCarContracts(V) {
  axios.get(`/api/addCarContracts?car_id=${V.id}&price=${V.prices??0}&price_dinar=${V.price_dinars??0}&paid=${V.paids??0}&paid_dinar=${V.paid_dinars??0}&phone=${V.phone??''}&note=${V.note??''}`)
  .then(response => {
    showModalAddCarContracts.value = false;
    toast.success( " تم دفع مبلغ بنجاح ", {
        timeout: 4000,
        position: "bottom-right",
        rtl: true

      });
      getResultsSelect();


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
      getResultsSelect();


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

      getResultsSelect();

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

function checkClientBalance(v){
    axios.get(`/api/checkClientBalance?userId=${props.client_id}&currentBalance=${v+calculateTotalFilteredAmount().totalAmount}`)
    .then(response => {
      console.log(response)
      if(response.status==201){
        toast.success( " تم مراجعة الحساب بنجاح  "+ response.data, {
            timeout: 5000,
            position: "bottom-right",
            rtl: true

          });
      }
    })
    .catch(error => {
      toast.error( "لم يتم اعادة فحص الحساب  بنجاح ", {
            timeout: 5000,
            position: "bottom-right",
            rtl: true

          });

    })
}

function confirmAddDriving(v){

axios.post(`/api/makeDrivingDocument`,v)
.then(response => {
  showModalShowDriving.value = false;

  window.open(`/api/makeDrivingDocumentPdf?doc_id=${response.data.id}`, '_blank');
})
.catch(error => {
  showModalShowDriving.value = false;

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

function calculateRunningBalance(item, isVisible) {
  // فقط احسب للصفوف المرئية
  if (isVisible) {
    if (item.type === 'car') {
      runningBalance.value += (parseFloat(item.data.paid) || 0) + (parseFloat(item.data.discount) || 0);
    } else if (item.type === 'payment') {
      // الدفعة تُطرح من الرصيد (لأن العميل دفع)
      runningBalance.value -= (parseFloat(item.data.amount) * -1) || 0;
    }
  }
  return runningBalance.value.toFixed(0);
}

function resetRunningBalance() {
  runningBalance.value = 0;
  return '';
}

function isRowVisible(car) {
  return (car.results == 2 && showComplatedCars.value) || car.results != 2;
}

function getMergedData() {
  const merged = [];
  const cars = laravelData.value?.data || [];
  const transactions = laravelData.value?.transactions || [];
  
  // إضافة السيارات أولاً
  cars.forEach(car => {
    merged.push({ type: 'car', data: car });
  });
  
  // إضافة الدفعات فقط إذا كان الفلاغ مفعل
  if (showPaymentsInTable.value) {
    // فلترة الدفعات فقط (المدفوعات)
    const payments = transactions.filter(t => t.type === 'out' && t.amount < 0 && t.is_pay === 1);
    
    // إضافة جميع الدفعات في النهاية
    payments.forEach(payment => {
      merged.push({ type: 'payment', data: payment });
    });
  }
  
  return merged;
}


function getImageUrl(name) {
      // Provide the base URL for your images
      return `/public/uploadsResized/${name}`;
    }
function getDownloadUrl(name) {
      // Provide the base URL for downloading images
      return `/public/uploads/${name}`;
    }

</script>

<template>
  <Head title="Dashboard" />
  <AuthenticatedLayout>
    <template #header>
      <h2
        class="font-semibold text-xl dark:text-gray-400 text-gray-800 leading-tight"
      >
        شركة سلام جلال
      </h2>
    </template>
    <ModalAddCarContracts
            :formData="formData"
            :show="showModalAddCarContracts ? true : false"
            @a="confirmAddCarContracts($event)"
            @close="showModalAddCarContracts = false"
            >
        <template #header>
          </template>
    </ModalAddCarContracts>

    <ModalDelCar
      :show="showModalAddPayFromBalanceCar ? true : false"
      :formData="formData"
      @a="confirmAddPayFromBalanceCar($event)"
      @close="showModalAddPayFromBalanceCar = false"
    >
      <template #header>
        <h2 class="mb-5 dark:text-gray-400 text-center">
          هل متأكد من دفع 
          {{ formData.car_type }}
          السيارة ؟
          من الرصيد
        </h2>
      </template>
    </ModalDelCar>

    <ModalDelCar
      :show="showModalDelPayFromBalanceCar ? true : false"
      :formData="formData"
      @a="confirmDelPayFromBalanceCar($event)"
      @close="showModalDelPayFromBalanceCar = false"
    >
      <template #header>
        <h2 class="mb-5 dark:text-gray-400 text-center">
          هل متأكد من اعادة دفعة السيارة
          {{ formData.car_type }}
          للرصيد ؟
        </h2>
      </template>
    </ModalDelCar>

    <ModalEditCarContracts
            :formData="formData"
            :show="showModalEditCarContracts ? true : false"
            @a="confirmEditCarContracts($event)"
            @close="showModalEditCarContracts = false"
            >
        <template #header>
          </template>
    </ModalEditCarContracts>
    <ModalAddExitCar
            :formData="formData"
            :show="showModalAddExitCar ? true : false"
            @a="confirmAddExitCar($event)"
            @close="showModalAddExitCar = false"
            >
        <template #header>
          </template>
    </ModalAddExitCar>
    <ModalShowExitCar
            :formData="formData"
            :show="showModalShowExitCar ? true : false"
            @a="confirmAddExitCar($event)"
            @close="showModalShowExitCar = false"
            >
        <template #header>
          </template>
    </ModalShowExitCar>
    <ModalShowDriving
            :formDriving="formDriving"
            :show="showModalShowDriving ? true : false"
            @a="confirmAddDriving($event)"
            @close="showModalShowDriving = false"
            
            >
        <template #header>
          </template>
    </ModalShowDriving>
    <ModalEditCars
      :formData="formData"
      :show="showModalEditCars ? true : false"
      :client="clients"
      @a="confirmUpdateCar($event)"
      @close="showModalEditCars = false"
    >
      <template #header> </template>
    </ModalEditCars>
    <ModalAddCarPayment
      :formData="formData"
      :show="showModalAddCarPayment ? true : false"
      @a="confirmAddPayment($event)"
      @close="showModalAddCarPayment = false"
    >
      <template #header> </template>
    </ModalAddCarPayment>

    <ModalDelCar
      :show="showModalDelCar ? true : false"
      :formData="formData"
      @a="confirmDelCar($event)"
      @close="showModalDelCar = false"
    >
      <template #header>
        <h2 class="mb-5 dark:text-gray-400 text-center">
          هل متأكد من حذف السيارة ؟
        </h2>
      </template>
    </ModalDelCar>
    <modal
      :show="showModal ? true : false"
      :data="showModal.toString()"
      @a="method1($event, arg1)"
      @close="showModal = false"
    >
    </modal>
    <div v-if="$page.props.success" >
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
    <div class="py-4" v-if="$page.props.auth.user.type_id==1||$page.props.auth.user.type_id==6">
      <h2 class="text-center pb-2 dark:text-gray-400">
        {{ $t("sales_bill") }}
      </h2>
      <div class="max-w-9xl mx-auto sm:px-6 lg:px-8 p-6 dark:bg-gray-900">
        <div class="overflow-hidden shadow-sm sm:rounded-lg">
          <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-2 lg:gap-1">
            <div class="pr-4">
              <InputLabel
                class="mb-1"
                for="invoice_number"
                :value="$t('Account')"
              />
              <select
                @change="getResultsSelect()"
                v-model="client_Select"
                id="default"
                class="pr-8 border border-gray-300 text-gray-900 mb-6 text-sm rounded-lg focus:ring-red-500 focus:border-red-500 block w-full p-2.5 dark:border-gray-400 dark:placeholder-gray-400 dark:text-gray-600 dark:focus:ring-red-500 dark:focus:border-red-500"
              >
                <option value="undefined" disabled>
                  {{ $t("selectCustomer") }}
                </option>
                <template v-for="(user, index) in clients" :key="index">
                <option
                  v-if="user.wallet.balance > 0 || user.id ==client_Select"
                  :value="user.id">
                  {{ user.name }}
                </option>
              </template>
              </select>
            </div>
            <div>
              <div className="mb-4  mr-5">
                <InputLabel for="totalAmount" value="فلترة السيارات المكتملة" />
                <div class="flex items-center ps-4  rounded-lg border border-gray-300 text-gray-900 mt-1">
                    <input id="bordered-checkbox-1" type="checkbox" @change="showComplatedCars== true? showComplatedCars=false: showComplatedCars=true" :value="showComplatedCars" :checked="!showComplatedCars" name="bordered-checkbox" class="w-4 h-4 mx-2 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                    <label for="bordered-checkbox-1" class="w-full pt-3 py-2 mx-4 text-sm  font-medium text-gray-900 dark:text-gray-300"> 
                      {{showComplatedCars== false?' تم الفلتر':'تم عرض جميع السيارة'}}
                    </label>
                </div>
              </div>
            </div>
            <div>
              <div className="mb-4  mr-5">
                <InputLabel for="showPayments" value="عرض الدفعات في الجدول 💳" />
                <div class="flex items-center ps-4  rounded-lg border border-purple-300 text-gray-900 mt-1 bg-purple-50 dark:bg-purple-950">
                    <input id="bordered-checkbox-2" type="checkbox" @change="showPaymentsInTable = !showPaymentsInTable" :value="showPaymentsInTable" :checked="showPaymentsInTable" name="bordered-checkbox-2" class="w-4 h-4 mx-2 text-purple-600 bg-gray-100 border-gray-300 rounded focus:ring-purple-500 dark:focus:ring-purple-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                    <label for="bordered-checkbox-2" class="w-full pt-3 py-2 mx-4 text-sm  font-medium text-gray-900 dark:text-gray-300"> 
                      {{showPaymentsInTable ? '✅ الدفعات ظاهرة (' + getPaymentsCount() + ')' : '❌ الدفعات مخفية (' + getPaymentsCount() + ')'}}
                    </label>
                </div>
                <div v-if="!showPaymentsInTable && getPaymentsCount() > 0" class="text-xs text-purple-600 dark:text-purple-400 mt-1 mr-5">
                  💡 لتحسين الأداء، الدفعات مخفية افتراضياً
                </div>
              </div>
            </div>
            <div class="px-4">
              <div className="mb-4 mx-5">
                <InputLabel for="from" :value="$t('from_date')" />
                <TextInput
                  id="from"
                  type="date"
                  class="mt-1 block w-full"
                  v-model="from"
                />
              </div>
            </div>
            <div class="px-4">
              <div className="mb-4 mx-5">
                <InputLabel for="to" :value="$t('to_date')" />
                <TextInput
                  id="to"
                  type="date"
                  class="mt-1 block w-full"
                  v-model="to"
                />
              </div>
            </div>
            <div className="mb-4  mr-5 print:hidden">
              <InputLabel for="pay" value="فلترة" />
              <button
                @click.prevent="getResults()"
                class="px-6 mb-12 py-2 mt-1 font-bold text-white bg-gray-500 rounded"
                style="width: 100%"
              >
                <span>فلترة</span>
              </button>
            </div>
            <div className="mb-4  mr-5 print:hidden">
              <InputLabel for="pay" value="طباعة" />
              <a
                :href="`/api/getIndexAccountsSelas?user_id=${client_Select}&from=${from}&to=${to}&print=1&showComplatedCars=${ showComplatedCars ? 0:1}`"
                target="_blank"
                class="px-6 mb-12 py-2 mt-1 font-bold text-white bg-orange-500 rounded block text-center"
                style="width: 100%"
              >
                <span>طباعة</span>
              </a>
            </div>
            <div className="mb-4  mr-5 print:hidden">
              <InputLabel for="pay" value="طباعة" />
              <a
                :href="`/api/getIndexAccountsSelas?user_id=${client_Select}&from=${from}&to=${to}&print=1&printExcel=1&showComplatedCars=${ showComplatedCars ? 0:1}`"
                target="_blank"
                class="px-6 mb-12 py-2 mt-1 font-bold text-white bg-green-500 rounded block text-center"
                style="width: 100%"
              >
                <span>Excel</span>
              </a>
            </div>
            <div className="mb-4  mr-5">
              <InputLabel for="car_total" value="مجموع السيارات" />
              <TextInput
                id="car_total"
                type="text"
                class="mt-1 block w-full"
                :value="laravelData.car_total"
                disabled
              />
            </div>
 
 
            <div className="mb-4  mr-5">
              <InputLabel for="cars_sum" :value="$t('Total_in_dollars')+' جمرك ' " />
              <TextInput
                id="cars_sum"
                type="text"
                class="mt-1 block w-full"
                :value="laravelData?.cars_sum"
                disabled
              />
            </div>
            <div className="mb-4  mr-5">
              <InputLabel for="cars_paid" value="مجموع المدفوع  جمرك بالدولار" />
              <TextInput
                id="cars_paid"
                type="number"
                class="mt-1 block w-full"
                :value="parseFloat(laravelData?.cars_sum)-(parseFloat(laravelData?.client?.wallet?.balance)+parseFloat(laravelData?.cars_discount))"
                disabled
              />
            </div>
            <div className="mb-4  mr-5">
              <InputLabel for="cars_discount" value="مجموع الخصومات  جمرك بالدولار" />
              <TextInput
                id="cars_discount"
                type="text"
                class="mt-1 block w-full"
                :value="laravelData?.cars_discount"
                disabled
              />
            </div>
           
            <div className="mb-4  mr-5">
              <InputLabel for="cars_need_paid" value="  الرصيد بالدولار" />
              <TextInput
                id="cars_need_paid"
                type="number"
                class="mt-1 block w-full"
                :value="((calculateTotalFilteredAmount().totalAmount)*-1)-(laravelData?.cars_sum)"
                disabled
              />
            </div>
            <div className="mb-4  mr-5 print:hidden"   v-if="((calculateTotalFilteredAmount().totalAmount)*-1)-(laravelData?.cars_sum) !=0">
              <InputLabel for="pay" value="اضافة دفعة" />
              <button
                @click.prevent="showAddPaymentTotal()"
                v-if="!showPaymentForm"
                :disabled="isLoading"
                class="px-6 mb-12 py-2 mt-1 font-bold text-white bg-green-500 rounded"
                style="width: 100%">
                <span>اضافة دفعة</span>
              </button>
              <button
                @click.prevent="hideAddPaymentTotal()"
                v-if="showPaymentForm"
                :disabled="isLoading"
                class="px-6 mb-12 py-2 mt-1 font-bold text-white bg-pink-500 rounded"
                style="width: 100%">
                <span>اخفاء دفعة</span>
              </button>
            </div>
            <div className="mb-4  mr-5 print:hidden" >
              <InputLabel for="pay" value="عرض الدفعات" />
              <button
                @click.prevent="showTransactionsDiv()"
                v-if="!showTransactions"
                :disabled="isLoading"
                class="px-6 mb-12 py-2 mt-1 font-bold text-white bg-purple-500 rounded"
                style="width: 100%">
                <span>عرض الدفعات</span>
              </button>
              <button
                @click.prevent="hideTransactionsDiv()"
                v-if="showTransactions"
                class="px-6 mb-12 py-2 mt-1 font-bold text-white bg-pink-500 rounded"
                style="width: 100%">
                <span>اخفاء الدفعات</span>
              </button>
            </div>

            <div className="mb-4  mr-5"   v-if="((((calculateTotalFilteredAmount().totalAmount)*-1)-laravelData?.cars_discount)-(laravelData?.cars_paid)) != 0">
              <InputLabel for="cars_need_paid" value="الرصيد غير موزع بالدولار" />
              <TextInput
                id="cars_need_paid"
                type="number"
                class="mt-1 block w-full"
               
                :value="((((calculateTotalFilteredAmount().totalAmount)*-1)-laravelData?.cars_discount)-(laravelData?.cars_paid))"
              />
            </div>

            <div className="mb-4  mr-5">
              <InputLabel for="total_running_balance" value="💰 إجمالي الرصيد المتصل" />
              <TextInput
                id="total_running_balance"
                type="text"
                class="mt-1 block w-full font-bold bg-blue-100 text-blue-900"
                :value="laravelData?.cars_paid + laravelData?.cars_discount"
                disabled
              />
            </div>

            <div className="mb-4  mr-5">
              <InputLabel
                for="contract_total"
                value="العقود المنجزة"
              />
              <TextInput
                id="contract_total"
                type="text"
                class="mt-1 block w-full"
                :value="laravelData.contract_total"
                disabled
              />
            </div>
            <div className="mb-4  mr-5">
              <InputLabel
                for="exit_car_total"
                value="الخروجيات المنجزة"
              />
              <TextInput
                id="exit_car_total"
                type="text"
                class="mt-1 block w-full"
                :value="laravelData.exit_car_total"
                disabled
              />
            </div>
            <div className="mb-4  mr-5">
              <InputLabel for="cars_paid" value="مجموع الدين عقود بالدولار" />
              <TextInput
                id="cars_paid"
                type="number"
                class="mt-1 block w-full"
                :value="laravelData?.contract_total_debit_Dollar"
                disabled
              />
            </div>
            <div className="mb-4  mr-5">
              <InputLabel for="cars_paid" value="مجموع الدين عقود بالدينار" />
              <TextInput
                id="cars_paid"
                type="number"
                class="mt-1 block w-full"
                :value="laravelData?.contract_total_debit_Dinar"
                disabled
              />
            </div>
          </div>
          <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2 lg:gap-1" v-if="showPaymentForm">
            <div className="mb-4  mr-5">
              <InputLabel
              
                for="discount"
                value="الخصم"
              />
              <TextInput
                id="discount"
                type="number"
                @input="calculateAmountDiscount"

                class="mt-1 block w-full"
                v-model="discount"
              />
            </div>

            <div className="mb-4  mr-5">
              <InputLabel
                for="percentage"
                value=" المبلغ بالدولار المراد دفعه"
              />
              <TextInput
                id="percentage"
                type="number"
                @input="calculateAmount"
                class="mt-1 block w-full"
                v-model="amount"
              />
            </div>

            <div className="mb-4  mr-5">
              <InputLabel
                for="discount"
                value="ملاحظة"
              />
              <TextInput
                id="discount"
                type="text"
                class="mt-1 block w-full"
                v-model="note"
              />
            </div>
            <div className="mb-4  mr-5 print:hidden">
              <InputLabel for="pay" value="تأكيد الدفع" />
              <button
                @click.prevent="confirmAddPaymentTotal(amount, client_Select,discount,note)"
                :disabled="isLoading"
                class="px-6 mb-12 py-2 mt-1 font-bold text-white bg-green-500 rounded"
                style="width: 100%"
              >
                <span v-if="showErorrAmount">يرجى مراجعة المبلغ ل</span>
                <span v-if="!isLoading">دفع</span>
                <span v-else>جاري الطباعة...</span>
              </button>
            </div>
          </div>
          <div class="relative overflow-x-auto shadow-md sm:rounded-lg mt-4 mb-5"  v-if="showTransactions">
                  <table class="w-full text-sm text-right text-gray-500 dark:text-gray-200 dark:text-gray-400 text-center">
                  <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400 text-center" >
                  <tr  class="bg-rose-500 text-gray-100 rounded-l-lg mb-2 sm:mb-0">
                    <th className="px-1 py-2 text-base">#</th>
                    <th className="px-1 py-2 text-base">{{$t('date')}}</th>
                    <th className="px-1 py-2 text-base">{{$t('description')}}</th>
                    <th className="px-1 py-2 text-base">{{$t('amount')}}</th>
                    <th
                      scope="col"
                      class="px-1 py-2 text-base print:hidden"
                      style="width: 250px"
                    >
                      {{ $t("execute") }}
                    </th>
                  </tr>
                </thead>
                <tbody>
                  <tr class="text-center px-4 py-2 border dark:border-gray-800 dark:text-gray-200" >
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td className="px-4 py-2 border dark:border-gray-800 dark:text-gray-200"> 
                    <a  target="_blank"
                    style="display: inline-flex;"
                    :href="`/api/getIndexAccountsSelas?user_id=${laravelData.client.id}&from=${from}&to=${to}&print=4`"
                    tabIndex="1"
                    class="px-4 py-1  text-white  m-1 bg-blue-500 rounded"
                    >
                    جميع الدفعات
                    <print />
                    </a>
            
                     </td>
                   
                  </tr>
                  <template  v-for="user in (laravelData?.transactions || [])" :key="user.id">
                  <tr class="text-center" v-if="user.type=='out' && user.amount < 0 && user.is_pay == 1 ">
                  <td className="px-4 py-2 border dark:border-gray-800 dark:text-gray-200">{{ indexs++ }}</td>
                  <td className="px-4 py-2 border dark:border-gray-800 dark:text-gray-200">{{ user.id }}</td>
                  <td className="px-4 py-2 border dark:border-gray-800 dark:text-gray-200">{{ user.created }}</td>
                  <td className="px-4 py-2 border dark:border-gray-800 dark:text-gray-200">{{ user.description }}</td>
                  <td className="px-4 py-2 border dark:border-gray-800 dark:text-gray-200">{{ user.amount*-1  }}</td>
                  <td className="px-4 py-2 border dark:border-gray-800 dark:text-gray-200">  
                    <a v-if="user.type =='out' && user.amount<0" target="_blank"
                    style="display: inline-flex;"
                    :href="`/api/getIndexAccountsSelas?user_id=${laravelData.client.id}&from=${from}&to=${to}&print=2&transactions_id=${user.id}`"
                    tabIndex="1"
                    class="px-4 py-1  text-white  m-1 bg-green-500 rounded"
                    >
                    <print />
                    </a>
            
                    <!-- <button
                      tabIndex="1"
                      class="px-1 py-1  text-white mx-1 bg-orange-500 rounded"
                      @click="openModalDelClient(user)"
                    >
                      <trash />
                    </button> -->
                  </td>
                  </tr>
                  </template>
                  <tr class="text-center px-4 py-2 border dark:border-gray-800 dark:text-gray-200" >
                    <td>مجموع الخصومات</td>
                    <td>{{ laravelData?.cars_discount }}</td>
                    <td>مجموع الدفعات</td>
                    <td className="px-4 py-2 border dark:border-gray-800 dark:text-gray-200"> 
                      {{ ((calculateTotalFilteredAmount().totalAmount)*-1)}}
                     </td>
                     <td>النتاتج : {{ ((calculateTotalFilteredAmount().totalAmount)*-1)-parseInt(laravelData?.cars_discount) }}</td>

                  </tr>
                </tbody>
              </table>
          </div>
          <div>
            <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
              <table
                class="w-full text-sm text-right text-gray-500 dark:text-gray-200 dark:text-gray-400 text-center"
              >
                <thead
                  class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400 text-center"
                >
                  <tr class="rounded-l-lg mb-2 sm:mb-0">
                    <th scope="col" class="px-1 py-2 text-base">
                      {{ $t("no") }}
                    </th>
                    <th scope="col" class="px-1 py-2 text-base">
                      {{ $t("car_type") }}
                    </th>
                    <th scope="col" class="px-1 py-2 text-base">
                      {{ $t("year") }}
                    </th>
                    <th scope="col" class="px-1 py-2 text-base">
                      {{ $t("color") }}
                    </th>
                    <th scope="col" class="px-1 py-2 text-base">
                      {{ $t("vin") }}
                    </th>
                    <th scope="col" class="px-1 py-2 text-base">
                      {{ $t("car_number") }}
                    </th>
                    <th scope="col" class="px-1 py-2 text-base">
                      {{ $t("dinar") }}
                    </th>
                    <th scope="col" class="px-1 py-2 text-base">
                      {{ $t("dolar_price") }}
                    </th>
                    <th scope="col" class="px-1 py-2 text-base print:hidden">
                      {{ $t("dolar_custom") }}
                    </th>
                    <th scope="col" class="px-1 py-2 text-base print:hidden">
                      {{ $t("note") }}
                    </th>
                    <th scope="col" class="px-1 py-2 text-base print:hidden">
                     جمرك منفست 
                    </th>
                    <th scope="col" class="px-1 py-2 text-base print:hidden">
                     خصم مباشر 
                    </th>
                    <th scope="col" class="px-1 py-2 text-base">
                      {{ $t("shipping_dolar") }}
                    </th>
                    <th scope="col" class="px-1 py-2 text-base">
                      {{ $t("coc_dolar") }}
                    </th>
                    <th scope="col" class="px-1 py-2 text-base">
                      {{ $t("checkout") }}
                    </th>
                    <th scope="col" class="px-1 py-2 text-base">
                      {{ $t("expenses") }}
                    </th>
                    <th scope="col" class="px-1 py-2 text-base">
                      نقل بري
                    </th>


                    <th scope="col" class="px-1 py-2 text-base">
                      {{ $t("total") }}
                    </th>
                    <th scope="col" class="px-1 py-2 text-base">
                      {{ $t("paid") }}
                    </th>
                    <th scope="col" class="px-1 py-2 text-base">
                      {{ $t("discount") }}
                    </th>
                    <th scope="col" class="px-1 py-2 text-base bg-blue-500 text-white">
                      الرصيد المتصل 💰
                    </th>
                    <th scope="col" class="px-1 py-2 text-base">
                      {{ $t("date") }}
                    </th>

                    <th
                      scope="col"
                      class="px-1 py-2 text-base print:hidden"
                      style="width:250px"
                    >
                      {{ $t("execute") }}
                    </th>
                    <th
                      scope="col"
                      class="px-1 py-2 text-base print:hidden"
                      style="width:100px"
                    >
                      تخزين
                    </th>
                    <th
                      scope="col"
                      class="px-1 py-2 text-base print:hidden"
                      style="width:120px"
                    >
                      الرصيد
                    </th>
                  </tr>
                </thead>
                <tbody>
                  {{ resetRunningBalance() }}
                  <template v-for="(item, i) in getMergedData()" :key="item.type + '-' + item.data.id">
                  <!-- صف السيارة -->
                  <tr
                    v-if="item.type === 'car'"
                    v-show="(item.data.results == 2 && showComplatedCars)|| item.data.results!=2"
                    :class="{
                      'bg-red-100 dark:bg-red-900': item.data.results == 0,
                      'bg-red-100 dark:bg-red-900': item.data.results == 1,
                      'bg-green-100 dark:bg-green-900': item.data.results == 2,
                      'bg-yellow-100 dark:bg-yellow-900':(props.q && (item.data.vin.startsWith(props.q) || (item.data.car_number ? item.data.car_number.toString().startsWith(props.q) : false))),
                    }
                    "
                    class="border-b dark:bg-gray-900 dark:border-gray-900 hover:bg-gray-50 dark:hover:bg-gray-600"
                  >
                    <td
                      className="border dark:border-gray-800 text-center px-2 py-1"
                    >
                      {{ i + 1 }}
                    </td>
                    <td
                      className="border dark:border-gray-800 text-center px-2 py-1"
                    >
                      {{ item.data.car_type }}
                    </td>
                    <td
                      className="border dark:border-gray-800 text-center px-2 py-1"
                    >
                      {{ item.data.year }}
                    </td>
                    <td
                      className="border dark:border-gray-800 text-center px-2 py-1"
                    >
                      {{ item.data.car_color }}
                    </td>
                    <td
                      className="border dark:border-gray-800 text-center px-2 py-1"
                    >
                      {{ item.data.vin }}
                    </td>
                    <td
                      className="border dark:border-gray-800 text-center px-2 py-1"
                    >
                      {{ item.data.car_number }}
                    </td>
                    <td
                      className="border dark:border-gray-800 text-center px-2 py-1"
                    >
                      {{ item.data.dinar_s }}
                    </td>
                    <td
                      className="border dark:border-gray-800 text-center px-2 py-1"
                    >
                      {{ item.data.dolar_price_s }}
                    </td>
                    <td
                      className="border dark:border-gray-800 text-center px-2 py-1 print:hidden"
                    >
                      {{ ((item.data.dinar_s/item.data.dolar_price_s)*100)?.toFixed(0)||0 }}
                    </td>
                    <td
                      className="border dark:border-gray-800 text-center px-2 py-1 print:hidden"
                    >
                      {{ item.data.note }}
                    </td>
                    <td
                      className="border dark:border-gray-800 text-center px-2 py-1 print:hidden"
                    >
                      {{ ((item.data.dinar_s/130000)*100)?.toFixed(0)||0 }}
                    </td>
                    <td
                      className="border dark:border-gray-800 text-center px-2 py-1 print:hidden"
                    >
                      {{ (((item.data.dinar_s/130000)*100)?.toFixed(0)||0)-(((item.data.dinar_s/item.data.dolar_price_s)*100)?.toFixed(0)||0) }}
                    </td>
                    <td
                      className="border dark:border-gray-800 text-center px-2 py-1"
                    >
                      {{ item.data.shipping_dolar_s }}
                    </td>
                    <td
                      className="border dark:border-gray-800 text-center px-2 py-1"
                    >
                      {{ item.data.coc_dolar_s }}
                    </td>
                    <td
                      className="border dark:border-gray-800 text-center px-2 py-1"
                    >
                      {{ item.data.checkout_s }}
                    </td>
                    <td
                      className="border dark:border-gray-800 text-center px-2 py-1"
                    >
                      {{ item.data.expenses_s }}
                    </td>
                    <td
                      className="border dark:border-gray-800 text-center px-2 py-1"
                    >
                      {{ item.data.land_shipping_s }}
                    </td>
                    
                    <td
                      className="border dark:border-gray-800 text-center px-2 py-1"
                    >
                      {{ item.data.total_s.toFixed(0) }}
                    </td>
                    <td
                      className="border dark:border-gray-800 text-center px-2 py-1"
                    >
                      {{ item.data.paid }}
                    </td>
                    <td className="border dark:border-gray-800 text-center px-1 py-2 ">{{ item.data.discount}}</td>
                    <td
                      className="border dark:border-gray-800 text-center px-2 py-1 font-bold"
                      :style="{
                        backgroundColor: (item.data.paid > 0 || item.data.discount > 0) ? '#bfdbfe' : '#e5e7eb',
                        color: (item.data.paid > 0 || item.data.discount > 0) ? '#1e40af' : '#374151'
                      }"
                    >
                      {{ calculateRunningBalance(item, isRowVisible(item.data)) }}
                    </td>
                    <td
                      className="border dark:border-gray-800 text-center px-2 py-1"
                    >
                      {{ item.data.date }}
                    </td>
                    <td
                      className="border dark:border-gray-800 text-start px-2 py-1 print:hidden"
                    >
    
                      <button
                        tabIndex="1"
                        
                        class="px-1 py-1  text-white mx-1 bg-slate-500 rounded"
                        @click="openModalEditCars(item.data)"
                      >
                        <edit />
                      </button>
                      <button
                        tabIndex="1"
                        
                        class="px-1 py-1  text-white mx-1 bg-orange-500 rounded"
                        @click="openModalDelCar(item.data)"
                      >
                        <trash />
                      </button>
                      <button
                        v-if="item.data.total_s != (item.data.paid+ item.data.discount)"
                        tabIndex="1"
                        class="px-1 py-1  text-white mx-1 bg-green-500 rounded"
                        @click="openAddCarPayment(item.data)"
                      >
                        <pay />
                      </button>
                      <button
                        v-if="(item.data.contract?.price != item.data.contract?.paid) || (item.data.contract?.price_dinar != item.data.contract?.paid_dinar)"
                        tabIndex="1"
                        class="px-1 py-1  text-white mx-1 bg-pink-500 rounded"
                        @click="openModalEditCarContracts(item.data)"
                      >
                        <pay />
                      </button>
                      <button
                      v-if="!item.data.contract"
                        tabIndex="1"
                        class="px-1 py-1  text-white mx-1 bg-yellow-500 rounded"
                        @click="openModalAddCarContracts(item.data)"
                      >
                        <newContracts />
                      </button>

                      <button
                        tabIndex="1"
                        class="px-1 py-1  text-white mx-1 bg-red-500 rounded"
                        v-if="!item.data.is_exit"
                        @click="openModalAddExitCar(item.data)"
                      >
                        <exit />
                      </button>
                      <button
                        tabIndex="1"
                        class="px-1 py-1  text-white mx-1 bg-blue-500 rounded"
                        v-if="item.data.is_exit"
                        @click="openModalShowExitCar(item.data)"

                      >
                        <show />
                      </button>
                      <button
                        tabIndex="1"
                        class="px-1 py-1  text-white mx-1 bg-violet-500 rounded"
                        @click="openModalShowDriving(item.data)"

                      >
                        <document />
                      </button>
                      <a  target="_blank"
                   
                      style="display: inline-flex;"
                      :href="`/api/getIndexAccountsSelas?user_id=${laravelData.client.id}&print=6&car_id=${item.data.id}`"
                      tabIndex="1"
                      class="px-1 py-1  text-white  m-1 bg-gray-900 rounded"
                      >
                      <print class="inline-flex" />
                      </a>
                    </td>
                    <td  className="border dark:border-gray-800 text-start px-2 py-1 print:hidden">
                      <a
                        v-for="(image, index) in item.data.car_images"
                        :key="index"
                        :href="getDownloadUrl(image.name)"
                        style="cursor: pointer;"
                        target="_blank"
                      >
                        <img :src="getImageUrl(image.name)" alt="" class="px-1" style="max-width: 80px;max-height: 50px;display: inline;" />
                      </a>
                    </td>

                    
                    <td
                      className="border dark:border-gray-800 text-start px-2 py-1 print:hidden"
                    >
                      <button
                        tabIndex="1"
                        style="min-width: 100px;"
                        class="px-1 py-1  text-white mx-1 bg-green-500 rounded"
                        v-if="((((calculateTotalFilteredAmount().totalAmount)*-1)-laravelData?.cars_discount)-(laravelData?.cars_paid)) != 0"
                        @click="openModalAddPayFromBalanceCar(item.data)"
                      >
                        دفع من الرصيد
                      </button>
                      <button
                        tabIndex="1"
                        style="min-width: 100px;"
                        v-if="((((calculateTotalFilteredAmount().totalAmount)*-1)-laravelData?.cars_discount)-(laravelData?.cars_sum)) != 0 && item.data.paid"
                        class="px-1 py-1 mt-1 text-white mx-1 bg-red-500 rounded"
                        @click="openModalDelPayFromBalanceCar(item.data)"
                      >
                       اعادة للرصيد
                      </button>
                      </td>
                      
                  </tr>
                  
                  <!-- صف الدفعة -->
                  <tr
                    v-if="item.type === 'payment'"
                    class="bg-gradient-to-r from-purple-100 to-pink-100 dark:from-purple-900 dark:to-pink-900 border-b dark:border-gray-700"
                  >
                    <!-- 1. no -->
                    <td className="border dark:border-gray-800 text-center px-2 py-2 font-bold text-purple-800 dark:text-purple-200">
                      💳
                    </td>
                    <!-- 2-6. car_type to car_number -->
                    <td colspan="5" className="border dark:border-gray-800 text-start px-3 py-2">
                      <span class="font-bold text-purple-800 dark:text-purple-200">💰 دفعة من العميل:</span>
                      <span class="font-semibold mr-2">{{ item.data.description }}</span>
                    </td>
                    <!-- 7-8. dinar, dolar_price -->
                    <td colspan="2" className="border dark:border-gray-800 text-center px-2 py-2"></td>
                    <!-- 9-12. print:hidden columns -->
                    <td colspan="4" className="border dark:border-gray-800 text-center px-2 py-2 print:hidden"></td>
                    <!-- 13-17. shipping to land_shipping -->
                    <td colspan="5" className="border dark:border-gray-800 text-center px-2 py-2"></td>
                    <!-- 18. total -->
                    <td className="border dark:border-gray-800 text-center px-2 py-2 font-bold text-red-700 dark:text-red-300 text-base bg-red-50 dark:bg-red-950">
                      <span class="text-red-600 dark:text-red-400">⬇️ -</span> {{ (item.data.amount * -1).toFixed(0) }}
                    </td>
                    <!-- 19. paid -->
                    <td className="border dark:border-gray-800 text-center px-2 py-2 bg-red-50 dark:bg-red-950"></td>
                    <!-- 20. discount -->
                    <td className="border dark:border-gray-800 text-center px-2 py-2 bg-red-50 dark:bg-red-950"></td>
                    <!-- 21. الرصيد المتصل -->
                    <td
                      className="border dark:border-gray-800 text-center px-2 py-2 font-bold text-base"
                      style="background-color: #c084fc; color: white;"
                    >
                      {{ calculateRunningBalance(item, true) }}
                    </td>
                    <!-- 22. date -->
                    <td className="border dark:border-gray-800 text-center px-2 py-2 text-sm">
                      📅 {{ item.data.created }}
                    </td>
                    <!-- 23. execute print:hidden -->
                    <td className="border dark:border-gray-800 text-center px-2 py-2 print:hidden">
                      <a  target="_blank"
                        style="display: inline-flex;"
                        :href="`/api/getIndexAccountsSelas?user_id=${laravelData.client.id}&print=2&transactions_id=${item.data.id}`"
                        tabIndex="1"
                        class="px-2 py-1 text-white bg-purple-600 rounded hover:bg-purple-700"
                      >
                        <print class="inline-flex" />
                      </a>
                    </td>
                    <!-- 24. تخزين print:hidden -->
                    <td className="border dark:border-gray-800 text-center px-2 py-2 print:hidden"></td>
                    <!-- 25. الرصيد print:hidden -->
                    <td className="border dark:border-gray-800 text-center px-2 py-2 print:hidden"></td>
                  </tr>
                  </template>
                </tbody>
              </table>
            </div>
          </div>

          <div class="mt-3 text-center" style="direction: ltr">
            <TailwindPagination
              :data="laravelData"
              @pagination-change-page="getResults"
              :limit="2"
            />
          </div>
        </div>
      </div>
    </div>
    <div
      class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 dark:text-gray-400 hidden print:block"
    >
      <div class="flex flex-row">
        <div class="basis-1/2">
          توقيع صاحب الحساب
          <br />
          {{ laravelData.client?.name }}
        </div>
        <div class="basis-1/2 text-center">توقيع قسم المحاسبة</div>
        <div class="basis-1/2 text-end">توقيع المدير</div>
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
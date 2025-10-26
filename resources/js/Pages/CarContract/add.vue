<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, useForm } from "@inertiajs/inertia-vue3";
import VueTailwindDatepicker from "vue-tailwind-datepicker";
import ModalAddCarExpensesFav from "@/Components/ModalAddCarExpensesFav.vue";
import ModalAddCarExpenses from "@/Components/ModalAddCarExpenses.vue";
import ModalArchiveCar from "@/Components/ModalArchiveCar.vue";
import ModalArchiveCarBack from "@/Components/ModalArchiveCarBack.vue";
import ModalDelCar from "@/Components/ModalDelCar.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import { useToast } from "vue-toastification";
import axios from "axios";
import { ref } from "vue";
import { useI18n } from "vue-i18n";
import newContracts from "@/Components/icon/new.vue";
import show from "@/Components/icon/show.vue";
import trash from "@/Components/icon/trash.vue";
import print from "@/Components/icon/print.vue";
import { ModelListSelect } from "vue-search-select"
  // Import everythModelSelecting
import "vue-search-select/dist/VueSearchSelect.css"

import InfiniteLoading from "v3-infinite-loading";
import "v3-infinite-loading/lib/style.css";
import debounce from "lodash/debounce";

// 🔥 نظام Offline للعقود - احترافي
import { useOfflineContracts } from '@/composables/useOfflineContracts';
import OfflineContractsStatus from '@/Components/OfflineContractsStatus.vue';
import OfflineContractsModal from '@/Components/OfflineContractsModal.vue';

const { t } = useI18n();

// تفعيل نظام Offline
const {
  isOnline,
  isSyncing,
  pendingCount,
  lastSyncFormatted,
  syncErrors,
  stats,
  saveContract,
  getOfflineContracts,
  deleteOfflineContract,
  retryFailedContract,
  syncAllContracts,
  exportContractsAsJSON,
  importContractsFromJSON
} = useOfflineContracts();

// عرض modal التفاصيل
const showOfflineContractsModal = ref(false);
const props = defineProps({
  client1: Array,
  client2: Array,
  data:Object
});
const formData = ref({});
const toast = useToast();
let searchTerm = ref("");
let showClient = ref(false);
let showClientB = ref(true);

let showModalAddCarExpensesFav = ref(false);
let showModalAddCarExpenses = ref(false);
let showModalArchiveCarExpenses = ref(false);
let showModalArchiveCarExpensesBack = ref(false);
let showModalDelCar = ref(false);

let car = ref([]);
function openwModalAddCarExpensesFav(form = {}) {
  showModalAddCarExpensesFav.value = true;
}
function openwshowModalAddCarExpenses(form = {}) {
  formData.value = form;
  showModalAddCarExpenses.value = true;
}
function openwshowModalArchiveCarExpenses(form = {}) {
  formData.value = form;
  showModalArchiveCarExpenses.value = true;
}
function openwshowModalArchiveCarExpensesBack(form = {}) {
  formData.value = form;
  showModalArchiveCarExpensesBack.value = true;
}
function openModalDelCar(form = {}) {
  formData.value = form;
  showModalDelCar.value = true;
}

const currentWork = ref(true);

let resetData = ref(false);
let user_id = 0;
let page = 1;
let q = "";
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
        user_id: user_id,
        car_have_expenses: currentWork.value ? 1 : 2,
      },
    });

    const json = response.data;

    if (json.data.length < 100) {
      car.value.push(...json.data);
      $state.complete();
    } else {
      car.value.push(...json.data);
      $state.loaded();
    }

    page++;
  } catch (error) {
    console.log(error);
    //$state.error();
  }
};

function confirmExpensesCar(V) {
  axios
    .post("/api/confirmExpensesCar", V)
    .then((response) => {
      showModalAddCarExpenses.value = false;
      toast.success("تم إضافة السيارة بنجاح ", {
        timeout: 3000,
        position: "bottom-right",
        rtl: true,
      });

      refresh();
    })
    .catch((error) => {
      console.error(error);
    });
}

function getTodayDate() {
  const today = new Date();
  const year = today.getFullYear();
  const month = String(today.getMonth() + 1).padStart(2, "0");
  const day = String(today.getDate()).padStart(2, "0");
  return `${year}-${month}-${day}`;
}
function calculateSum(carexpenses) {
  // Use reduce to sum up carexpenses.amount_dollar
  return carexpenses.reduce(
    (sum, expense) => sum + (expense.amount_dollar || 0),
    0
  );
}
function calculateSumDinar(carexpenses) {
  // Use reduce to sum up carexpenses.amount_dollar
  return carexpenses.reduce(
    (sum, expense) => sum + (expense.amount_dinar || 0),
    0
  );
}

function confirmCar(car) {
  axios
    .post("/api/addCarFavorite", car)
    .then((response) => {
      showModalAddCarExpenses.value = false;
      toast.success("تم إضافة السيارة بنجاح ", {
        timeout: 3000,
        position: "bottom-right",
        rtl: true,
      });
      refresh();
      showModalAddCarExpensesFav.value = false;
    })
    .catch((error) => {
      console.error(error);
    });
}

function confirmArchiveCar(car) {
  axios
    .post("/api/confirmArchiveCar", car)
    .then((response) => {
      showModalAddCarExpenses.value = false;
      toast.success("تم نقل السيارة بنجاح ", {
        timeout: 3000,
        position: "bottom-right",
        rtl: true,
      });
      refresh();
      showModalArchiveCarExpenses.value = false;
    })
    .catch((error) => {
      console.error(error);
    });
}
function confirmArchiveCarBack(car) {
  axios
    .post("/api/confirmArchiveCarBack", car)
    .then((response) => {
      showModalArchiveCarExpensesBack.value = false;
      toast.success("تم نقل السيارة بنجاح ", {
        timeout: 3000,
        position: "bottom-right",
        rtl: true,
      });
      refresh();
      showModalArchiveCarExpenses.value = false;
    })
    .catch((error) => {
      console.error(error);
    });
}
function swiptab(tab) {
  currentWork.value = tab;
  refresh();
}

function confirmDelCarFav(V) {
  axios
    .post("/api/confirmDelCarFav", V)
    .then((response) => {
      showModalDelCar.value = false;
      toast.success("حذف السيارة بنجاح", {
        timeout: 3000,
        position: "bottom-right",
        rtl: true,
      });
      refresh();
    })
    .catch((error) => {
      console.error(error);
    });
}
const profileAdded = ref(0);
const form = props.data ? ref(props.data) : ref
({
  name_seller: "",
  phone_seller: "",
  address_seller: "",
  name_buyer: "",
  phone_buyer: "",
  address_buyer: "",
  tex_seller: 0,
  tex_seller_dinar: 0,
  tex_buyer: 0,
  tex_buyer_dinar: 0,
  vin: "",
  car_name: "",
  modal: "",
  color: "",
  size: "",
  note: "",
  no:"",
  vin_s: "",
  car_name_s: "",
  modal_s: "",
  color_s: "",
  size_s: "",

  system_note: "",
  car_price: 0,
  car_paid: 0,
  tex_seller_paid: 0,
  tex_seller_dinar_paid: 0,
  tex_buyer_paid: 0,
  tex_buyer_dinar_paid: 0,
});


const isLoading = ref(false);



let isValid = true;

// 🔥 دالة الحفظ الجديدة - تعمل Online و Offline
const submit = async (V) => {
  isLoading.value = true;
  let missingFields = [];

  if (!form.value.name_seller) missingFields.push('اسم البائع');
  if (!form.value.name_buyer) missingFields.push('اسم المشتري');
  if (!form.value.vin) missingFields.push('رقم الشانصى');
  if (!form.value.car_name) missingFields.push('السيارة');
  if (!form.value.no) missingFields.push('رقم السيارة او كاتي');

  if (missingFields.length > 0) {
    // Display an error message for the missing fields
    toast.info(`يرجى تعبئة الحقول التالية: ${missingFields.join(', ')}`, {
      timeout: 4000,
      position: "bottom-right",
      rtl: true,
    });
    isLoading.value = false;
    return;
  }

  try {
    // 🚀 استخدام نظام Offline الذكي
    const result = await saveContract(V);

    if (result.success) {
      profileAdded.value = true;

      if (result.online) {
        // تم الحفظ online مباشرة
        toast.success('✅ تم حفظ العقد بنجاح', {
          timeout: 3000,
          position: 'bottom-right',
          rtl: true
        });

        setTimeout(() => {
          isLoading.value = false;
          window.location = '/car_contract';
        }, 1000);
      } else {
        // تم الحفظ offline
        toast.warning(`⚠️ تم الحفظ محلياً - سيتم الإرسال عند الاتصال بالإنترنت
        
العقود المعلقة: ${pendingCount.value}`, {
          timeout: 5000,
          position: 'bottom-right',
          rtl: true
        });

        // إعادة تعيين النموذج للسماح بإدخال عقد جديد
        setTimeout(() => {
          isLoading.value = false;
          resetForm();
        }, 1500);
      }
    }
  } catch (error) {
    console.error('❌ خطأ في حفظ العقد:', error);
    
    toast.error(error.message || 'حدث خطأ أثناء الحفظ', {
      timeout: 3000,
      position: 'bottom-right',
      rtl: true
    });
    
    isLoading.value = false;
  }
};

// إعادة تعيين النموذج
const resetForm = () => {
  form.value = {
    name_seller: "",
    phone_seller: "",
    address_seller: "",
    name_buyer: "",
    phone_buyer: "",
    address_buyer: "",
    tex_seller: 0,
    tex_seller_dinar: 0,
    tex_buyer: 0,
    tex_buyer_dinar: 0,
    vin: "",
    car_name: "",
    modal: "",
    color: "",
    size: "",
    note: "",
    no:"",
    vin_s: "",
    car_name_s: "",
    modal_s: "",
    color_s: "",
    size_s: "",
    system_note: "",
    car_price: 0,
    car_paid: 0,
    tex_seller_paid: 0,
    tex_seller_dinar_paid: 0,
    tex_buyer_paid: 0,
    tex_buyer_dinar_paid: 0,
  };
};

 
const updateSeller = (v) => {
      const selectedClient = props.client1.find(
        (client) => client.name_seller ===  v
      );

      if (selectedClient) {
        form.value.phone_seller = selectedClient?.phone_seller || 0;
        form.value.address_seller = selectedClient?.address_seller||0;
      }
    }
const updateBuyer = (v) => {
      const selectedClient = props.client2.find(
        (client) => client.name_buyer ===  v
      );
      console.log(selectedClient)

      if (selectedClient) {
        form.value.phone_buyer = selectedClient?.phone_buyer;
        form.value.address_buyer = selectedClient?.address_buyer;
      }
    }
const validTexSeller = (v) =>{
  let amount = form.value.tex_seller -v
if(amount < 0){
  form.value.tex_seller_paid =0
  setTimeout(() => {
    form.value.tex_seller_paid = form.value.tex_seller

  }, 1000);
    toast.info(" المبلغ اكبر من  دلالى بالدولار"+" "+form.value.tex_seller, {
        timeout: 4000,
        position: "bottom-right",
        rtl: true,
      });
}
}
const validTexSellerDinar = (v) =>{
  let amount = form.value.tex_seller_dinar -v
if(amount < 0){
  form.value.tex_seller_dinar_paid
  form.value.tex_seller_dinar_paid =0
  setTimeout(() => {
    form.value.tex_seller_dinar_paid = form.value.tex_seller_dinar

  }, 1000);
    toast.info(" المبلغ اكبر من  دلالى بالدينار"+" "+form.value.tex_seller_dinar, {
        timeout: 4000,
        position: "bottom-right",
        rtl: true,
      });
}
}
function checkApisVin(vin) {
    if (vin.length === 17) {
      VinApi(vin.toUpperCase())
    } 
}
function VinApi (v){
    axios.get(`https://vpic.nhtsa.dot.gov/api/vehicles/decodevinvalues/${v}?format=json`)
  .then(response => {
    form.value.car_name=(response.data.Results[0].Make ? response.data.Results[0].Make:response.data.Results[0].Manufacturer)+' '+response.data.Results[0].Model
    form.value.modal=response.data.Results[0].ModelYear
    form.value.size=response.data.Results[0].Doors
    form.value.vin=''
    form.value.vin=response.data.Results[0].VIN
    
  })
  .catch(error => {
    console.error(error);
  })
}

function checkApisVin1(vin) {
    if (vin.length === 17) {
      VinApi1(vin.toUpperCase())
    } 
}
function VinApi1 (v){
    axios.get(`https://vpic.nhtsa.dot.gov/api/vehicles/decodevinvalues/${v}?format=json`)
  .then(response => {
    form.value.car_name_s=(response.data.Results[0].Make ? response.data.Results[0].Make:response.data.Results[0].Manufacturer)+' '+response.data.Results[0].Model
    form.value.modal_s=response.data.Results[0].ModelYear
    form.value.size_s=response.data.Results[0].Doors
    form.value.vin_s=response.data.Results[0].VIN
    
  })
  .catch(error => {
    console.error(error);
  })
}
</script>

<template>
  <Head title="Dashboard" />
  <AuthenticatedLayout>
    <div v-if="profileAdded">
      <div
        id="alert-2"
        class="p-4 mb-4 bg-green-300 rounded-lg dark:bg-green-300 text-center"
        role="alert"
      >
        <div
          class="ml-3 text-sm font-medium text-green-700 dark:text-green-800"
        >
          تم ادخال العقد بنجاح
        </div>
      </div>
    </div>
    <form name="createForm">
      <div class="flex flex-wrap my-6">
          <div class="md:w-1/2 w-full">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
              <div class="bg-white  dark:bg-gray-900 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white dark:bg-gray-900">
                  <h2 class="text-center text-xl py-2 dark:text-white">الطرف الاول (البائع)</h2>
                  <div className="flex flex-col">
                    <div className="mb-4">
                      <InputLabel for="name" value="الأسم"  :class="{'list-item text-red-600':!form.name_seller}" />
                      <div class="relative">
                        <ModelListSelect
                          v-if="!showClient"
                          optionValue="name_seller"
                          optionText="name_seller"
                          class="mt-1 "
                          v-model="form.name_seller"
                          :list="client1"
                          @searchchange="updateSeller(form.name_seller)"
                          :placeholder="$t('selectCustomer')">
                        </ModelListSelect>
                          <button
                            type="button"
                            @click="
                              showClient = true;
                              form.name_seller = '';
                              form.phone_seller = '';
                              form.address_seller = '';
                            "
                            v-if="!showClient"
                            class="absolute left-0 top-0 h-full px-3 py-2 font-bold text-white bg-green-500 rounded-tl-lg rounded-bl-lg"
                          >
                            بائع جديد
                          </button>
                        </div>
                        <div  v-if="showClient">
                          <div class="relative" >
                          <input
                           
                            type="text"
                            class="mt-1  block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700  dark:text-gray-200 dark:border-gray-900"
                            v-model="form.name_seller"
                          />
                          <button
                            type="button"
                            @click="
                              showClient = false;
                              form.name_seller = '';
                              form.phone_seller = '';
                              form.address_seller = '';
                            "
                            v-if="showClient"
                            class="absolute left-0 top-0 h-full px-3 py-2 font-bold text-white bg-pink-500 rounded-tl-lg rounded-bl-lg"
                          >
                            تحديد بائع
                          </button>
                        </div>

  
                        </div>
                    </div>
         
                    <div className="mb-4">
                        <InputLabel for="address_seller" value="العنوان" />
                        <TextInput
                          type="text"
                          class="mt-1 block w-full"
                          v-model="form.address_seller"
                        />
                        </div>
                        <div className="mb-4">
                          <InputLabel for="phone_seller" value="رقم الهاتف" />
                          <TextInput
                            type="text"
                            class="mt-1 block w-full"
                            v-model="form.phone_seller"
                          />
                        </div>
                    <div class="flex justify-center">
                      <div className="mb-4 ml-5">
                        <InputLabel for="tex_seller" value="دلالى دولار" />
                        <TextInput
                          type="number"
                          class="mt-1"
                          @focus="form.tex_seller=form.tex_seller ? form.tex_seller :'100'"
                          v-model="form.tex_seller"
                        />
                      </div>
                      <div className="mb-4">
                        <InputLabel for="tex_seller_dinar" value="دلالى دينار" />
                        <TextInput
                          type="number"
                          class="mt-1"
                          @focus="form.tex_seller_dinar=form.tex_seller_dinar?'form.tex_seller_dinar':'150000'"
                          v-model="form.tex_seller_dinar"
                        />
                      </div>
                    </div>
                    <div class="flex justify-center">
                      <div className="mb-4 ml-5">
                        <InputLabel for="tex_seller_paid" value="مدفوع دولار" />
                        <TextInput
                          type="number"
                          class="mt-1"
                          @focus="form.tex_seller_paid=form.tex_seller"
                          @input="validTexSeller(form.tex_seller_paid)"
                          v-model="form.tex_seller_paid"
                        />
                      </div>
                      <div className="mb-4">
                        <InputLabel for="tex_seller_dinar_paid" value="مدفوع دينار" />
                        <TextInput
                          type="number"
                          class="mt-1"
                          @focus="form.tex_seller_dinar_paid=form.tex_seller_dinar"
                          @input="validTexSellerDinar(form.tex_seller_dinar_paid)"
                          v-model="form.tex_seller_dinar_paid"
                        />
                      </div>
                    </div>
                    <div class="flex justify-center">
                      <div className="mb-4 ml-5">
                        <InputLabel for="debit" value="متبقي دولار" />
                        <TextInput
                          type="number"
                          class="mt-1"
                          :value="form.tex_seller-form.tex_seller_paid"
                          disabled
                        />
                      </div>
                      <div className="mb-4">
                        <InputLabel for="debit_dinar" value="متبقي دينار" />
                        <TextInput
                          type="number"
                          class="mt-1"
                          :value="form.tex_seller_dinar-form.tex_seller_dinar_paid"
                          disabled

                        />
                      </div>
                    </div>
                    <div className="mb-4">
                      <InputLabel for="note" value="ملاحظة" />
                      <TextInput
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.note"
                      />
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="md:w-1/2 w-full">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
              <div class="bg-white  dark:bg-gray-900 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white  dark:bg-gray-900">
                  <h2 class="text-center text-xl py-2  dark:text-white">الطرف الثاني (المشتري)</h2>

                  <div className="flex flex-col">
        
                    <div className="mb-4">
                      <InputLabel for="name" value="الأسم"  :class="{'list-item text-red-600':!form.name_buyer}" />
                      <div class="relative">
                        <ModelListSelect
                          v-if="!showClientB"
                          optionValue="name_buyer"
                          optionText="name_buyer"
                          class="mt-1"
                          v-model="form.name_buyer"
                          :list="client2"
                          @searchchange="updateBuyer(form.name_buyer)"
                          :placeholder="$t('selectCustomer')">
                        </ModelListSelect>
                          <button
                            type="button"
                            @click="
                              showClientB = true;
                              form.name_buyer = '';
                              form.phone_seller = '';
                              form.address_seller = '';
                            "
                            v-if="!showClientB"
                            class="absolute left-0 top-0 h-full px-3 py-2 font-bold text-white bg-green-500 rounded-tl-lg rounded-bl-lg"
                          >
                            بائع جديد
                          </button>
                        </div>
                        <div  v-if="showClientB">
                          <div class="relative" >
                          <input
                           
                            type="text"
                            class="mt-1  block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
                            v-model="form.name_buyer"
                          />
                          <button
                            type="button"
                            @click="
                              showClientB = false;
                              form.name_buyer = '';
                              form.phone_seller = '';
                              form.address_seller = '';
                            "
                            v-if="showClientB"
                            class="absolute left-0 top-0 h-full px-3 py-2 font-bold text-white bg-pink-500 rounded-tl-lg rounded-bl-lg"
                          >
                            تحديد بائع
                          </button>
                        </div>

  
                        </div>
                    </div>
                    <div className="mb-4">
                      <InputLabel for="address_buyer" value="العنوان" />
                      <TextInput
                        id="address_buyer"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.address_buyer"
                      />
                    </div>

                    <div className="mb-4">
                      <InputLabel for="phone_buyer" value="رقم الهاتف" />
                      <TextInput
                        id="phone_buyer"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.phone_buyer"
                      />
                    </div>

                    <div class="flex justify-center">
                      <div className="mb-4 ml-5">
                        <InputLabel for="tex_buyer" value="دلالى دولار" />
                        <TextInput
                          type="number"
                          class="mt-1"
                          @focus="form.tex_buyer=form.tex_buyer?form.tex_buyer:'100'"
                          v-model="form.tex_buyer"
                        />
                      </div>
                      <div className="mb-4">
                        <InputLabel for="tex_buyer_dinar" value="دلالى دينار" />
                        <TextInput
                          type="number"
                          class="mt-1"
                          @focus="form.tex_buyer_dinar=form.tex_buyer_dinar?form.tex_buyer_dinar:'150000'"
                          v-model="form.tex_buyer_dinar"
                        />
                      </div>
                    </div>
                    <div class="flex justify-center">
                      <div className="mb-4 ml-5">
                        <InputLabel for="tex_buyer_paid" value="مدفوع دولار" />
                        <TextInput
                          type="number"
                          class="mt-1"
                          @focus="form.tex_buyer_paid=form.tex_buyer"
                          v-model="form.tex_buyer_paid"
                        />
                      </div>
                      <div className="mb-4">
                        <InputLabel for="tex_buyer_dinar_paid" value="مدفوع دينار" />
                        <TextInput
                          type="number"
                          class="mt-1"
                          @focus="form.tex_buyer_dinar_paid=form.tex_buyer_dinar"
                          v-model="form.tex_buyer_dinar_paid"
                        />
                      </div>
                    </div>
                    <div class="flex justify-center">
                      <div className="mb-4 ml-5">
                        <InputLabel for="phone_number" value="متبقي دولار" />
                        <TextInput
                          type="number"
                          class="mt-1"
                          :value="form.tex_buyer-form.tex_buyer_paid"
                          disabled
                        />
                      </div>
                      <div className="mb-4">
                        <InputLabel for="phone_number" value="متبقي دينار" />
                        <TextInput
                          type="number"
                          class="mt-1"
                          :value="form.tex_buyer_dinar-form.tex_buyer_dinar_paid"
                          disabled
                        />
                      </div>
                    </div>
                  </div>
                  <div className="mb-4">
                    <InputLabel for="system_note" value="ملاحظة ستاف" />

                    <TextInput
                      id="system_note"
                      type="text"
                      class="mt-1 block w-full"
                      v-model="form.system_note"
                    />
                  </div>
                </div>
              </div>
            </div>
          </div>
      </div>
      <div class="flex">
        <div class="grow">
          <div class="pb-3">
            <div class="mx-auto mx-7">
              <div class="bg-white  dark:bg-gray-900 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white  dark:bg-gray-900">
                  <div class="flex flex-wrap my-6 justify-center">
                    <div class="md:w-1/3 w-full">
                      <div className="mb-4 mx-5">
                        <InputLabel for="vin" value="رقم الشاصى" :class="{'list-item text-red-600':!form.vin}" />
                        <TextInput
                          type="text"
                          @input="checkApisVin(form.vin)"
                          class="mt-1 block w-full"
                          v-model="form.vin"
                        />
                      </div>
                    </div>
                    <div class="md:w-1/3 w-full">
                      <div className="mb-4 mx-5">
                        <InputLabel for="car_name" value="السيارة" :class="{'list-item text-red-600':!form.car_name}" />
                        <TextInput
                          type="text"
                          class="mt-1 block w-full"
                          v-model="form.car_name"
                        />
                      </div>
                    </div>
                    <div class="md:w-1/3 w-full">
                      <div className="mb-4 mx-5">
                        <InputLabel for="modal" value="موديل" />
                        <TextInput
                          type="number"
                          class="mt-1 block w-full"
                          v-model="form.modal"
                        />
                      </div>
                    </div>
                    <div class="md:w-1/3 w-full">
                      <div className="mb-4 mx-5">
                        <InputLabel for="color" value="اللون" />
                        <TextInput
                          type="text"
                          class="mt-1 block w-full"
                          v-model="form.color"
                        />
                      </div>
                    </div>
                    <div class="md:w-1/3 w-full">
                      <div className="mb-4 mx-5">
                        <InputLabel for="size" value="قماره" />
                        <TextInput
                          type="number"
                          class="mt-1 block w-full"
                          v-model="form.size"
                        />
                      </div>
                    </div>
                    <div class="md:w-1/3 w-full">
                      <div className="mb-4 mx-5">
                        <InputLabel
                          for="no"
                          value="رقم السيارة او كاتي"
                          :class="{'list-item text-red-600':!form.no}"
                        />
                        <TextInput
                          type="text"
                          class="mt-1 block w-full"
                          v-model="form.no"
                        />
                      </div>
                    </div>
                  </div>
                  <div class="flex justify-center">
                    <div class="md:w-1/3 w-full">
                      <div className="mb-4 mx-5">
                        <InputLabel
                          for="car_price"
                          value="سعر السيارة بالدولار"
                        />
                        <TextInput
                          type="number"
                          @focus="form.car_price=''"
                          class="mt-1 block w-full"
                          v-model="form.car_price"
                        />
                      </div>
                    </div>
                    <div class="md:w-1/3 w-full">
                      <div className="mb-4 mx-5">
                        <InputLabel
                          for="car_paid"
                          value="المدفوع بالدولار"
                        />
                        <TextInput
                          type="number"
                          class="mt-1 block w-full"
                          @focus="form.car_paid=form.car_price"
                          v-model="form.car_paid"
                        />
                      </div>
                    </div>
                    <div class="md:w-1/3 w-full">
                      <div className="mb-4 mx-5">
                        <InputLabel for="relatives" value="المتبقي بالدولار" />
                        <TextInput
                          type="number"
                          class="mt-1 block w-full"
                          :value="form.car_price-form.car_paid"
                          disabled
                        />
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>


      <div class="flex">
        <div class="grow">
          <div class="pb-3">
            <div class="mx-auto mx-7">
              <div class="bg-white  dark:bg-gray-900 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white  dark:bg-gray-900">
                  <h5 class="text-center py-4  dark:text-white">السيارة البديلة</h5>

                  <div class="flex flex-wrap justify-center">
                    <div class="md:w-1/3 w-full">
                      <div className="mb-4 mx-5">
                        <InputLabel for="vin_s" value="رقم الشاصى"  />
                        <TextInput
                          type="text"
                          @input="checkApisVin1(form.vin_s)"
                          class="mt-1 block w-full"
                          v-model="form.vin_s"
                        />
                      </div>
                    </div>
                    <div class="md:w-1/3 w-full">
                      <div className="mb-4 mx-5">
                        <InputLabel for="car_name_s" value="السيارة"  />
                        <TextInput
                          type="text"
                          class="mt-1 block w-full"
                          v-model="form.car_name_s"
                        />
                      </div>
                    </div>
                    <div class="md:w-1/3 w-full">
                      <div className="mb-4 mx-5">
                        <InputLabel for="modal_s" value="موديل" />
                        <TextInput
                          type="number"
                          class="mt-1 block w-full"
                          v-model="form.modal_s"
                        />
                      </div>
                    </div>
                    <div class="md:w-1/3 w-full">
                      <div className="mb-4 mx-5">
                        <InputLabel for="color_s" value="اللون" />
                        <TextInput
                          type="text"
                          class="mt-1 block w-full"
                          v-model="form.color_s"
                        />
                      </div>
                    </div>
                    <div class="md:w-1/3 w-full">
                      <div className="mb-4 mx-5">
                        <InputLabel for="size_s" value="قماره" />
                        <TextInput
                          type="number"
                          class="mt-1 block w-full"
                          v-model="form.size_s"
                        />
                      </div>
                    </div>
                    <div class="md:w-1/3 w-full">
                      <div className="mb-4 mx-5">
                        <InputLabel
                          for="no_s"
                          value="رقم السيارة او كاتي"
                         
                        />
                        <TextInput
                          type="text"
                          class="mt-1 block w-full"
                          v-model="form.no_s"
                        />
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div className="flex items-center justify-center my-6 ">
     
     
        <Link
          className="px-6 mx-2 py-2 mb-12 text-white bg-gray-500 rounded-md focus:outline-none rounded"
          :href="route('car_contract')"
        >
          العودة
        </Link>

        <button
          v-if="!data"
          @click="submit(form)"
          :disabled="isLoading"
          class="px-6 mb-12 mx-2 py-2 font-bold text-white bg-rose-500 rounded"
        >
          <span v-if="!isLoading">حفظ</span>
          <span v-else>جاري الحفظ...</span>
        </button>

        <button 
          v-if="data"
          @click="submit(form)"
          :disabled="isLoading"
          class="px-6 mb-12 mx-2 py-2 font-bold text-white bg-rose-500 rounded"
        >
          <span v-if="!isLoading">حفظ التعديلات</span>
          <span v-else>جاري الحفظ...</span>
        </button>
    
        
        
      </div>
    </form>

    <!-- 🔥 شريط حالة Offline -->
    <OfflineContractsStatus
      :isOnline="isOnline"
      :isSyncing="isSyncing"
      :pendingCount="pendingCount"
      :lastSyncFormatted="lastSyncFormatted"
      :syncErrors="syncErrors"
      :stats="stats"
      @sync="syncAllContracts"
      @view-details="showOfflineContractsModal = true"
    />

    <!-- 🔥 Modal تفاصيل العقود المحفوظة -->
    <OfflineContractsModal
      :show="showOfflineContractsModal"
      :isOnline="isOnline"
      :isSyncing="isSyncing"
      :contracts="getOfflineContracts()"
      :stats="stats"
      :lastSyncFormatted="lastSyncFormatted"
      :syncErrors="syncErrors"
      @close="showOfflineContractsModal = false"
      @sync-all="syncAllContracts"
      @retry="retryFailedContract"
      @delete="deleteOfflineContract"
      @export="exportContractsAsJSON"
      @import="importContractsFromJSON"
    />
  </AuthenticatedLayout>
</template>
<style scoped>
.list-item{
  display: list-item;
}
</style>
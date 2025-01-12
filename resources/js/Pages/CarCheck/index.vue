<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/inertia-vue3';
import VueTailwindDatepicker from 'vue-tailwind-datepicker'
import ModalAddCarExpensesFav from "@/Components/ModalAddCarExpensesFav.vue";
import ModalAddCarExpenses from "@/Components/ModalAddCarExpenses.vue";
import ModalArchiveCar from "@/Components/ModalArchiveCar.vue";
import ModalArchiveCarBack from "@/Components/ModalArchiveCarBack.vue";
import ModalDelCar from "@/Components/ModalDelCar.vue";


import { useToast } from "vue-toastification";
import axios from 'axios';
import { ref } from 'vue';
import { useI18n } from "vue-i18n";
import newContracts from "@/Components/icon/new.vue";
import show from "@/Components/icon/show.vue";
import trash from "@/Components/icon/trash.vue";
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
let loading = ref(false); // بدء التحميل
let showModalAddCarExpensesFav =  ref(false);
let showModalAddCarExpenses =  ref(false);
let showModalArchiveCarExpenses=  ref(false);
let showModalArchiveCarExpensesBack=  ref(false);
let showModalDelCar = ref(false);

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
function openModalDelCar(form={}) {
  formData.value=form
  showModalDelCar.value = true;
}

const currentWork = ref(true);


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
        user_id: user_id,
        car_have_expenses:currentWork.value?1:2
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

 

function confirmExpensesCar(V) { 
  axios.post('/api/confirmExpensesCar',V)
  .then(response => {
    showModalAddCarExpenses.value = false;
    toast.success( "تم إضافة السيارة بنجاح ", {
        timeout: 3000,
        position: "bottom-right",
        rtl: true

      });


      refresh()

  })
  .catch(error => {
    console.error(error);
  })
}


function getTodayDate() {
  const today = new Date();
  const year = today.getFullYear();
  const month = String(today.getMonth() + 1).padStart(2, '0');
  const day = String(today.getDate()).padStart(2, '0');
  return `${year}-${month}-${day}`;
}
function  calculateSum(carexpenses) {
      // Use reduce to sum up carexpenses.amount_dollar
      return carexpenses.reduce((sum, expense) => sum + (expense.amount_dollar || 0), 0);
    }
function  calculateSumDinar(carexpenses) {
      // Use reduce to sum up carexpenses.amount_dollar
      return carexpenses.reduce((sum, expense) => sum + (expense.amount_dinar || 0), 0);
    }

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
    console.error(error);
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
function swiptab(tab){
  currentWork.value=tab
  refresh()

}


function confirmDelCarFav(V) {
  axios.post('/api/confirmDelCarFav',V)
  .then(response => {
    showModalDelCar.value = false;
    toast.success("حذف السيارة بنجاح", {
        timeout: 3000,
        position: "bottom-right",
        rtl: true

      });
    refresh();
  })
  .catch(error => {
    console.error(error);
  })


}
const vinInput = ref('');
const results = ref([]); // مصفوفة النتائج
const noResultsVINs = ref([]);
const searchVINs = async () => {
  loading.value=true;
  // تقسيم الأكواد من textarea إلى قائمة
  const vinList = vinInput.value.split('\n').map(vin => vin.trim());

  try {
    const response = await axios.post('/api/search-vins', { vins: vinList });
    results.value = response.data.results ; // استقبال النتائج كمصفوفة من المصفوفات
    noResultsVINs.value = response.data.noResultsVINs ; // استقبال النتائج كمصفوفة من المصفوفات
     
    loading.value=false;
  } catch (error) {
    console.error('خطأ في البحث:', error);
  }
};
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
            :currentWork="currentWork"
            @a="confirmExpensesCar($event)"
            @close="showModalAddCarExpenses = false"
            >
        <template #header>
          </template>
    </ModalAddCarExpenses>
    <ModalDelCar
      :show="showModalDelCar ? true : false"
      :formData="formData"
      @a="confirmDelCarFav($event)"
      @close="showModalDelCar = false"
    >
      <template #header>
        <h2 class="mb-5 dark:text-gray-400 text-center">
          هل متأكد من حذف السيارة ؟
        </h2>
      </template>
    </ModalDelCar>

    <AuthenticatedLayout>
        <div class="py-2" v-if="$page.props.auth.user.type_id==1||$page.props.auth.user.type_id==7">
          <ul class="sm:px-6 lg:px-8 text-sm font-medium text-center text-gray-500 rounded-lg  flex dark:divide-gray-700 dark:text-gray-400">
            <li class="w-full">
                <button @click="swiptab(true)" class="inline-block w-full p-4 border-r border-gray-200 dark:border-gray-700 hover:text-gray-700 hover:bg-gray-50  focus:outline-none  dark:text-white" :class="!currentWork ? 'dark:bg-gray-800 dark:hover:bg-gray-700':'bg-white  dark:bg-gray-900'" >قيد العمل </button>
            </li>
            <li class="w-full">
                <button @click="swiptab(false)" class="inline-block w-full p-4 border-r border-gray-200 dark:border-gray-700 hover:text-gray-700 hover:bg-gray-50  focus:outline-none  dark:text-white" :class="currentWork ? 'dark:bg-gray-800 dark:hover:bg-gray-700':'bg-white  dark:bg-gray-900'" >السيارة المكتملة</button>
            </li>
     
          </ul>

        <div class="max-w-9xl mx-auto sm:px-6 lg:px-8 ">
            <div class="bg-white overflow-hidden shadow-sm ">
                  <div class="p-6 dark:bg-gray-900">
                      <div class="flex flex-col">
                        <textarea v-model="vinInput" placeholder="أدخل الأكواد هنا" style="height: 500px;"></textarea>
                        <br>
                        <!-- زر البحث -->
                        <button 
                          class="px-6 mb-12 mx-2 py-2 font-bold text-white bg-green-500 rounded" 
                          @click="searchVINs" 
                          :disabled="loading"
                        >
                          <span v-if="!loading">تشغيل</span>
                          <span v-else>جاري التحميل...</span>
                        </button>

                        <!-- مؤشر التحميل -->
                        <div v-if="loading" class="text-center mt-4 text-gray-500">
                          <span class="loader"></span> <!-- يمكنك استخدام CSS للـ loader -->
                          <p>جاري البحث، يرجى الانتظار...</p>
                        </div>

                        <h3 class="text-center h3 py-3">النتائج</h3>
                        <!-- عرض الأرقام التي ليس لها نتائج -->
                        <div v-if="noResultsVINs.length">
                          <h3 class="mt-8 text-lg font-semibold text-red-500">الأرقام التي ليس لها نتائج:</h3>
                          <ul>
                            <li v-for="(vin, index) in noResultsVINs" :key="index">
                              <p class="text-sm text-gray-300 mb-2">
                                <span class="font-bold">
                                  {{ index+1 }}--
                                  رقم الشاصي:

                                </span> {{ vin }}
                              </p>
                            </li>
                          </ul>
                        </div>

                        <div v-if="results.length">
                          <div v-for="(resultSet, index) in results" :key="index">
                            <h4 class="text-lg font-semibold text-gray-100">بحث {{ index + 1 }}</h4>

                            <!-- عرض رقم الشاصي -->
                            <p class="text-sm text-gray-300 mb-2">
                              <span class="font-bold">رقم الشاصي:</span> {{ resultSet.vin || 'غير معروف' }}
                            </p>

                            <!-- عرض الجدول في حالة وجود نتائج -->
                            <div v-if="resultSet.cars && resultSet.cars.length">
                              <table
                                :class="resultSet.cars.length > 1 
                                        ? 'bg-yellow-200 text-gray-800' 
                                        : 'bg-emerald-200 text-gray-800'"
                                class="w-full text-sm text-right text-gray-500 dark:text-gray-200 dark:text-gray-400 text-center divide-y divide-gray-200 dark:divide-gray-800"
                              >
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400 text-center">
                                  <tr     :class="resultSet.cars.length > 1 
                                        ? 'bg-yellow-600 text-gray-100' 
                                        : 'bg-emerald-600 text-gray-100'" >
                                    <th scope="col" class="px-3 py-2 sm:px-4 sm:py-2">{{ $t("date") }}</th>
                                    <th scope="col" class="px-3 py-2 sm:px-4 sm:py-2">{{ $t('car_owner') }}</th>
                                    <th scope="col" class="px-3 py-2 sm:px-4 sm:py-2">{{ $t('car_type') }}</th>
                                    <th scope="col" class="px-3 py-2 sm:px-4 sm:py-2">{{ $t('year') }}</th>
                                    <th scope="col" class="px-3 py-2 sm:px-4 sm:py-2">{{ $t('color') }}</th>
                                    <th scope="col" class="px-3 py-2 sm:px-4 sm:py-2">{{ $t('vin') }}</th>
                                    <th scope="col" class="px-3 py-2 sm:px-4 sm:py-2">{{ $t('car_number') }}</th>
                                    <th scope="col" class="px-3 py-2 sm:px-4 sm:py-2">{{ $t("note") }}</th>
                                    <th scope="col" class="px-3 py-2 sm:px-4 sm:py-2">فرع</th>
                                  </tr>
                                </thead>
                                <tbody>
                                  <tr
                                    v-for="(car, idx) in resultSet.cars"
                                    :key="idx"
                                    class="bg-white dark:bg-gray-800"
                                  >
                                    <td class="px-3 py-2 sm:px-4 sm:py-2 text-center">{{ car.date }}</td>
                                    <td class="px-3 py-2 sm:px-4 sm:py-2 text-center">{{ car.client?.name }}</td>
                                    <td class="px-3 py-2 sm:px-4 sm:py-2 text-center">{{ car.car_type }}</td>
                                    <td class="px-3 py-2 sm:px-4 sm:py-2 text-center">{{ car.year }}</td>
                                    <td class="px-3 py-2 sm:px-4 sm:py-2 text-center">{{ car.car_color }}</td>
                                    <td class="px-3 py-2 sm:px-4 sm:py-2 text-center">{{ car.vin }}</td>
                                    <td class="px-3 py-2 sm:px-4 sm:py-2 text-center">{{ car.car_number }}</td>
                                    <td class="px-3 py-2 sm:px-4 sm:py-2 text-center">{{ car.note }}</td>
                                    <td class="px-3 py-2 sm:px-4 sm:py-2 text-center">
                                      {{ car.owner_id == 1 ? 'اربيل' : 'كركوك' }}
                                    </td>
                                  </tr>
                                </tbody>
                              </table>
                            </div>

                            <!-- عرض رسالة في حالة عدم وجود نتائج -->
                            <div v-else>
                              <p class="text-red-500">
                                لا توجد نتائج لهذا الرقم: <span class="font-bold">{{ resultSet.vin || 'غير معروف' }}</span>
                              </p>
                            </div>
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

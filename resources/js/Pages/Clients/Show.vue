<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Modal from "@/Components/Modal.vue";
import { Head, Link, useForm } from "@inertiajs/inertia-vue3";
import { ref } from "vue";
import { TailwindPagination } from "laravel-vue-pagination";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import axios from 'axios';
import ModalDelCar from "@/Components/ModalDelCar.vue";
import ModalEditCars from "@/Components/ModalEditCar_S.vue";
import ModalAddCarPayment from "@/Components/ModalAddCarPayment.vue";
import { useToast } from "vue-toastification";
const toast = useToast();

const laravelData = ref({});
const client_id = props.client_id;
const isLoading = ref(0);
let showModalEditCars=ref(false);
let showModalDelCar =  ref(false);
let showModalAddCarPayment =  ref(false);

const total = ref(0);
const formData = ref({});


const showReceiveBtn = ref(0);
const getResults = async (v,page = 1) => {
  axios.get(`/api/getIndexAccountsSelas?page=${page}&user_id=${v ? v:(client_id)}`)
  .then(response => {
    laravelData.value =  response.data;
  })
  .catch(error => {
    console.error(error);
  })
}
getResults()
const props = defineProps({
  url: String,
  clients:Array,
  client_id:String,
  client:Object,

});

const form = useForm();

let showModal = ref(false);
const pay = async (id) => {
  const response = await fetch(`/paySelse/${id}`);
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
function openModalDelCar(form={}) {
  formData.value=form
  showModalDelCar.value = true;
}
function openModalEditCars(form={}){
  formData.value=form
  showModalEditCars.value = true;
}
function openAddCarPayment(form={}) {
    formData.value=form
    showModalAddCarPayment.value = true;
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

  })
  .catch(error => {
    showModal.vcons
    toast.error("لم التعديل بنجاح", {
        timeout: 2000,
        position: "bottom-right",
        rtl: true

      });

  })
}
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
function confirmAddPaymentTotal(amount,client_id) {
  axios.get(`/api/addPaymentCarTotal?amount=${amount??0}&client_id=${client_id??''}`)
  .then(response => {
    showModalAddCarPayment.value = false;
    toast.success( " تم دفع مبلغ دولار "+amount+" بنجاح ", {
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
  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl dark:text-gray-400 text-gray-800 leading-tight">
            شركة سلام جلال
      </h2>
    </template>
    <ModalEditCars
            :formData="formData"
            :show="showModalEditCars ? true : false"
            :client="clients"
            :carModel="carModel"
            @a="confirmUpdateCar($event)"
            @close="showModalEditCars = false"
            >
        <template #header>
          </template>
    </ModalEditCars>
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
            <h2 class=" mb-5 dark:text-gray-400 text-center">

          هل متأكد من حذف السيارة
          ؟
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
    <div class="py-4">
      <h2 class="text-center pb-2 dark:text-gray-400">{{ $t('sales_bill') }}</h2>
      <div class="max-w-9xl mx-auto sm:px-6 lg:px-8 p-6 dark:bg-gray-900">
        <div class=" overflow-hidden shadow-sm sm:rounded-lg">
            <div class="flex flex-row">
              <div class="basis-1/2 pr-4">
                <InputLabel class="mb-1" for="invoice_number" :value="$t('Account')" />
                <select @change="getResults(client_id)" v-model="client_id" id="default" class="pr-8  border border-gray-300 text-gray-900 mb-6 text-sm rounded-lg focus:ring-red-500 focus:border-red-500 block w-full p-2.5 dark:border-gray-400 dark:placeholder-gray-400 dark:text-gray-600 dark:focus:ring-red-500 dark:focus:border-red-500">
                    <option value="undefined" disabled> {{ $t("selectCustomer") }}</option>
                  <option v-for="(user, index) in clients" :key="index" :value="user.id">{{ user.name }}</option>
                </select>
              </div>
              <div class="basis-1/2">
                            <div className="mb-4  mr-5">
                              <InputLabel for="totalAmount" :value="$t('phoneNumber')" />
                              <TextInput
                                id="totalAmount"
                                type="text"
                                class="mt-1 block w-full "
                                :value="laravelData.client?.phone"
                                disabled
                              />
                            </div>
                </div>
            </div>
            <div class="flex flex-row">
              <div class="grow">
                <div class="pb-3">
                  <div class="mx-auto">
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2 lg:gap-1">
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
                              <InputLabel for="car_total_complete" value="مجموع السيارات مكتمل" />
                              <TextInput
                                id="car_total_complete"
                                type="text"
                                class="mt-1 block w-full"
                                :value="laravelData.car_total_complete"
                                disabled
                              />
                            </div>
                            <div className="mb-4  mr-5">
                              <InputLabel for="car_total_unpaid" value="مجموع السيارات غير مدفوع" />
                              <TextInput
                                id="car_total_unpaid"
                                type="text"
                                class="mt-1 block w-full"
                                :value="laravelData.car_total_unpaid"
                                disabled
                              />
                            </div>
                            <div className="mb-4  mr-5">
                              <InputLabel for="car_total_uncomplete" value=" مجموع السيارات مدفوع وغير مكمل" />
                              <TextInput
                                id="car_total_uncomplete"
                                type="text"
                                class="mt-1 block w-full"
                                :value="laravelData.car_total_uncomplete"
                                disabled
                              />
                            </div>
                        </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="flex flex-row">
              <div class="grow">
                <div class="pb-3">
                  <div class="mx-auto">
                          <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2 lg:gap-1">
                            <div className="mb-4  mr-5">
                              <InputLabel for="cars_sum" :value="$t('Total_in_dollars')" />
                              <TextInput
                                id="cars_sum"
                                type="text"
                                class="mt-1 block w-full"
                                :value="laravelData?.cars_sum"
                                disabled
                              />
                            </div>
                            <div className="mb-4  mr-5">
                              <InputLabel for="cars_paid" value="مجموع المدفوع بالدولار" />
                              <TextInput
                                id="cars_paid"
                                type="text"
                                class="mt-1 block w-full"
                                :value="laravelData?.cars_paid"
                                disabled
                              />
                            </div>
                            <div className="mb-4  mr-5">
                              <InputLabel for="cars_need_paid" value="مجموع الدين بالدولار" />
                              <TextInput
                                id="cars_need_paid"
                                type="text"
                                class="mt-1 block w-full"
                                :value="laravelData?.cars_need_paid"
                                disabled
                              />
                            </div>
                            <div className="mb-4  mr-5">
                              <InputLabel for="percentage" value=" المبلغ بالدولار" />
                              <TextInput
                                id="percentage"
                                type="text"
                                class="mt-1 block w-full"
                                v-model="total"
                              />
                            </div>
                          </div>
                          <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2 lg:gap-1">
                           <div className="mb-4  mr-5 print:hidden" v-if="laravelData?.cars_need_paid">
                              <InputLabel for="pay" value="تأكيد الدفع" />
                              <button
                              @click.prevent="confirmAddPaymentTotal(total,client_id)"
                              :disabled="isLoading || !parseInt(laravelData.totalAmount)"
                              class="px-6 mb-12 py-2 mt-1 font-bold text-white bg-green-500 rounded" style="width: 100%">
                              <span v-if="!isLoading">دفع</span>
                              <span v-else>جاري الحفظ...</span>
                            </button>
                          </div>
                          </div>
                  </div>
                </div>
              </div>
            </div>
            <div>
              <table class="w-full text-sm text-right text-gray-500 dark:text-gray-200 dark:text-gray-400 text-center">
                              <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400 text-center" >
                                  <tr>
                                      <th scope="col" class="px-1 py-3 text-base	" >
                                        {{ $t('no') }}  
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
                                      <th scope="col" class="px-1 py-3 text-base print:hidden">
                                        {{ $t('dolar_custom') }}
                                      </th>
                                      <th scope="col" class="px-1 py-3 text-base print:hidden">
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
                
                                      <th scope="col" class="px-1 py-3 text-base print:hidden" style="width: 240px;">
                                        {{ $t('execute') }}
                                      </th>
                                  </tr>
                              </thead>
                              <tbody>
                                <tr v-for="(car ,i) in  laravelData.data" :key="car.id" :class="car.results == 0 ?'':car.results == 1 ?'bg-red-100 dark:bg-red-900':'bg-green-100 dark:bg-green-900'"  class=" border-b dark:bg-gray-900 dark:border-gray-900 hover:bg-gray-50 dark:hover:bg-gray-600">
                                    <td className="border dark:border-gray-800 text-center px-4 py-2 text-base">{{ i+1 }}</td>
                                    <td className="border dark:border-gray-800 text-center px-4 py-2 text-base">{{ car.car_type}}</td>
                                    <td className="border dark:border-gray-800 text-center px-4 py-2 text-base">{{ car.year}}</td>
                                    <td className="border dark:border-gray-800 text-center px-4 py-2 text-base">{{ car.car_color }}</td>
                                    <td className="border dark:border-gray-800 text-center px-4 py-2 text-base">{{ car.vin }}</td>
                                    <td className="border dark:border-gray-800 text-center px-4 py-2 text-base">{{ car.car_number }}</td> 
                                    <td className="border dark:border-gray-800 text-center px-4 py-2 text-base">{{ car.dinar_s  }}</td>
                                    <td className="border dark:border-gray-800 text-center px-4 py-2 text-base">{{ car.dolar_price_s}}</td>
                                    <td className="border dark:border-gray-800 text-center px-4 py-2 text-base print:hidden">{{ ((car.dinar_s / car.dolar_price_s||0)?.toFixed(0)) }}</td> 
                                    <td className="border dark:border-gray-800 text-center px-4 py-2 text-base print:hidden">{{ car.note }}</td>
                                    <td className="border dark:border-gray-800 text-center px-4 py-2 text-base">{{ car.shipping_dolar_s}}</td>
                                    <td className="border dark:border-gray-800 text-center px-4 py-2 text-base">{{ car.coc_dolar_s  }}</td>
                                    <td className="border dark:border-gray-800 text-center px-4 py-2 text-base">{{ car.checkout_s}}</td>
                                    <td className="border dark:border-gray-800 text-center px-4 py-2 text-base">{{ car.expenses}}</td>
                                    <td className="border dark:border-gray-800 text-center px-4 py-2 text-base">{{ (car.total_s).toFixed(0) }}</td>
                                    <td className="border dark:border-gray-800 text-center px-4 py-2 text-base">{{ car.paid}}</td>
                                    <td className="border dark:border-gray-800 text-center px-4 py-2 text-base">{{ car.date  }}</td>
                                     <td className="border dark:border-gray-800 text-start px-2 py-2 print:hidden">
                                    <button
                                      tabIndex="1"
                                      
                                      class="px-2 py-1 text-base text-white mx-1 bg-slate-500 rounded"
                                      @click="openModalEditCars(car)"
                                    >
                                      {{ $t('edit') }}
                                    </button>
                                    <button
                                      tabIndex="1"
                                      class="px-2 py-1 text-base text-white mx-1 bg-orange-500 rounded"
                                      @click="openModalDelCar(car)"
                                    >
                                      {{ $t('delete') }}
                                    </button>
                                    <button
                                      v-if="car.total_s != car.paid"
                                      tabIndex="1"
                                      class="px-2 py-1 text-base text-white mx-1 bg-green-500 rounded"
                                      @click="openAddCarPayment(car)"
                                    >
                                      {{ $t('complet_pay') }}
                                    </button>
                                    <!-- 
                                    <button
                                      tabIndex="1"
                                      class="px-4 py-1 text-base text-white mx-1 bg-purple-500 rounded"
                                      v-if="car.results == 0"
                                      @click="openSaleCar(car)"
                                    >
                                      {{ $t('sell') }}
                                    </button>
                                    <button
                                      tabIndex="1"
                                      class="px-2 py-1 text-base text-white mx-1 bg-blue-600 rounded"
                                      @click="openAddExpenses(car)"
                                    >
                                      {{ $t('expenses') }}
                                    </button>
                                    <button
                                      tabIndex="1"
                                      class="px-2 py-1 text-base text-white mx-1 bg-green-500 rounded"
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
                :data="laravelData"
                @pagination-change-page="getResults"
                :limit ="2"
              />
            </div>
        </div>
      </div>
    </div>
    <div class="max-w-7xl mx-auto  px-4 sm:px-6 lg:px-8 dark:text-gray-400 hidden print:block">
                    <div class="flex flex-row">
                      <div class="basis-1/2">
                            توقيع صاحب الحساب
                            <br>
                            {{ laravelData.client?.name }}
                        </div>
                        <div class="basis-1/2  text-center">
                           توقيع قسم المحاسبة
                        </div>
                        <div class="basis-1/2 text-end">
                            توقيع المدير
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
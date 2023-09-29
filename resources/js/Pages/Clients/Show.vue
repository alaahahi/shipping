<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Modal from "@/Components/Modal.vue";
import { Head, Link, useForm } from "@inertiajs/inertia-vue3";
import { ref } from "vue";
import { TailwindPagination } from "laravel-vue-pagination";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import axios from "axios";
import ModalDelCar from "@/Components/ModalDelCar.vue";
import ModalEditCars from "@/Components/ModalEditCar_S.vue";
import ModalAddCarPayment from "@/Components/ModalAddCarPayment.vue";
import show from "@/Components/icon/show.vue";
import pay from "@/Components/icon/pay.vue";
import trash from "@/Components/icon/trash.vue";
import edit from "@/Components/icon/edit.vue";


import { useToast } from "vue-toastification";
const toast = useToast();

const laravelData = ref({});
const isLoading = ref(0);
const from = ref(0);
const to = ref(0);
const showPaymentForm = ref(false);
let showModalEditCars = ref(false);
let showModalDelCar = ref(false);
let showModalAddCarPayment = ref(false);


const total = ref(0);
const formData = ref({});
const discount= ref(0);
const note = ref('');
const amount = ref(0);
let client_Select = ref(0);
const showReceiveBtn = ref(0);
const getResults = async (page = 1) => {
  axios
    .get(`/api/getIndexAccountsSelas?page=${page}&user_id=${props.client_id}&from=${from.value}&to=${to.value}`)
    .then((response) => {
      laravelData.value = response.data;
      client_Select.value = response.data.client.id
    })
    .catch((error) => {
      console.error(error);
    });
};
const getResultsSelect = async (page = 1) => {

  axios
    .get(`/api/getIndexAccountsSelas?page=${page}&user_id=${client_Select.value}&from=${from.value}&to=${to.value}`)
    .then((response) => {
      laravelData.value = response.data;
      client_Select = response.data.client.id


    })
    .catch((error) => {
      console.error(error);
    });
};
getResults();
const props = defineProps({
  url: String,
  clients: Array,
  client_id: String,
  client: Object,
});

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
  showModalEditCars.value = true;
}

function openAddCarPayment(form = {}) {
  formData.value = form;
  showModalAddCarPayment.value = true;
}
function confirmDelCar(V) {
  axios
    .post("/api/DelCar", V)
    .then((response) => {
      showModalDelCar.value = false;
      window.location.reload();
    })
    .catch((error) => {
      console.error(error);
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

    })
    .catch((error) => {
      
       toast.error("لم التعديل بنجاح", {
        timeout: 2000,
        position: "bottom-right",
        rtl: true,
      });
    });
}
function confirmAddPayment(V) {
  axios
    .get(
      `/api/addPaymentCar?car_id=${V.id}&discount=${V.discount??0}&amount=${V.amountPayment ?? 0}&note=${
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

      let transaction=response.data

      window.open(`/api/getIndexAccountsSelas?user_id=${props.client_Select}&print=2&transactions_id=${transaction.id}`, '_blank');
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
}
function hideAddPaymentTotal(){
  showPaymentForm.value = false;
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
    <div class="py-4" v-if="$page.props.auth.user.type_id==1">
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
                <InputLabel for="totalAmount" :value="$t('phoneNumber')" />
                <TextInput
                  id="totalAmount"
                  type="text"
                  class="mt-1 block w-full"
                  :value="laravelData.client?.phone"
                  disabled
                />
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
                :href="`/api/getIndexAccountsSelas?user_id=${client_Select}&from=${from}&to=${to}&print=1`"
                target="_blank"
                class="px-6 mb-12 py-2 mt-1 font-bold text-white bg-orange-500 rounded block text-center"
                style="width: 100%"
              >
                <span>طباعة</span>
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
              <InputLabel
                for="car_total_complete"
                value="مجموع السيارات مكتمل"
              />
              <TextInput
                id="car_total_complete"
                type="text"
                class="mt-1 block w-full"
                :value="laravelData.car_total_complete"
                disabled
              />
            </div>
            <div className="mb-4  mr-5">
              <InputLabel
                for="car_total_unpaid"
                value="مجموع السيارات غير مدفوع"
              />
              <TextInput
                id="car_total_unpaid"
                type="text"
                class="mt-1 block w-full"
                :value="laravelData.car_total_unpaid"
                disabled
              />
            </div>
            <div className="mb-4  mr-5">
              <InputLabel
                for="car_total_uncomplete"
                value=" مجموع السيارات مدفوع وغير مكمل"
              />
              <TextInput
                id="car_total_uncomplete"
                type="text"
                class="mt-1 block w-full"
                :value="laravelData.car_total_uncomplete"
                disabled
              />
            </div>

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
              <InputLabel for="cars_discount" value="مجموع الخصومات بالدولار" />
              <TextInput
                id="cars_discount"
                type="text"
                class="mt-1 block w-full"
                :value="laravelData?.cars_discount"
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
            <div className="mb-4  mr-5 print:hidden"   v-if="laravelData?.cars_need_paid">
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
          </div>
          <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2 lg:gap-1" v-if="showPaymentForm">
            <div className="mb-4  mr-5">
              <InputLabel
                for="percentage"
                value=" المبلغ بالدولار المراد دفعه"
              />
              <TextInput
                id="percentage"
                type="number"
                class="mt-1 block w-full"
                v-model="amount"
              />
            </div>
            <div className="mb-4  mr-5">
              <InputLabel
                for="discount"
                value="الخصم"
              />
              <TextInput
                id="discount"
                type="number"
                class="mt-1 block w-full"
                v-model="discount"
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
                <span v-if="!isLoading">دفع</span>
                <span v-else>جاري الحفظ...</span>
              </button>
            </div>
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
                      {{ $t("total") }}
                    </th>
                    <th scope="col" class="px-1 py-2 text-base">
                      {{ $t("paid") }}
                    </th>
                    <th scope="col" class="px-1 py-2 text-base">
                      {{ $t("discount") }}
                    </th>
                    <th scope="col" class="px-1 py-2 text-base">
                      {{ $t("date") }}
                    </th>

                    <th
                      scope="col"
                      class="px-1 py-2 text-base print:hidden"
                      style="width:150px"
                    >
                      {{ $t("execute") }}
                    </th>
                  </tr>
                </thead>
                <tbody>
                  <tr
                    v-for="(car, i) in laravelData.data"
                    :key="car.id"
                    :class="
                      car.results == 0
                        ? ''
                        : car.results == 1
                        ? 'bg-red-100 dark:bg-red-900'
                        : 'bg-green-100 dark:bg-green-900'
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
                      {{ car.car_type }}
                    </td>
                    <td
                      className="border dark:border-gray-800 text-center px-2 py-1"
                    >
                      {{ car.year }}
                    </td>
                    <td
                      className="border dark:border-gray-800 text-center px-2 py-1"
                    >
                      {{ car.car_color }}
                    </td>
                    <td
                      className="border dark:border-gray-800 text-center px-2 py-1"
                    >
                      {{ car.vin }}
                    </td>
                    <td
                      className="border dark:border-gray-800 text-center px-2 py-1"
                    >
                      {{ car.car_number }}
                    </td>
                    <td
                      className="border dark:border-gray-800 text-center px-2 py-1"
                    >
                      {{ car.dinar_s }}
                    </td>
                    <td
                      className="border dark:border-gray-800 text-center px-2 py-1"
                    >
                      {{ car.dolar_price_s }}
                    </td>
                    <td
                      className="border dark:border-gray-800 text-center px-2 py-1 print:hidden"
                    >
                      {{ ((car.dinar_s/car.dolar_price_s)*100)?.toFixed(0)||0 }}
                    </td>
                    <td
                      className="border dark:border-gray-800 text-center px-2 py-1 print:hidden"
                    >
                      {{ car.note }}
                    </td>
                    <td
                      className="border dark:border-gray-800 text-center px-2 py-1"
                    >
                      {{ car.shipping_dolar_s }}
                    </td>
                    <td
                      className="border dark:border-gray-800 text-center px-2 py-1"
                    >
                      {{ car.coc_dolar_s }}
                    </td>
                    <td
                      className="border dark:border-gray-800 text-center px-2 py-1"
                    >
                      {{ car.checkout_s }}
                    </td>
                    <td
                      className="border dark:border-gray-800 text-center px-2 py-1"
                    >
                      {{ car.expenses }}
                    </td>
                    <td
                      className="border dark:border-gray-800 text-center px-2 py-1"
                    >
                      {{ car.total_s.toFixed(0) }}
                    </td>
                    <td
                      className="border dark:border-gray-800 text-center px-2 py-1"
                    >
                      {{ car.paid }}
                    </td>
                    <td className="border dark:border-gray-800 text-center px-1 py-2 ">{{ car.discount}}</td>

                    <td
                      className="border dark:border-gray-800 text-center px-2 py-1"
                    >
                      {{ car.date }}
                    </td>
                    <td
                      className="border dark:border-gray-800 text-start px-2 py-1 print:hidden"
                    >
    
                      <button
                        tabIndex="1"
                        
                        class="px-1 py-1  text-white mx-1 bg-slate-500 rounded"
                        @click="openModalEditCars(car)"
                      >
                        <edit />
                      </button>
                      <button
                        tabIndex="1"
                        
                        class="px-1 py-1  text-white mx-1 bg-orange-500 rounded"
                        @click="openModalDelCar(car)"
                      >
                        <trash />
                      </button>
                      <button
                        v-if="car.total_s != (car.paid+ car.discount)"
                        tabIndex="1"
                        class="px-1 py-1  text-white mx-1 bg-green-500 rounded"
                        @click="openAddCarPayment(car)"
                      >
                        <pay />
                      </button>
                                       <!-- 
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
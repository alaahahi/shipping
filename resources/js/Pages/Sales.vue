<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/inertia-vue3";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import { Link } from "@inertiajs/inertia-vue3";
import show from "@/Components/icon/show.vue";
import pay from "@/Components/icon/pay.vue";
import trash from "@/Components/icon/trash.vue";
import edit from "@/Components/icon/edit.vue";

import ModalAddCarPayment from "@/Components/ModalAddCarPayment.vue";
import ModalDelCar from "@/Components/ModalDelCar.vue";
import ModalEditCars from "@/Components/ModalEditCar_S.vue";
import ModalBulkEditCarSales from "@/Components/ModalBulkEditCarSales.vue";
import InfiniteLoading from "v3-infinite-loading";
import "v3-infinite-loading/lib/style.css";
import debounce from "lodash/debounce";
import { useToast } from "vue-toastification";
import axios from "axios";
import { ref } from "vue";
import { useI18n } from "vue-i18n";
const { t } = useI18n();
const props = defineProps({
  client: Array,
  config: [Array, Object] // يمكن أن يكون array أو object
});

const toast = useToast();
let showModal = ref(false);
let showModalCar = ref(false);
let showModalCarSale = ref(false);
let showModalAddCarPayment = ref(false);
let showModalEditCars = ref(false);
let showModalBulkEdit = ref(false);
let showModalDelCar = ref(false);
let mainAccount = ref(0);
let allCars = ref(0);
const selectedCarIds = ref([]);
const bulkFormData = ref({});

function openModalEditCars(form = {}) {
  formData.value = form;
  if (formData.value.dinar_s == 0) {
    formData.value.dinar_s = formData.value.dinar;
  }
  if (formData.value.expenses_s == 0) {
    formData.value.expenses_s = formData.value.expenses;
  }
  // التعامل مع config كـ object أو array
  const configData = Array.isArray(props.config) && props.config.length > 0 
    ? props.config[0] 
    : (props.config || {});
  
  if (formData.value.dolar_price_s == 0) {
    formData.value.dolar_price_s = configData.dolar_price || 0;
  }
  if (formData.value.shipping_dolar_s == 0) {
    formData.value.shipping_dolar_s = configData.shipping_dolar || 0;
  }
  if (formData.value.coc_dolar_s == 0) {
    formData.value.coc_dolar_s = configData.coc_dolar || 0;
  }
  if (formData.value.checkout_s == 0) {
    formData.value.checkout_s = configData.checkout || 0;
  }
  if (formData.value.land_shipping_s == 0) {
    formData.value.land_shipping_s = configData.land_shipping_s || 0;
  }  

  showModalEditCars.value = true;
}
function openModalDelCar(form = {}) {
  formData.value = form;
  showModalDelCar.value = true;
}

function openBulkEdit() {
  if (!selectedCarIds.value.length) {
    toast.warning("الرجاء اختيار سيارة واحدة على الأقل", {
      timeout: 2000,
      position: "bottom-right",
      rtl: true,
    });
    return;
  }
  const firstId = selectedCarIds.value[0];
  const firstCar = car.value.find((item) => item.id === firstId);
  if (!firstCar) {
    return;
  }
  const initial = { ...firstCar };
  if (!initial.dinar_s || initial.dinar_s === 0) {
    initial.dinar_s = initial.dinar;
  }
  if (!initial.expenses_s || initial.expenses_s === 0) {
    initial.expenses_s = initial.expenses;
  }
  // التعامل مع config كـ object أو array
  const configData = Array.isArray(props.config) && props.config.length > 0 
    ? props.config[0] 
    : (props.config || {});
  
  if (!initial.dolar_price_s || initial.dolar_price_s === 0) {
    initial.dolar_price_s = configData.dolar_price ?? initial.dolar_price_s;
  }
  if (!initial.shipping_dolar_s || initial.shipping_dolar_s === 0) {
    initial.shipping_dolar_s = configData.shipping_dolar ?? initial.shipping_dolar_s;
  }
  if (!initial.coc_dolar_s || initial.coc_dolar_s === 0) {
    initial.coc_dolar_s = configData.coc_dolar ?? initial.coc_dolar_s;
  }
  if (!initial.checkout_s || initial.checkout_s === 0) {
    initial.checkout_s = configData.checkout ?? initial.checkout_s;
  }
  if (!initial.land_shipping_s || initial.land_shipping_s === 0) {
    initial.land_shipping_s = configData.land_shipping_s ?? initial.land_shipping_s;
  }
  delete initial.car_type;
  delete initial.car_color;
  delete initial.year;
  delete initial.dinar;
  delete initial.dinar_s;
  delete initial.car_owner;
  bulkFormData.value = initial;
  showModalBulkEdit.value = true;
}

function openAddCarPayment(form = {}) {
  formData.value = form;
  formData.value.notePayment = " بيد ";
  showModalAddCarPayment.value = true;
}
const formData = ref({});
const car = ref([]);
let from = ref("");
let to = ref("");

let resetData = ref(false);
let user_id = 0;
let page = 1;
let q = "";
const refresh = () => {
  page = 0;
  car.value.length = 0;
  resetData.value = !resetData.value;
  selectedCarIds.value = [];
};
const getResultsCar = async ($state) => {
  console.log($state);
  try {
    const response = await axios.get(`/getIndexCar`, {
      params: {
        limit: 100,
        page: page,
        q: q,
        user_id: user_id,
        from: from.value,
        to: to.value,
        get_image: 1,
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

const getcountTotalInfo = async () => {
  axios
    .get("/api/totalInfo")
    .then((response) => {
      mainAccount.value = response.data.data.mainAccount;
      allCars.value = response.data.data.allCars;
    })
    .catch((error) => {
      console.error(error);
    });
};
getcountTotalInfo();

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

      getcountTotalInfo();
      refresh();
    })
    .catch((error) => {
      showModal.value = false;

      toast.error("لم التعديل بنجاح", {
        timeout: 2000,
        position: "bottom-right",
        rtl: true,
      });
    });
}

function confirmBulkUpdate(V) {
  const payload = {
    ...V,
    car_ids: selectedCarIds.value,
  };
  delete payload.car_type;
  delete payload.car_color;
  delete payload.year;
  delete payload.dinar;
  delete payload.dinar_s;
  delete payload.vin;
  delete payload.car_number;
  delete payload.car_owner;
  axios
    .post("/api/bulkUpdateCarsS", payload)
    .then(() => {
      toast.success("تم التعديل الجماعي بنجاح", {
        timeout: 2000,
        position: "bottom-right",
        rtl: true,
      });
      showModalBulkEdit.value = false;
      selectedCarIds.value = [];
      getcountTotalInfo();
      refresh();
    })
    .catch((error) => {
      console.error(error);
      toast.error("فشل التعديل الجماعي", {
        timeout: 2000,
        position: "bottom-right",
        rtl: true,
      });
    });
}

function confirmDelCar(V) {
  axios
    .post("/api/DelCar", V)
    .then((response) => {
      showModalDelCar.value = false;
      toast.success("تم التعديل بنجاح وخصم المبلغ من دين الزبون", {
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

function confirmAddPayment(V) {
  axios
    .get(
      `/api/addPaymentCar?car_id=${V.id}&discount=${
        V.discountPayment ?? 0
      }&amount=${V.amountPayment ?? 0}&note=${V.notePayment ?? ""}`
    )
    .then((response) => {
      refresh();

      showModalAddCarPayment.value = false;
      toast.success(" تم دفع مبلغ دولار " + V.amountPayment + " بنجاح ", {
        timeout: 3000,
        position: "bottom-right",
        rtl: true,
      });
      let transaction = response.data;
      window.open(
        `/api/getIndexAccountsSelas?user_id=${V.client.id}&print=2&transactions_id=${transaction.id}`,
        "_blank"
      );
    })
    .catch((error) => {
      showModal.value = false;

      toast.error("لم التعديل بنجاح", {
        timeout: 2000,
        position: "bottom-right",
        rtl: true,
      });
    });
}

const debouncedGetResultsCar = debounce(refresh, 500); // Adjust the debounce delay (in milliseconds) as needed
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

  <ModalEditCars
    :formData="formData"
    :show="showModalEditCars ? true : false"
    :client="client"
    :systemConfig="config"
    @a="confirmUpdateCar($event)"
    @close="showModalEditCars = false"
  >
    <template #header> </template>
  </ModalEditCars>

  <ModalBulkEditCarSales
    :show="showModalBulkEdit ? true : false"
    :baseData="bulkFormData"
    :selected-count="selectedCarIds.length"
    @confirm="confirmBulkUpdate"
    @close="showModalBulkEdit = false"
  >
    <template #header></template>
  </ModalBulkEditCarSales>

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
      <h2 class="mb-5 dark:text-white text-center">
        هل متأكد من حذف السيارة ؟
      </h2>
    </template>
  </ModalDelCar>

  <AuthenticatedLayout>
    <div
      class="py-2"
      v-if="
        $page.props.auth.user.type_id == 1 || $page.props.auth.user.type_id == 6
      "
    >
      <div class="max-w-9xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm">
          <div class="p-6 dark:bg-gray-900">
            <div class="flex flex-col">
              <div
                class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-7 gap-2 lg:gap-1"
              >
                <div>
                  <InputLabel class="mb-1" for="pay" value="بحث" />
                  <form class="flex items-center max-w-5xl">
                    <label
                      class="dark:text-gray-200"
                      for="simple-search"
                    ></label>
                    <div class="relative w-full">
                      <div
                        class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none"
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
                        v-model="q"
                        @input="debouncedGetResultsCar"
                        type="text"
                        id="simple-search"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                        placeholder="رقم الشانصى - رقم السيارة - اسم التاجر - اسم السيارة"
                        required
                      />
                    </div>
                  </form>
                </div>

                <div>
                  <InputLabel class="mb-1" for="pay" value="اختيار تاجر" />
                  <select
                    @change="refresh()"
                    v-model="user_id"
                    id="default"
                    class="pr-8 bg-gray-50 border border-gray-300 text-gray-900 mb-6 text-sm rounded-lg focus:ring-red-500 focus:border-red-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-red-500 dark:focus:border-red-500"
                  >
                    <option value="0" disabled>
                      {{ $t("selectCustomer") }}
                    </option>
                    <option value="">{{ $t("allOwners") }}</option>
                    <option
                      v-for="(user, index) in client"
                      :key="index"
                      :value="user.id"
                    >
                      {{ user.name }}
                    </option>
                  </select>
                </div>
                <div class="px-2">
                  <div className="mb-4">
                    <InputLabel for="from" :value="$t('from_date')" />
                    <TextInput
                      id="from"
                      type="date"
                      class="mt-1 block w-full"
                      v-model="from"
                    />
                  </div>
                </div>
                <div class="px-2">
                  <div className="mb-4">
                    <InputLabel for="to" :value="$t('to_date')" />
                    <TextInput
                      id="to"
                      type="date"
                      class="mt-1 block w-full"
                      v-model="to"
                    />
                  </div>
                </div>
                <div className="mb-4  mr-2 print:hidden">
                  <InputLabel for="pay" value="فلترة" />
                  <button
                    @click.prevent="refresh()"
                    class="px-6 mb-12 py-2 mt-1 font-bold text-white bg-gray-500 rounded"
                    style="width: 100%"
                  >
                    <span>فلترة</span>
                  </button>
                </div>

                <div className="mb-4  mr-5 print:hidden">
                  <InputLabel for="pay" value="طباعة" />
                  <a
                    :href="`/api/getIndexCar?user_id=${user_id}&from=${from}&to=${to}&print=1&printExcel=1`"
                    target="_blank"
                    class="px-6 mb-12 py-2 mt-1 font-bold text-white bg-green-500 rounded block text-center"
                    style="width: 100%"
                  >
                    <span>Excel</span>
                  </a>
                </div>
                <div className="mb-4  mr-5 print:hidden" v-if="user_id">
                  <InputLabel for="pay" value="طباعة" />
                  <a
                    target="_blank"
                    :href="`api/getIndexAccountsSelas?user_id=${user_id}&from=${from}&to=${to}&print=1&showComplatedCars=0`"
                    class="px-6 mb-12 py-2 mt-1 font-bold text-white bg-orange-500 rounded d-block"
                    style="width: 100%; display: block; text-align: center"
                  >
                    <span>طباعة</span>
                  </a>
                </div>
                <div className="mb-4 mr-5 print:hidden text-center">
                  <InputLabel for="bulk" value="تعديل جماعي" />
                  <button
                    id="bulk"
                    type="button"
                    @click="openBulkEdit()"
                    :disabled="!selectedCarIds.length"
                    :class="[
                      'px-6 mb-12 py-2 mt-1 font-bold text-white rounded w-full',
                      selectedCarIds.length ? 'bg-blue-500' : 'bg-gray-400 cursor-not-allowed'
                    ]"
                  >
                    تنفيذ
                  </button>
                </div>
              </div>
              <div>
                <div></div>
                <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                  <table
                    class="w-full text-sm text-right text-gray-500 dark:text-gray-200 dark:text-gray-400 text-center"
                  >
                    <thead
                      class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400 text-center"
                    >
                      <tr>
                        <th scope="col" class="px-1 py-3 text-base">
                          تحديد
                        </th>
                        <th scope="col" class="px-1 py-3 text-base">
                          {{ $t("car_owner") }}
                        </th>
                        <th scope="col" class="px-1 py-3 text-base">
                          {{ $t("car_type") }}
                        </th>
                        <th scope="col" class="px-1 py-3 text-base">
                          {{ $t("year") }}
                        </th>
                        <th scope="col" class="px-1 py-3 text-base">
                          {{ $t("color") }}
                        </th>
                        <th scope="col" class="px-1 py-3 text-base">
                          {{ $t("vin") }}
                        </th>
                        <th scope="col" class="px-1 py-3 text-base">
                          {{ $t("car_number") }}
                        </th>
                        <th scope="col" class="px-1 py-3 text-base">
                          {{ $t("dinar") }}
                        </th>
                        <th scope="col" class="px-1 py-3 text-base">
                          {{ $t("dolar_price") }}
                        </th>
                        <th scope="col" class="px-1 py-3 text-base">
                          {{ $t("dolar_custom") }}
                        </th>
                        <th scope="col" class="px-1 py-3 text-base">
                          {{ $t("note") }}
                        </th>
                        <th scope="col" class="px-1 py-3 text-base">
                          {{ $t("shipping_dolar") }}
                        </th>
                        <th scope="col" class="px-1 py-3 text-base">
                          {{ $t("coc_dolar") }}
                        </th>
                        <th scope="col" class="px-1 py-3 text-base">
                          {{ $t("checkout") }}
                        </th>
                        <th scope="col" class="px-1 py-3 text-base">
                          {{ $t("expenses") }}
                        </th>
                        <th scope="col" class="px-1 py-3 text-base">
                            نقل بري
                        </th>
                       
                        <th scope="col" class="px-1 py-3 text-base">
                          {{ $t("total") }}
                        </th>
                        <th scope="col" class="px-1 py-3 text-base">
                          {{ $t("paid") }}
                        </th>
                        <th scope="col" class="px-1 py-3 text-base">
                          {{ $t("discount") }}
                        </th>
                        <th scope="col" class="px-1 py-3 text-base">
                          {{ $t("date") }}
                        </th>

                        <th
                          scope="col"
                          class="px-1 py-3 text-base"
                          style="width: 180px"
                        >
                          {{ $t("execute") }}
                        </th>
                        <th class="px-1 py-3 text-base">تخزين</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr
                        v-for="car in car"
                        :key="car.id"
                        :class="
                          car.results == 0
                            ? ''
                            : car.results == 1
                            ? 'bg-red-100 dark:bg-red-900'
                            : 'bg-green-100 dark:bg-green-900'
                        "
                        class="bg-white border-b dark:bg-gray-900 dark:border-gray-900 hover:bg-gray-50 dark:hover:bg-gray-600"
                      >
                        <td class="border dark:border-gray-800 text-center px-1 py-2">
                          <input
                            type="checkbox"
                            :value="car.id"
                            v-model="selectedCarIds"
                            class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                          />
                        </td>
                        <td
                          className="border dark:border-gray-800 text-center  dark:text-gray-200 text-black px-1 py-2 "
                          style="font-weight: bold; font-size: 16px"
                        >
                          {{ car.client?.name }}
                        </td>
                        <td
                          className="border dark:border-gray-800 text-center px-1 py-2 "
                        >
                          {{ car.car_type }}
                        </td>
                        <td
                          className="border dark:border-gray-800 text-center px-1 py-2 "
                        >
                          {{ car.year }}
                        </td>
                        <td
                          className="border dark:border-gray-800 text-center px-1 py-2 "
                        >
                          {{ car.car_color }}
                        </td>
                        <td
                          className="border dark:border-gray-800 text-center px-1 py-2 "
                        >
                          {{ car.vin }}
                        </td>
                        <td
                          className="border dark:border-gray-800 text-center px-1 py-2 "
                        >
                          {{ car.car_number }}
                        </td>
                        <td
                          className="border dark:border-gray-800 text-center px-1 py-2 "
                        >
                          {{ car.dinar_s }}
                        </td>
                        <td
                          className="border dark:border-gray-800 text-center px-1 py-2 "
                        >
                          {{ car.dolar_price_s }}
                        </td>
                        <td
                          className="border dark:border-gray-800 text-center px-1 py-2 "
                        >
                          {{
                            ((car.dinar_s / car.dolar_price_s) * 100)?.toFixed(
                              0
                            ) || 0
                          }}
                        </td>
                        <td
                          className="border dark:border-gray-800 text-center px-1 py-2 "
                        >
                          {{ car.note }}
                        </td>
                        <td
                          className="border dark:border-gray-800 text-center px-1 py-2 "
                        >
                          {{ car.shipping_dolar_s }}
                        </td>
                        <td
                          className="border dark:border-gray-800 text-center px-1 py-2 "
                        >
                          {{ car.coc_dolar_s }}
                        </td>
                        <td
                          className="border dark:border-gray-800 text-center px-1 py-2 "
                        >
                          {{ car.checkout_s }}
                        </td>
                        <td
                          className="border dark:border-gray-800 text-center px-1 py-2 "
                        >
                          {{ car.expenses_s }}
                        </td>
                        <td
                          className="border dark:border-gray-800 text-center px-1 py-2 "
                        >
                          {{ car.land_shipping_s }}
                        </td>
                        
                        <td
                          className="border dark:border-gray-800 text-center px-1 py-2 "
                        >
                          {{ car.total_s.toFixed(0) }}
                        </td>
                        <td
                          className="border dark:border-gray-800 text-center px-1 py-2 "
                        >
                          {{ car.paid }}
                        </td>
                        <td
                          className="border dark:border-gray-800 text-center px-1 py-2 "
                        >
                          {{ car.discount }}
                        </td>

                        <td
                          className="border dark:border-gray-800 text-center px-1 py-2 "
                        >
                          {{ car.date }}
                        </td>
                        <td
                          className="border dark:border-gray-800 text-start px-1 py-2"
                        >
                          <button
                            tabIndex="1"
                            class="px-1 py-1 text-white mx-1 bg-slate-500 rounded"
                            @click="openModalEditCars(car)"
                          >
                            <edit />
                          </button>
                          <button
                            tabIndex="1"
                            class="px-1 py-1 text-white mx-1 bg-orange-500 rounded"
                            @click="openModalDelCar(car)"
                          >
                            <trash />
                          </button>
                          <button
                            v-if="car.total_s != car.paid + car.discount"
                            tabIndex="1"
                            class="px-1 py-1 text-white mx-1 bg-green-500 rounded"
                            @click="openAddCarPayment(car)"
                          >
                            <pay />
                          </button>
                          <Link
                            style="display: inline-flex"
                            className="px-1 py-1  text-white mx-1 bg-blue-500 rounded d-inline-block"
                            :href="route('showClients', car.client?.id)"
                          >
                            <show />
                          </Link>
                        </td>
                        <td  className="border dark:border-gray-800 text-center px-1 py-2 ">
                          <a
                            v-for="(image, index) in car.car_images"
                            :key="index"
                            :href="getDownloadUrl(image.name)"
                            style="cursor: pointer"
                            target="_blank">
                            <img
                              :src="getImageUrl(image.name)"
                              alt=""
                              class="px-1"
                              style="
                                max-width: 100px;
                                max-height: 50px;
                                display: inline;
                              "
                            />
                          </a>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
              <div class="spaner">
                <InfiniteLoading
                  :car="car"
                  @infinite="getResultsCar"
                  :identifier="resetData"
                />
              </div>

              <div>
                <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-4 lg:grid-cols-5 xl:grid-cols-7">
                  <div  class="flex items-start rounded-xl dark:bg-gray-600 dark:text-gray-300 bg-white p-4 shadow-lg">
                    <div class="flex h-12 w-12 items-center justify-center rounded-full border border-orange-100 bg-orange-50">
                      <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-6 w-6 text-orange-400"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="2"
                      >
                        <path
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                        />
                      </svg>
                    </div>
                    <div class="mr-4">
                      <h2 class="font-semibold">{{ $t("capital") }}</h2>
                      <p class="mt-2 text-sm text-gray-500 dark:text-gray-200">
                        {{ mainAccount }}
                      </p>
                    </div>
                  </div>
                  <div class="flex items-start rounded-xl dark:bg-gray-600 dark:text-gray-300 bg-white p-4 shadow-lg">
                    <div class="flex h-12 w-12 items-center justify-center rounded-full border border-red-100 bg-red-50">
                      <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-6 w-6 text-red-400"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="2"
                      >
                        <path
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"
                        />
                      </svg>
                    </div>
                    <div class="mr-4">
                      <h2 class="font-semibold">{{ $t("all_cars") }}</h2>
                      <p class="mt-2 text-sm text-gray-500 dark:text-gray-200">
                        {{ allCars }}
                      </p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div>
    </div>
  </AuthenticatedLayout>
</template>
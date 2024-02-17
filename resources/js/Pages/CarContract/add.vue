<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/inertia-vue3";
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

import InfiniteLoading from "v3-infinite-loading";
import "v3-infinite-loading/lib/style.css";
import debounce from "lodash/debounce";
const { t } = useI18n();
const props = defineProps({
  client: Array,
});
const formData = ref({});
const toast = useToast();
let searchTerm = ref("");
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
const form = ref({
  name_seller: "",
  phone_seller: "",
  address_seller: "",
  name_buyer: "",
  phone_buyer: "",
  address_buyer: "",
  tex_seller: 0,
  tex_seller_dinar:0,
  tex_buyer: 0,
  tex_buyer_dinar: 0,
  vin: "",
  car_name: "",
  modal: "",
  color: "",
  size: "",
  note: "",
  system_note: "",
  car_price: 0,
  car_paid: 0,
  tex_seller_paid: 0,
  tex_seller_dinar_paid: 0,
  tex_buyer_paid: 0,
  tex_buyer_dinar_paid: 0,
});


const isLoading = ref(false);

const submit = (V) => {
  isLoading.value = true;
  axios.post('/api/addCarContract',V)
  .then(response => {
    isLoading.value = false;
    profileAdded.value=true
    form.value = {
    name_seller: "",
    phone_seller: "",
    address_seller: "",
    name_buyer: "",
    phone_buyer: "",
    address_buyer: "",
    tex_seller: 0,
    tex_seller_dinar:0,
    tex_buyer: 0,
    tex_buyer_dinar: 0,
    vin: "",
    car_name: "",
    modal: "",
    color: "",
    size: "",
    note: "",
    system_note: "",
    car_price: 0,
    car_paid: 0,
    tex_seller_paid: 0,
    tex_seller_dinar_paid: 0,
    tex_buyer_paid: 0,
    tex_buyer_dinar_paid: 0,
  };

  })
  .catch(error => {
    isLoading.value = false;
    console.error(error);
  })
};
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
      <div class="flex flex-row">
        <div class="basis-1/2">
          <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
              <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                  <h2 class="text-center text-xl py-2">الطرف الاول (البائع)</h2>
                  <div className="flex flex-col">
                    <div className="mb-4">
                      <InputLabel for="name" value="الأسم"  :class="{'list-item text-red-600':!form.name_seller}" />
                      <TextInput
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.name_seller"
                       
                      />
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
                          v-model="form.tex_seller"
                        />
                      </div>
                      <div className="mb-4">
                        <InputLabel for="tex_seller_dinar" value="دلالى دينار" />
                        <TextInput
                          type="number"
                          class="mt-1"
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
                          v-model="form.tex_seller_paid"
                        />
                      </div>
                      <div className="mb-4">
                        <InputLabel for="tex_seller_dinar_paid" value="مدفوع دينار" />
                        <TextInput
                          type="number"
                          class="mt-1"
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
        </div>
        <div class="basis-1/2">
          <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
              <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                  <h2 class="text-center text-xl py-2">الطرف الثاني (المشتري)</h2>

                  <div className="flex flex-col">
                    <div className="mb-4">
  
                      <InputLabel for="name_buyer" value="الأسم" :class="{'list-item text-red-600':!form.name_buyer}" />

                      <TextInput
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.name_buyer"
                      />
                     
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
                          v-model="form.tex_buyer"
                        />
                      </div>
                      <div className="mb-4">
                        <InputLabel for="tex_buyer_dinar" value="دلالى دينار" />
                        <TextInput
                          type="number"
                          class="mt-1"
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
                          v-model="form.tex_buyer_paid"
                        />
                      </div>
                      <div className="mb-4">
                        <InputLabel for="tex_buyer_dinar_paid" value="مدفوع دينار" />
                        <TextInput
                          type="number"
                          class="mt-1"
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
      </div>
      <div class="flex">
        <div class="grow">
          <div class="pb-3">
            <div class="mx-auto mx-7">
              <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                  <div class="flex justify-center">
                    <div class="basis-1/3">
                      <div className="mb-4 mx-5">
                        <InputLabel for="vin" value="رقم الشاصى" :class="{'list-item text-red-600':!form.vin}" />
                        <TextInput
                          type="text"
                          class="mt-1 block w-full"
                          v-model="form.vin"
                        />
                      </div>
                    </div>
                    <div class="basis-1/3">
                      <div className="mb-4 mx-5">
                        <InputLabel for="car_name" value="السيارة" :class="{'list-item text-red-600':!form.car_name}" />
                        <TextInput
                          type="text"
                          class="mt-1 block w-full"
                          v-model="form.car_name"
                        />
                      </div>
                    </div>
                    <div class="basis-1/3">
                      <div className="mb-4 mx-5">
                        <InputLabel for="modal" value="موديل" />
                        <TextInput
                          type="number"
                          class="mt-1 block w-full"
                          v-model="form.modal"
                        />
                      </div>
                    </div>
                    <div class="basis-1/3">
                      <div className="mb-4 mx-5">
                        <InputLabel for="color" value="اللون" />
                        <TextInput
                          type="text"
                          class="mt-1 block w-full"
                          v-model="form.color"
                        />
                      </div>
                    </div>
                    <div class="basis-1/3">
                      <div className="mb-4 mx-5">
                        <InputLabel for="size" value="قماره" />
                        <TextInput
                          type="text"
                          class="mt-1 block w-full"
                          v-model="form.size"
                        />
                      </div>
                    </div>
                    <div class="basis-1/3">
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
                    <div class="basis-1/3">
                      <div className="mb-4 mx-5">
                        <InputLabel
                          for="car_price"
                          value="سعر السيارة بالدولار"
                        />
                        <TextInput
                          type="number"
                          class="mt-1 block w-full"
                          v-model="form.car_price"
                        />
                      </div>
                    </div>
                    <div class="basis-1/3">
                      <div className="mb-4 mx-5">
                        <InputLabel
                          for="car_paid"
                          value="المدفوع بالدولار"
                        />
                        <TextInput
                          type="number"
                          class="mt-1 block w-full"
                          v-model="form.car_paid"
                        />
                      </div>
                    </div>
                    <div class="basis-1/3">
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

      <div className="flex items-center justify-center my-6 ">
        <Link
          className="px-6 mx-2 py-2 mb-12 text-white bg-gray-500 rounded-md focus:outline-none rounded"
          :href="route('formRegistration')"
        >
          العودة
        </Link>

        <button
          @click="submit(form)"
          :disabled="isLoading"
          class="px-6 mb-12 mx-2 py-2 font-bold text-white bg-rose-500 rounded"
        >
          <span v-if="!isLoading">حفظ</span>
          <span v-else>جاري الحفظ...</span>
        </button>
      </div>
    </form>
  </AuthenticatedLayout>
</template>
<style scoped>
.list-item{
  display: list-item;
}
</style>
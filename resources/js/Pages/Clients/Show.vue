<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Modal from "@/Components/Modal.vue";
import { Head, Link, useForm } from "@inertiajs/inertia-vue3";
import { Inertia } from "@inertiajs/inertia";
import { onMounted, ref, watch, computed, nextTick } from 'vue';
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
import { formatBaghdadTimestamp } from "@/utils/datetime";

import newContracts from "@/Components/icon/new.vue";

import { useToast } from "vue-toastification";
import { debounce } from "lodash";
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
let isDataLoaded = ref(false);
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

const currentClientId = computed(() => {
  if (client_Select.value && client_Select.value !== 0 && client_Select.value !== "undefined") {
    return client_Select.value;
  }
  return props.client_id;
});

let clientPhone = ref('');
let isSavingPhone = ref(false);
const allowAutoSavePhone = ref(false);

function normalizeIdentifier(value) {
  if (value === undefined || value === null) {
    return '';
  }
  return value.toString().replace(/[\s-]+/g, '').toUpperCase();
}

function extractIdentifiersFromDescription(description) {
  if (!description || typeof description !== 'string') {
    return [];
  }
  const matches = description.match(/[A-Za-z0-9]+/g) || [];
  return matches
    .map((segment) => normalizeIdentifier(segment))
    .filter((segment) => segment.length >= 3)
    .filter((segment, index, self) => segment && self.indexOf(segment) === index);
}

const highlightPalette = [
  '#F97316', // orange
  '#10B981', // emerald
  '#3B82F6', // blue
  '#EC4899', // pink
  '#F59E0B', // amber
  '#22D3EE', // cyan
  '#8B5CF6', // violet
  '#F87171', // red
  '#14B8A6', // teal
  '#A855F7', // purple
];

function getHighlightColorForPayment(payment) {
  const base = Number(payment?.id ?? 0) || payment?.description?.length || 0;
  const index = Math.abs(base) % highlightPalette.length;
  return highlightPalette[index];
}

function withAlpha(hexColor, alpha = 0.15) {
  if (!hexColor) {
    return `rgba(0,0,0,${alpha})`;
  }
  let hex = hexColor.replace('#', '');
  if (hex.length === 3) {
    hex = hex.split('').map((char) => char + char).join('');
  }
  const bigint = parseInt(hex, 16);
  const r = (bigint >> 16) & 255;
  const g = (bigint >> 8) & 255;
  const b = bigint & 255;
  return `rgba(${r}, ${g}, ${b}, ${alpha})`;
}

const saveClientPhone = debounce(async (value) => {
  if (!currentClientId.value) {
    return;
  }
  try {
    isSavingPhone.value = true;
    await axios.post("/api/updateClientPhone", {
      userId: currentClientId.value,
      phone: value
    });
    toast.success("تم تحديث رقم الهاتف بنجاح", {
      timeout: 2000,
      position: "bottom-right",
      rtl: true,
    });
  } catch (error) {
    console.error(error);
    toast.error("لم يتم تحديث رقم الهاتف", {
      timeout: 3000,
      position: "bottom-right",
      rtl: true,
    });
  } finally {
    isSavingPhone.value = false;
  }
}, 3000);

watch(clientPhone, (newVal, oldVal) => {
  if (!allowAutoSavePhone.value) {
    return;
  }
  if (newVal === oldVal) {
    return;
  }
  saveClientPhone(newVal);
});

function syncClientPhone(phone) {
  allowAutoSavePhone.value = false;
  clientPhone.value = phone ?? '';
  nextTick(() => {
    allowAutoSavePhone.value = true;
  });
}

const isClientPhoneTooLong = computed(() => {
  return clientPhone.value ? String(clientPhone.value).length > 10 : false;
});

const isFilterActive = computed(() => {
  const fromVal = from.value && from.value !== 0 && from.value !== '0' && from.value !== '';
  const toVal = to.value && to.value !== 0 && to.value !== '0' && to.value !== '';
  return Boolean(fromVal || toVal);
});

const paymentIdentifierLookup = computed(() => {
  if (!showComplatedCars.value) {
    return Object.create(null);
  }
  const lookup = Object.create(null);
  mergedData.value.forEach((item) => {
    if (item.type === 'payment' && Array.isArray(item.relatedIdentifiers) && item.relatedIdentifiers.length) {
      item.relatedIdentifiers.forEach((identifier) => {
        if (!identifier) {
          return;
        }
        if (!lookup[identifier]) {
          lookup[identifier] = [];
        }
        const color = item.highlightColor || highlightPalette[0];
        if (!lookup[identifier].includes(color)) {
          lookup[identifier].push(color);
        }
      });
    }
  });
  return lookup;
});

function getIdentifierColors(identifiers) {
  const colors = [];
  identifiers.forEach((identifier) => {
    if (!identifier) {
      return;
    }
    const list = paymentIdentifierLookup.value[identifier];
    if (Array.isArray(list)) {
      list.forEach((color) => {
        if (color && !colors.includes(color)) {
          colors.push(color);
        }
      });
    }
  });
  return colors;
}

function isCarReferencedByPayment(item) {
  if (!item || item.type !== 'car') {
    return false;
  }
  const identifiers = Array.isArray(item.normalizedIdentifiers)
    ? item.normalizedIdentifiers
    : [
        normalizeIdentifier(item?.data?.car_number),
        normalizeIdentifier(item?.data?.vin),
      ].filter(Boolean);
  return getIdentifierColors(identifiers).length > 0;
}

function paymentReferencesCars(item) {
  return Boolean(item?.type === 'payment' && item?.relatedIdentifiers?.length);
}

function getCarHighlightStyle(item) {
  if (!item || item.type !== 'car') {
    return {};
  }
  const identifiers = Array.isArray(item.normalizedIdentifiers)
    ? item.normalizedIdentifiers
    : [
        normalizeIdentifier(item?.data?.car_number),
        normalizeIdentifier(item?.data?.vin),
      ].filter(Boolean);
  const colors = getIdentifierColors(identifiers);
  if (!colors.length) {
    return {};
  }
  const color = colors[0];
  return {
    boxShadow: `inset 0 -3px 0 ${color}`,
    backgroundColor: withAlpha(color, 0.12),
  };
}

function getPaymentHighlightStyle(item) {
  if (!paymentReferencesCars(item)) {
    return {};
  }
  const color = item.highlightColor || highlightPalette[0];
  return {
    boxShadow: `inset 0 0 0 2px ${withAlpha(color, 0.35)}`,
    backgroundColor: withAlpha(color, 0.12),
  };
}

function getCarHighlightClass(item) {
  return isCarReferencedByPayment(item)
    ? 'car-highlighted'
    : '';
}

let getResults = async (page = 1, shouldCheckBalance = true) => {
  isDataLoaded.value = false;
  const userId = currentClientId.value;
  if (!userId) {
    isDataLoaded.value = true;
    return;
  }
  axios
    .get(`/api/getIndexAccountsSelas?page=${page}&user_id=${userId}&from=${from.value || ""}&to=${to.value || ""}`)
    .then((response) => {
      laravelData.value = response.data;
      client_Select.value = response.data.client.id
      syncClientPhone(response.data?.client?.phone);
      cancelEditingPaymentDescription();
      if (shouldCheckBalance && !isFilterActive.value) {
        checkClientBalance(response.data.cars_sum);
      }
      isDataLoaded.value = true;

      if (props.q && !hasHandledQueryBehavior.value) {
        const cars = Array.isArray(response.data?.data) ? response.data.data : [];
        const matchingCar = cars.find((car) => matchesCarQuery(car));
        if (matchingCar) {
          if (matchingCar.results === 2) {
            showComplatedCars.value = true;
            showPaymentsInTable.value = true;
          }
          nextTick(() => {
            scrollToHighlightedCar();
          });
        }
        hasHandledQueryBehavior.value = true;
      }
    })
    .catch((error) => {
      console.error(error);
      isDataLoaded.value = true;
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

const paymentsCount = computed(() => {
  try {
    return laravelData.value?.transactions?.filter(t => t.type === 'out' && t.amount < 0 && t.is_pay === 1).length || 0;
  } catch (error) {
    return 0;
  }
});
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
  const userId = currentClientId.value;
  if (!userId) {
    return;
  }
  axios
    .get(`/api/getIndexAccountsSelas?page=${page}&user_id=${userId}&from=${from.value || ""}&to=${to.value || ""}`)
    .then((response) => {
      laravelData.value = response.data;
      client_Select.value = response.data.client.id
      syncClientPhone(response.data?.client?.phone);
      cancelEditingPaymentDescription();
      if (!isFilterActive.value) {
        checkClientBalance(response.data.cars_sum);
      }

      if (props.q) {
        const cars = Array.isArray(response.data?.data) ? response.data.data : [];
        const matchingCar = cars.find((car) => matchesCarQuery(car));
        if (matchingCar) {
          if (matchingCar.results === 2) {
            showComplatedCars.value = true;
            showPaymentsInTable.value = true;
          }
          nextTick(() => {
            scrollToHighlightedCar();
          });
        }
      }
    })
    .catch((error) => {
      console.error(error);
    });
};

// استدعاء getResults عند تحميل الصفحة
onMounted(() => {
  getResults();
});

const hasHandledQueryBehavior = ref(false);
const shouldAutoScrollToQuery = ref(true);
const matchedRowElement = ref(null);

function matchesCarQuery(car) {
  if (!car || !props.q) {
    return false;
  }
  const query = String(props.q).trim().toUpperCase();
  if (!query) {
    return false;
  }
  const vin = car?.vin ? String(car.vin).toUpperCase() : '';
  const carNumber = car?.car_number ? String(car.car_number).toUpperCase() : '';
  const chassis = car?.chassis_number ? String(car.chassis_number).toUpperCase() : '';
  return (
    vin.startsWith(query) ||
    carNumber.startsWith(query) ||
    chassis.startsWith(query)
  );
}

function resetQueryBehaviour() {
  hasHandledQueryBehavior.value = false;
  shouldAutoScrollToQuery.value = true;
  matchedRowElement.value = null;
}

function scrollToHighlightedCar(retry = 6) {
  if (!shouldAutoScrollToQuery.value || typeof window === 'undefined' || retry <= 0) {
    return;
  }

  if (matchedRowElement.value && typeof matchedRowElement.value.scrollIntoView === 'function') {
    matchedRowElement.value.scrollIntoView({
      behavior: 'smooth',
      block: 'center'
    });
    shouldAutoScrollToQuery.value = false;
    return;
  }

  if (typeof document?.querySelector !== 'function') {
    return;
  }

  window.requestAnimationFrame(() => {
    const el = document.querySelector('[data-car-match="true"]');
    if (el) {
      matchedRowElement.value = el;
      if (typeof el.scrollIntoView === 'function') {
        el.scrollIntoView({
          behavior: 'smooth',
          block: 'center'
        });
        shouldAutoScrollToQuery.value = false;
      }
    } else if (retry > 1) {
      setTimeout(() => scrollToHighlightedCar(retry - 1), 160);
    }
  });
}

function setMatchedRowRef(el, car) {
  const isMatch = matchesCarQuery(car);
  if (!isMatch) {
    return;
  }

  if (el) {
    matchedRowElement.value = el;
  } else if (matchedRowElement.value) {
    matchedRowElement.value = null;
  }
}

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
    if (isFilterActive.value) {
      return;
    }
    const userId = currentClientId.value;
    if (!userId) {
      return;
    }
    const transactionsTotal = Number(calculateTotalFilteredAmount().totalAmount || 0);
    const currentBalance = Number(v || 0) + transactionsTotal;
    const params = new URLSearchParams({
      userId: userId,
      currentBalance: currentBalance,
      from: from.value || "",
      to: to.value || ""
    }).toString();

    axios.get(`/api/checkClientBalance?${params}`)
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
        console.error(error);
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


function shouldShowCar(car) {
  if (!car) {
    return false;
  }
  if (car.results == 2) {
    return showComplatedCars.value;
  }
  return true;
}

const mergedData = computed(() => {
  if (!isDataLoaded.value || !laravelData.value) return [];
  
  try {
    const cars = laravelData.value.data || [];
    const transactions = laravelData.value.transactions || [];
    
    // تجهيز السيارات والدفعات
    const allItems = [];
    
    // إضافة السيارات مع تواريخها
    if (Array.isArray(cars)) {
      for (let i = 0; i < cars.length; i++) {
        const car = cars[i];
        const normalizedCarNumber = normalizeIdentifier(car?.car_number);
        const normalizedVin = normalizeIdentifier(car?.vin);
        const normalizedIdentifiers = [normalizedCarNumber, normalizedVin].filter(Boolean);
        allItems.push({ 
          type: 'car', 
          data: car,
          date: new Date(car.created_at || car.date || 0),
          id: `car-${car.id || i}`,
          normalizedIdentifiers
        });
      }
    }
    
    // إضافة الدفعات مع تواريخها (إذا كان الفلاغ مفعل)
    if (showPaymentsInTable.value && Array.isArray(transactions) && showComplatedCars.value) {
      const payments = transactions.filter(t => 
        t && t.type === 'out' && Number(t.amount) < 0 && t.is_pay === 1
      );
      
      for (let i = 0; i < payments.length; i++) {
        const payment = payments[i];
        const relatedIdentifiers = extractIdentifiersFromDescription(payment?.description);
        const highlightColor = getHighlightColorForPayment(payment);
        allItems.push({ 
          type: 'payment', 
          data: payment,
          date: new Date(payment.created_at || payment.created || 0),
          id: `payment-${payment.id || i}`,
          relatedIdentifiers,
          highlightColor
        });
      }
    }
    
    // ترتيب حسب التاريخ (الأقدم أولاً)
    allItems.sort((a, b) => a.date - b.date);
    
    // حساب الرصيد المتصل (بسيط جداً)
    let balance = 0;
    let totalSum = 0;
    const merged = [];
    
    for (let i = 0; i < allItems.length; i++) {
      const item = allItems[i];
      
      if (item.type === 'car') {
        const car = item.data;
        const isVisible = shouldShowCar(car);
        
        if (isVisible) {
          // السيارة تزيد الرصيد: (المجموع - الخصم) فقط - بدون طرح المدفوع
          const total = Number(car.total_s) || 0;
          balance += (total); // الرصيد = الإجمالي - الخصم فقط  - discount
          totalSum += (total ); // المجموع التراكمي-   discount
        }
      } else if (item.type === 'payment') {
        // الدفعة قيمتها سالبة أصلاً، نجمعها مباشرة (الدفعات تنزل الرصيد)
        const paymentAmount = Number(item.data.amount) || 0;
        balance += paymentAmount; // نجمع (السالب ينزل الرصيد تلقائياً)
        totalSum += paymentAmount; // المجموع التراكمي
      }
      
      merged.push({
        ...item,
        balance: balance,
        totalSum: totalSum
      });
    }
    
    return merged;
  } catch (error) {
    console.error('❌ Error in mergedData:', error);
    return [];
  }
});

watch(() => props.q, () => {
  resetQueryBehaviour();
  if (props.q) {
    nextTick(() => {
      scrollToHighlightedCar();
    });
  }
});

watch(matchedRowElement, (el) => {
  if (!el || !shouldAutoScrollToQuery.value || typeof el.scrollIntoView !== 'function') {
    return;
  }
  nextTick(() => {
    el.scrollIntoView({
      behavior: 'smooth',
      block: 'center'
    });
    shouldAutoScrollToQuery.value = false;
  });
});

watch(
  () => showComplatedCars.value,
  (newVal) => {
    showPaymentsInTable.value = newVal;
    if (props.q && shouldAutoScrollToQuery.value) {
      nextTick(() => scrollToHighlightedCar());
    }
  }
);

watch(
  () => mergedData.value,
  () => {
    if (props.q && shouldAutoScrollToQuery.value) {
      nextTick(() => scrollToHighlightedCar());
    }
  },
  { deep: true }
);


function getImageUrl(name) {
      // Provide the base URL for your images
      return `/public/uploadsResized/${name}`;
    }
function getDownloadUrl(name) {
      // Provide the base URL for downloading images
      return `/public/uploads/${name}`;
    }

const distributedBalance = computed(() => {
  try {
    const paymentsTotal = Number(calculateTotalFilteredAmount().totalAmount || 0);
    const carsDiscount = Number(laravelData.value?.cars_discount || 0);
    const carsPaid = Number(laravelData.value?.cars_paid || 0);
    return (paymentsTotal * -1 - carsDiscount) - carsPaid;
  } catch (error) {
    return 0;
  }
});
 



const payment_loading = ref(false);
const selectedCompletedCarsState = ref(true);

const editingPaymentDescriptionId = ref(null);
const paymentDescriptionDraft = ref('');
const paymentDescriptionError = ref('');
const isSavingPaymentDescription = ref(false);
const PAYMENT_DESCRIPTION_MAX = 1000;

function startEditingPaymentDescription(payment) {
  if (!payment || isSavingPaymentDescription.value) {
    return;
  }
  editingPaymentDescriptionId.value = payment.id;
  paymentDescriptionDraft.value = payment.description ?? '';
  paymentDescriptionError.value = '';
}

function cancelEditingPaymentDescription() {
  if (isSavingPaymentDescription.value) {
    return;
  }
  editingPaymentDescriptionId.value = null;
  paymentDescriptionDraft.value = '';
  paymentDescriptionError.value = '';
}

async function savePaymentDescription(payment) {
  if (!payment || editingPaymentDescriptionId.value !== payment.id) {
    return;
  }

  const trimmed = paymentDescriptionDraft.value ? paymentDescriptionDraft.value.trim() : '';

  if (!trimmed) {
    paymentDescriptionError.value = 'الوصف مطلوب';
    return;
  }

  if (trimmed.length > PAYMENT_DESCRIPTION_MAX) {
    paymentDescriptionError.value = `الوصف يجب ألا يتجاوز ${PAYMENT_DESCRIPTION_MAX} حرفًا`;
    return;
  }

  isSavingPaymentDescription.value = true;
  paymentDescriptionError.value = '';

  try {
    await axios.post('/api/updateTransactionDescription', {
      transaction_id: payment.id,
      description: trimmed,
    });

    payment.description = trimmed;
    payment._descriptionUpdated = true;
    setTimeout(() => {
      if (payment) {
        payment._descriptionUpdated = false;
      }
    }, 3000);

    cancelEditingPaymentDescription();
  } catch (error) {
    if (error.response?.data?.errors?.description?.length) {
      paymentDescriptionError.value = error.response.data.errors.description[0];
    } else if (error.response?.data?.message) {
      paymentDescriptionError.value = error.response.data.message;
    } else {
      paymentDescriptionError.value = 'حدث خطأ أثناء حفظ الوصف';
    }
  } finally {
    isSavingPaymentDescription.value = false;
  }
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
      <div class="flex justify-between items-center mb-4">
        <h2 class="text-center pb-2 dark:text-gray-400 flex-1">
          {{ $t("sales_bill") }}
        </h2>
        <Link 
          v-if="props.client?.has_internal_sales"
          :href="`/internalSales/${currentClientId}`" 
          class="px-4 py-2 text-white bg-purple-600 rounded hover:bg-purple-700 dark:bg-purple-700 dark:hover:bg-purple-800 flex items-center gap-2"
        >
          <span>💰</span>
          <span>المبيعات الداخلية</span>
        </Link>
      </div>
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
              <div class="mb-4 mr-5">
                <InputLabel for="filters" value="خيارات العرض" class="mb-2" />
                <div class="p-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800">
                  <!-- فلتر السيارات المكتملة -->
                  <div class="flex items-center justify-between">
                    <label for="switch-completed" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                       السيارات المكتملة
                    </label>
                    <label class="relative inline-flex items-center cursor-pointer">
                      <input 
                        type="checkbox" 
                        id="switch-completed"
                        @change="showComplatedCars = !showComplatedCars" 
                        :checked="!showComplatedCars" 
                        class="sr-only peer"
                      >
                      <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                    </label>
                </div>
                  
              </div>
            </div>
            </div>
            <div class="px-2 flex flex-col justify-end">
              <div class="mb-4">
                <InputLabel for="from" :value="$t('from_date')" />
                <TextInput
                  id="from"
                  type="date"
                  class="mt-1 block w-full"
                  v-model="from"
                />
              </div>
            </div>
            <div class="px-2 flex flex-col justify-end">
              <div class="mb-4">
                <InputLabel for="to" :value="$t('to_date')" />
                <TextInput
                  id="to"
                  type="date"
                  class="mt-1 block w-full"
                  v-model="to"
                />
              </div>
            </div>
            <div class="mb-4 mr-5 print:hidden flex flex-col gap-2">
              <InputLabel for="pay" value="فلترة" />
              <button
                @click.prevent="getResults(1, false)"
                class="w-full px-6 py-2 mt-1 font-bold text-white bg-gray-500 rounded"
              >
                <span>فلترة</span>
              </button>
            </div>
            <div class="mb-4 mr-5 print:hidden flex flex-col gap-2">
              <InputLabel for="pay" value="طباعة" />
              <a
                :href="`/api/getIndexAccountsSelas?user_id=${client_Select}&from=${from}&to=${to}&print=1&showComplatedCars=${ showComplatedCars ? 0:1}`"
                target="_blank"
                class="w-full px-6 py-2 mt-1 font-bold text-white bg-orange-500 rounded block text-center"
              >
                <span>طباعة</span>
              </a>
            </div>
            <div class="mb-4 mr-5 print:hidden flex flex-col gap-2">
              <InputLabel for="pay" value="طباعة" />
              <a
                :href="`/api/getIndexAccountsSelas?user_id=${client_Select}&from=${from}&to=${to}&print=1&printExcel=1&showComplatedCars=${ showComplatedCars ? 0:1}`"
                target="_blank"
                class="w-full px-6 py-2 mt-1 font-bold text-white bg-green-500 rounded block text-center"
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
                class="px-6 mb-6 py-2 mt-1 font-bold text-white bg-green-500 rounded"
                style="width: 100%">
                <span>اضافة دفعة</span>
              </button>
              <button
                @click.prevent="hideAddPaymentTotal()"
                v-if="showPaymentForm"
                :disabled="isLoading"
                class="px-6 mb-6 py-2 mt-1 font-bold text-white bg-pink-500 rounded"
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
                class="px-6 mb-6 py-2 mt-1 font-bold text-white bg-purple-500 rounded"
                style="width: 100%">
                <span>عرض الدفعات</span>
              </button>
              <button
                @click.prevent="hideTransactionsDiv()"
                v-if="showTransactions"
                class="px-6 mb-6 py-2 mt-1 font-bold text-white bg-pink-500 rounded"
                style="width: 100%">
                <span>اخفاء الدفعات</span>
              </button>
            </div>
            <div className="mb-4 mr-5">
              <InputLabel for="client_phone" value="رقم هاتف الزبون" />
              <TextInput
                id="client_phone"
                type="text"
                :class="[
                  'mt-1 block w-full border rounded',
                  isClientPhoneTooLong
                    ? 'border-red-500 focus:border-red-500 focus:ring-red-400'
                    : 'border-gray-300 focus:border-indigo-500 focus:ring-indigo-200'
                ]"
                v-model="clientPhone"
                :disabled="isSavingPhone"
              />
              <p v-if="isClientPhoneTooLong" class="text-xs text-red-500 mt-1">
                الرقم يجب ألا يتجاوز 10 أرقام.
              </p>
              <p v-if="isSavingPhone" class="text-xs text-gray-500 mt-1">
                جاري حفظ رقم الهاتف...
              </p>
            </div>
            <div className="mb-4  mr-5"   v-if="distributedBalance != 0">
              <InputLabel for="cars_need_paid" value="الرصيد غير موزع بالدولار" />
              <TextInput
                id="cars_need_paid"
                type="number"
                class="mt-1 block w-full"
               
                :value="distributedBalance"
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
                class="px-6 mb-6 py-2 mt-1 font-bold text-white bg-green-500 rounded"
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
                    <th className="px-1 py-2 text-base">no</th>
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
                  <td className="px-4 py-2 border dark:border-gray-800 dark:text-gray-200">{{ formatBaghdadTimestamp(user.created_at) }}</td>
                  <td className="px-4 py-2 border dark:border-gray-800 dark:text-gray-200 align-top">
                    <div v-if="editingPaymentDescriptionId === user.id" class="space-y-2 text-right">
                      <textarea
                        v-model="paymentDescriptionDraft"
                        class="w-full rounded border border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm leading-6 p-2"
                        rows="3"
                        :maxlength="PAYMENT_DESCRIPTION_MAX"
                        placeholder="اكتب الوصف الجديد هنا"
                      ></textarea>
                      <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                        <span>الحد الأقصى {{ PAYMENT_DESCRIPTION_MAX }} حرفًا</span>
                        <span :class="paymentDescriptionDraft.length > PAYMENT_DESCRIPTION_MAX ? 'text-red-500' : ''">
                          {{ paymentDescriptionDraft.length }}/{{ PAYMENT_DESCRIPTION_MAX }}
                        </span>
                      </div>
                      <p v-if="paymentDescriptionError" class="text-xs text-red-500">{{ paymentDescriptionError }}</p>
                      <div class="flex justify-end gap-2">
                        <button
                          type="button"
                          class="px-3 py-1 text-sm font-semibold rounded bg-gray-200 text-gray-800 dark:bg-gray-700 dark:text-gray-200"
                          @click="cancelEditingPaymentDescription"
                          :disabled="isSavingPaymentDescription"
                        >
                          إلغاء
                        </button>
                        <button
                          type="button"
                          class="px-3 py-1 text-sm font-semibold text-white bg-green-600 rounded disabled:opacity-70"
                          @click="savePaymentDescription(user)"
                          :disabled="isSavingPaymentDescription"
                        >
                          <span v-if="isSavingPaymentDescription">جارٍ الحفظ...</span>
                          <span v-else>حفظ</span>
                        </button>
                      </div>
                    </div>
                    <div v-else class="space-y-1 text-right">
                      <span class="block whitespace-pre-line leading-6">{{ user.description }}</span>
                      <span py-1 text-white bg-blue-500 rounded-md focus:outline-none disabled:opacity-60
                        v-if="user._descriptionUpdated"
                        class="inline-flex items-center text-xs font-semibold text-green-600"
                      >
                        تم التحديث
                      </span>
                    </div>
                  </td>
                  <td className="px-4 py-2 border dark:border-gray-800 dark:text-gray-200">{{ user.amount*-1  }}</td>
                  <td className="px-4 py-2 border dark:border-gray-800 dark:text-gray-200 space-x-1 space-x-reverse">  
                    <button
                      class="px-4 py-2  text-white bg-blue-500 rounded-md focus:outline-none disabled:opacity-60"
                      title="تعديل الوصف"
                      @click="startEditingPaymentDescription(user)"
                      :disabled="isSavingPaymentDescription && editingPaymentDescriptionId === user.id"
                    >
                      <edit class="w-4 h-4" />
                    </button>
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
            <!-- Loading State -->
            <div v-if="!isDataLoaded" class="text-center py-10">
              <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500"></div>
              <p class="mt-4 text-gray-600 dark:text-gray-400">جاري تحميل البيانات...</p>
            </div>
            
            <div v-else class="relative overflow-x-auto shadow-md sm:rounded-lg">
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
                    <th scope="col" class="px-1 py-2 text-base bg-gradient-to-r from-blue-500 to-indigo-500 text-white dark:from-blue-600 dark:to-indigo-600">
                      الرصيد
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
                  <template v-for="(item, i) in mergedData" :key="item.id">
                  <!-- صف السيارة -->
                  <tr
                    v-if="item.type === 'car'"
                    v-show="shouldShowCar(item.data)"
                    :class="[
                      {
                      'bg-red-100 dark:bg-red-900': item.data.results == 0,
                      'bg-red-100 dark:bg-red-900': item.data.results == 1,
                      'bg-green-100 dark:bg-green-900': item.data.results == 2,
                      },
                      isCarReferencedByPayment(item)
                        ? 'ring-2 ring-inset'
                        : ''
                    ,
                      matchesCarQuery(item.data) ? 'query-highlight-row' : ''
                    ]"
                    :data-car-match="matchesCarQuery(item.data) ? 'true' : null"
                    :ref="el => setMatchedRowRef(el, item.data)"
                    :style="getCarHighlightStyle(item)"
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
                      :class="{
                        'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200': item.totalSum > 0,
                        'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200': item.totalSum <= 0
                      }"
                    >
                      {{ item.totalSum?.toFixed(0) || 0 }}
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
                      <!-- Badge المشتري إذا كانت السيارة مباعة في المبيعات الداخلية -->
                      <span
                        v-if="props.client?.has_internal_sales && item.data.internal_sale?.client"
                        class="px-2 py-1 m-1 bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200 rounded text-xs font-semibold"
                        style="min-width: 110px; display: block;"
                        title="مباعة في المبيعات الداخلية"
                      >
                        👤 {{ item.data.internal_sale.client.name }}
                      </span>
                      <!-- زر البيع إذا لم تكن مباعة -->
                      <Link
                        v-else-if="props.client?.has_internal_sales && !item.data.internal_sale"
                        :href="`/internalSales/${currentClientId}?car_id=${item.data.id}`"
                        tabIndex="1"
                        class="px-1 py-1  text-white  m-1 bg-purple-600 rounded hover:bg-purple-700 dark:bg-purple-700 dark:hover:bg-purple-800"
                        title="بيع في المبيعات الداخلية"
                      >
                        💰
                      </Link>
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
                        v-if="distributedBalance != 0"
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
                    :class="[
                      'bg-gradient-to-r from-purple-50 to-pink-50 dark:from-purple-900/30 dark:to-pink-900/30 border-b dark:border-gray-700',
                      paymentReferencesCars(item) ? 'ring-2 ring-inset' : ''
                    ]"
                    :style="getPaymentHighlightStyle(item)"
                  >
                    <!-- 1. no -->
                    <td className="border dark:border-gray-800 text-center px-2 py-2 font-bold text-purple-800 dark:text-purple-200">
                      💳
                    </td>
                    <!-- 2-6. car_type to car_number -->
                    <td colspan="5" className="border dark:border-gray-800 text-start px-3 py-2">
                      <span class="font-semibold mr-2">{{ item.data.description }}</span>
                    </td>
                    <!-- 7-8. dinar, dolar_price -->
                    <td colspan="2" className="border dark:border-gray-800 text-center px-2 py-2"></td>
                    <!-- 9-12. print:hidden columns -->
                    <td colspan="4" className="border dark:border-gray-800 text-center px-2 py-2 print:hidden"></td>
                    <!-- 13-17. shipping to land_shipping -->
                    <td colspan="5" className="border dark:border-gray-800 text-center px-2 py-2"></td>
                    <!-- 18. total -->
                    <td className="border dark:border-gray-800 text-center px-2 py-2 font-bold text-red-700 dark:text-red-300 text-base bg-red-50 dark:bg-red-900/20">
                      <span class="text-red-600 dark:text-red-400">⬇️ -</span> {{ (item.data.amount * -1).toFixed(0) }}
                    </td>
                    <!-- 19. paid -->
                    <td className="border dark:border-gray-800 text-center px-2 py-2 bg-red-50 dark:bg-red-900/20"></td>
                    <!-- 20. discount -->
                    <td className="border dark:border-gray-800 text-center px-2 py-2 bg-red-50 dark:bg-red-900/20"></td>
                    <!-- 21. الرصيد -->
                    <td
                      className="border dark:border-gray-800 text-center px-2 py-2 font-bold text-base"
                      :class="{
                        'bg-purple-600 text-white dark:bg-purple-700': item.totalSum > 0,
                        'bg-green-600 text-white dark:bg-green-700': item.totalSum <= 0
                      }"
                    >
                      {{ item.totalSum?.toFixed(0) || 0 }}
                    </td>
                    <!-- 22. date -->
                    <td className="border dark:border-gray-800 text-center px-2 py-2 text-sm">
                        {{ item.data.created }}
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
                  
                  <!-- صف الرصيد غير الموزع في آخر الجدول -->
                  <tr 
                    v-if="!showPaymentsInTable && distributedBalance != 0"
                    class="bg-gradient-to-r from-yellow-100 to-amber-100 dark:from-yellow-900/30 dark:to-amber-900/30 border-t-4 border-amber-500"
                  >
                    <!-- 1. no -->
                    <td className="border dark:border-gray-800 text-center px-2 py-3 font-bold">
                       
                    </td>
                    <!-- 2-17. باقي الأعمدة -->
                    <td colspan="16" className="border dark:border-gray-800 text-start px-4 py-3">
                      <span class="text-xl font-bold text-amber-800 dark:text-amber-200">💰 الرصيد غير الموزع على السيارات:</span>
                    </td>
                    <!-- 18. total -->
                    <td className="border dark:border-gray-800 text-center px-2 py-3"></td>
                    <!-- 19. paid -->
                    <td className="border dark:border-gray-800 text-center px-2 py-3"></td>
                    <!-- 20. discount -->
                    <td className="border dark:border-gray-800 text-center px-2 py-3"></td>
                    <!-- 21. الرصيد -->
                    <td className="border dark:border-gray-800 text-center px-2 py-3 font-bold text-xl bg-amber-200 dark:bg-amber-800 text-amber-900 dark:text-amber-100">
                      {{ distributedBalance.toFixed(0) }}
                    </td>
                    <!-- 22. date -->
                    <td className="border dark:border-gray-800 text-center px-2 py-3"></td>
                    <!-- 23-25. الأعمدة الأخرى -->
                    <td colspan="3" className="border dark:border-gray-800 text-center px-2 py-3 print:hidden"></td>
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
.query-highlight-row {
  position: relative;
  scroll-margin-top: 160px;
}

.query-highlight-row > td {
  background-image: linear-gradient(90deg, rgba(250, 204, 21, 0.22), rgba(250, 204, 21, 0.35));
  background-color: rgba(250, 204, 21, 0.25) !important;
  color: #1f2937;
}

.dark .query-highlight-row > td {
  background-image: linear-gradient(90deg, rgba(217, 119, 6, 0.35), rgba(217, 119, 6, 0.25));
  background-color: rgba(217, 119, 6, 0.25) !important;
  color: #f9fafb;
}

.query-highlight-row::after {
  content: "";
  position: absolute;
  inset: 0;
  border-radius: 0.375rem;
  box-shadow: 0 0 0 3px rgba(234, 179, 8, 0.45), 0 12px 30px -12px rgba(234, 179, 8, 0.6);
  pointer-events: none;
  animation: queryHighlightPulse 3s ease-in-out infinite;
}

@media (prefers-reduced-motion: reduce) {
  .query-highlight-row::after {
    animation: none;
  }
}

@keyframes queryHighlightPulse {
  0%, 100% {
    box-shadow: 0 0 0 2px rgba(234, 179, 8, 0.4), 0 10px 28px -14px rgba(234, 179, 8, 0.45);
  }
  50% {
    box-shadow: 0 0 0 4px rgba(234, 179, 8, 0.55), 0 14px 36px -10px rgba(234, 179, 8, 0.6);
  }
}

.query-highlight-row svg {
  color: #92400e;
}

.dark .query-highlight-row svg {
  color: #fbbf24;
}
</style>
<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Modal from "@/Components/Modal.vue";
import { Head, Link, useForm, usePage } from "@inertiajs/inertia-vue3";
import { computed, ref, watch } from "vue";
import ModalAddSales from "@/Components/ModalAddSales.vue";
import ModalAddDebt from "@/Components/ModalAddDebt.vue";
import ModalAddExpenses from "@/Components/ModalAddExpenses.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import ModalAddGenExpenses from "@/Components/ModalAddGenExpenses.vue";
import ModalAddExpensesToMainBransh from "@/Components/ModalAddExpensesToMainBransh.vue";
import ModalExpensesFromOtherBransh from "@/Components/ModalExpensesFromOtherBransh.vue";
import ModalConvertDollarDinar from "@/Components/ModalConvertDollarDinar.vue";
import ModalConvertDinarDollar from "@/Components/ModalConvertDinarDollar.vue";
import ModalDel from "@/Components/ModalDel.vue";
import ModalUploader from "@/Components/ModalUploader.vue";
import ModalTransferWalletTransaction from "@/Components/ModalTransferWalletTransaction.vue";
import axios from 'axios';
import show from "@/Components/icon/show.vue";
import imags from "@/Components/icon/imags.vue";
import trash from "@/Components/icon/trash.vue";
import edit from "@/Components/icon/edit.vue";
import print from "@/Components/icon/print.vue";
import transfer from "@/Components/icon/transfer.vue";
import InfiniteLoading from "v3-infinite-loading";
import "v3-infinite-loading/lib/style.css";
import debounce from 'lodash/debounce';
import { formatBaghdadTimestamp } from "@/utils/datetime";

const inertiaPage = usePage();
const showAccountingExtraButtons = computed(
  () => inertiaPage.props.value?.showAccountingExtraButtons !== false
);
const showKirkukTransfers = computed(
  () => inertiaPage.props.value?.showKirkukTransfers === true
);

const laravelData = ref({});
/** رصيد الصندوق من دفتر القيود (transactions) — ليس من wallet.balance المخزّن */
const cashBoxBalanceDollar = computed(() => {
  const d = laravelData.value;
  if (d?.ledger_balance !== null && d?.ledger_balance !== undefined) {
    return d.ledger_balance;
  }
  return d?.user?.wallet?.balance ?? 0;
});
const cashBoxBalanceDinar = computed(() => {
  const d = laravelData.value;
  if (d?.ledger_balance_dinar !== null && d?.ledger_balance_dinar !== undefined) {
    return d.ledger_balance_dinar;
  }
  return d?.user?.wallet?.balance_dinar ?? 0;
});
const searchTerm = ref('');
let showModalAddSales = ref(false);
let showModaldebtSales = ref(false);
let showModalAddExpenses = ref(false);
let showModalAddGenExpenses = ref(false);
let showModalConvertDollarDinar = ref(false);
let showModalConvertDinarDollar = ref(false);
let showModalAddExpensesToMainBransh = ref(false);
let showModalExpensesFromOtherBransh = ref(false);
let showModalDel = ref(false);
let showModalUploader = ref(false);
let showModalTransfer = ref(false);
let tranForTransfer = ref(null);
let transactions= ref([]);
let expenses_type_id = ref(0);
let tranId =ref({});
let formData = ref({});
let GenExpenses = ref({});
let isLoading=ref(false);
let from = ref(getTodayDate());
let to = ref(getTodayDate());
let onlineContracts= ref(0)
let howler= ref(0)
let shippingCoc= ref(0)
let border= ref(0)
let iran= ref(0)
let dubai= ref(0)
let debtOnlineContracts= ref(0)
let allCars= ref(0)
let transactionInTodayDollar = ref(0)
let transactionInTodayDinar = ref(0)
let transactionOutTodayDollar = ref(0)
let transactionOutTodayDinar = ref(0)
let onlineContractsDinar = ref(0)
let debtOnlineContractsDinar = ref(0)
let resetData = ref(false);
let user_id = 0;
let page = 1;
let q = '';
let allTransfers=ref([]);
let deletedOnly = ref(false);
let restoringId = ref(null);

const editingDescriptionId = ref(null);
const descriptionDraft = ref('');
const isSavingDescription = ref(false);
const descriptionError = ref('');
const DESCRIPTION_MAX_LENGTH = 1000;

/** تبويبات: الحركات | تاريخ رصيد الصندوق (lazy API) */
const activeTab = ref('movements');
const historyRows = ref([]);
const historyMeta = ref({
  current_ledger: null,
  latest_verification: null,
  verified_until_transaction_id: null,
  can_verify: false,
  pagination: null,
});
const historyLoading = ref(false);
const historyError = ref('');
const historyPage = ref(1);
const historyLoadedOnce = ref(false);
const historyHasMore = ref(false);
const showVerifyModal = ref(false);
const verifyTarget = ref(null);
const verifyNote = ref('');
const verifySubmitting = ref(false);
const verifyError = ref('');

const isAdminUser = computed(
  () => Number(inertiaPage.props.value?.auth?.user?.type_id) === 1
);

function applyHistoryMeta(data) {
  historyMeta.value = {
    current_ledger: data.current_ledger || null,
    latest_verification: data.latest_verification || null,
    verified_until_transaction_id: data.verified_until_transaction_id ?? null,
    can_verify: !!data.can_verify,
    pagination: data.pagination || null,
  };
  const pag = data.pagination || {};
  if (typeof pag.has_more === 'boolean') {
    historyHasMore.value = pag.has_more;
  } else if (pag.has_more === 1 || pag.has_more === '1') {
    historyHasMore.value = true;
  } else if (pag.has_more === 0 || pag.has_more === '0') {
    historyHasMore.value = false;
  } else {
    historyHasMore.value =
      Number(pag.current_page) > 0 &&
      Number(pag.last_page) > 0 &&
      Number(pag.current_page) < Number(pag.last_page);
  }
  if (pag.current_page) {
    historyPage.value = Number(pag.current_page);
  }
}

async function loadCashBoxHistory(reset = true) {
  if (historyLoading.value) return;
  historyLoading.value = true;
  historyError.value = '';
  const pageToLoad = reset ? 1 : historyPage.value + 1;
  try {
    if (reset) {
      historyPage.value = 1;
      historyRows.value = [];
    }
    const lastId =
      !reset && historyRows.value.length
        ? historyRows.value[historyRows.value.length - 1].id
        : null;
    const response = await axios.get('/getCashBoxHistory', {
      params: {
        page: pageToLoad,
        per_page: 50,
        ...(lastId ? { before_id: lastId } : {}),
      },
    });
    const data = response.data || {};
    const rows = Array.isArray(data.transactions) ? data.transactions : [];
    if (reset) {
      historyRows.value = rows;
    } else {
      const seen = new Set(historyRows.value.map((r) => r.id));
      const fresh = rows.filter((r) => !seen.has(r.id));
      historyRows.value = [...historyRows.value, ...fresh];
    }
    applyHistoryMeta(data);
    historyLoadedOnce.value = true;
  } catch (error) {
    historyError.value =
      error?.response?.data?.message || 'تعذر تحميل تاريخ رصيد الصندوق';
  } finally {
    historyLoading.value = false;
  }
}

function openCashBoxHistoryTab() {
  activeTab.value = 'history';
  if (!historyLoadedOnce.value) {
    loadCashBoxHistory(true);
  }
}

async function loadMoreHistory() {
  if (!historyHasMore.value || historyLoading.value) return;
  await loadCashBoxHistory(false);
}

function openVerifyModal(row) {
  if (!historyMeta.value.can_verify && !isAdminUser.value) return;
  if (row?.is_trusted) return;
  verifyTarget.value = row;
  verifyNote.value = '';
  verifyError.value = '';
  showVerifyModal.value = true;
}

function closeVerifyModal() {
  showVerifyModal.value = false;
  verifyTarget.value = null;
  verifyNote.value = '';
  verifyError.value = '';
}

async function confirmVerify() {
  if (!verifyTarget.value?.id || verifySubmitting.value) return;
  verifySubmitting.value = true;
  verifyError.value = '';
  try {
    const response = await axios.post('/verifyCashBoxHistory', {
      transaction_id: verifyTarget.value.id,
      note: verifyNote.value || null,
    });
    const history = response.data?.history;
    if (history) {
      historyRows.value = history.transactions || [];
      applyHistoryMeta(history);
      historyPage.value = 1;
      historyLoadedOnce.value = true;
    } else {
      await loadCashBoxHistory(true);
    }
    closeVerifyModal();
  } catch (error) {
    verifyError.value =
      error?.response?.data?.message ||
      (error?.response?.status === 403
        ? 'غير مصرح: التوثيق للمسؤول فقط'
        : 'تعذر توثيق الحركة');
  } finally {
    verifySubmitting.value = false;
  }
}

watch(activeTab, (tab) => {
  if (tab === 'history' && !historyLoadedOnce.value) {
    loadCashBoxHistory(true);
  }
});

const refresh = () => {
  page = 0;
  transactions.value.length = 0;
  resetData.value = !resetData.value;


};
const debouncedGetResultsCar = debounce(refresh, 500);

const getResults = async ($state) => {
  try {
    const params = {
      limit: 100,
      page: page,
      q: q,
      user_id: props.boxes[0].id,
      from: from.value,
      to: to.value,
    };
    if (deletedOnly.value) {
      params.deleted_only = 1;
    }
    const response = await axios.get(`/getIndexAccounting`, { params });

    const json = response.data;


    if (json.transactions.data.length < 100){
      transactions.value.push(...json.transactions.data);
      $state.complete();
    } 
    else {
      transactions.value.push(...json.transactions.data);
       $state.loaded();
    }

    laravelData.value = json;
    page++;
  } catch (error) {
    console.log(error);
    //$state.error();
  }
};

function toggleDeletedPayments() {
  deletedOnly.value = !deletedOnly.value;
  if (deletedOnly.value) {
    from.value = '';
    to.value = '';
  } else {
    from.value = getTodayDate();
    to.value = getTodayDate();
  }
  refresh();
}

async function restoreDeletedTransaction(tran) {
  if (!tran?.id || restoringId.value) {
    return;
  }
  if (!window.confirm(`استرجاع الدفعة رقم ${tran.id}؟`)) {
    return;
  }
  restoringId.value = tran.id;
  try {
    await axios.post('/api/restoreTransaction', { id: tran.id });
    transactions.value = transactions.value.filter((t) => t.id !== tran.id);
  } catch (error) {
    const message = error?.response?.data?.message || 'تعذر استرجاع الدفعة';
    window.alert(message);
  } finally {
    restoringId.value = null;
  }
}
 
const getcountTotalInfo = async () => {
  axios.get('/api/totalInfo')
  .then(response => {
    onlineContracts.value=  response.data.data.onlineContracts
    howler.value=  response.data.data.howler
    shippingCoc.value=  response.data.data.shippingCoc
    border.value=  response.data.data.border
    iran.value=  response.data.data.iran
    dubai.value=  response.data.data.dubai
    debtOnlineContracts.value=  response.data.data.debtOnlineContracts
    onlineContractsDinar.value =response.data.data.onlineContractsDinar
    debtOnlineContractsDinar.value = response.data.data.debtOnlineContractsDinar
    allCars.value =response.data.data.allCars;
    transactionInTodayDollar.value = response.data.data.transactionInTodayDollar
    transactionOutTodayDollar.value = response.data.data.transactionOutTodayDollar
    transactionInTodayDinar.value = response.data.data.transactionInTodayDinar
    transactionOutTodayDinar.value = response.data.data.transactionOutTodayDinar

  })
  .catch(error => {
    console.error(error);
  })
  
    
}
getcountTotalInfo()
function openAddSales() {
  showModalAddSales.value = true;
}
function opendebtSales() {
  showModaldebtSales.value = true;
}
function openAddExpenses(){
  showModalAddExpenses.value = true;
}
function openModalAddExpensesToMainBransh() {
  getTransfers();
  showModalAddExpensesToMainBransh.value = true;
}
function openModalExpensesFromOtherBransh() {
  getTransfers();
  showModalExpensesFromOtherBransh.value = true;
}
function openConvertDollarDinar(){
  showModalConvertDollarDinar.value = true;
}
function openConvertDinarDollar(){
  showModalConvertDinarDollar.value = true;
}
function openModalDel(tran){
  tranId.value = tran
  showModalDel.value = true;
}
function openModalUploader(tran){
  tranId.value = tran
  showModalUploader.value = true;
}

function canTransferBoxTransaction(tran) {
  if (!tran) {
    return false;
  }
  const parentId = Number(tran.parent_id ?? 0);
  if (parentId > 0) {
    return false;
  }
  // Direct add/withdraw, or already-assigned box rows (re-target)
  return ['in', 'out', 'debt', 'inUserBox', 'outUserBox'].includes(tran.type);
}

function openModalTransfer(tran) {
  if (!canTransferBoxTransaction(tran)) return;
  tranForTransfer.value = tran;
  showModalTransfer.value = true;
}

function onTransferSaved() {
  showModalTransfer.value = false;
  tranForTransfer.value = null;
  refresh();
}

const props = defineProps({
  url: String,
  users:Array,
  accounts:Array,
  boxes:Array,
  flaggedWallets: {
    type: Array,
    default: () => []
  },
  walletUsers: {
    type: Array,
    default: () => []
  },
});
const search = async (q) => {
  user_id.value=0;
  laravelData.value = [];
  const response = await fetch(`/livesearchAppointment?q=${q}`);
  laravelData.value = await response.json();
};
const form = useForm();

let showModal = ref(false);
const come = async (id) => {
  const response = await fetch(`/appointmentCome?id=${id}`);
  refresh();

};
const cancel = async (id) => {
  const response = await fetch(`/appointmentCancel?id=${id}`);
  refresh();

};

 
 
const errors = ref(0);
 
 
 
function confirm(V) {
  axios.post('/api/receiptArrived',V)
  .then(response => {
    showModalAddSales.value=false;
    //getResults();
    window.location.reload();
  })
  .catch(error => {

    errors.value = error.response.data.errors
  })
}
function confirmdebt(V) {
  axios.post('/api/salesDebt',V)
  .then(response => {
    refresh();
    showModaldebtSales.value=false;
    showModalAddExpenses.value = false;
    window.location.reload();

  })
  .catch(error => {

    errors.value = error.response.data.errors
  })
}
function confirmConvertDollarDinar(V) {
  axios.post('/api/convertDollarDinar',V)
  .then(response => {
    refresh();
    showModalConvertDollarDinar.value=false;

  })
  .catch(error => {

    errors.value = error.response.data.errors
  })
}
function confirmConvertDinarDollar(V) {
  axios.post('/api/convertDinarDollar',V)
  .then(response => {
    refresh();
    showModalConvertDinarDollar.value=false;

  })
  .catch(error => {

    errors.value = error.response.data.errors
  })
}



function getTodayDate() {
  const today = new Date();
  const year = today.getFullYear();
  const month = String(today.getMonth() + 1).padStart(2, '0');
  const day = String(today.getDate()).padStart(2, '0');
  return `${year}-${month}-${day}`;
}
function delTransactions(id){
  axios.post(`/api/delTransactions?id=${id.id}`)
  .then(response => {
    refresh();
    showModalDel.value=false;
  })
  .catch(error => {

    errors.value = error.response.data.errors
  })
}
function openAddGenExpenses(v) {
    expenses_type_id.value=v
    getGenfirmExpenses()
    showModalAddGenExpenses.value = true;
}
function getGenfirmExpenses() {
  axios.get(`/api/getGenExpenses?expenses_type_id=${expenses_type_id.value}`)
  .then(response => {
    GenExpenses.value = response.data;

  })
  .catch(error => {

    errors.value = error.response.data.errors
  })


}

function conGenfirmExpenses(V) {
  axios.post(`/api/GenExpenses?amount=${V.amount??0}&expenses_type_id=${expenses_type_id.value}&factor=${V.factor??1}&note=${V.note??''}`)
  .then(response => {
    refresh();
    showModalAddGenExpenses.value = false;
    console.log(response.data);
    window.open(`/api/getIndexAccountsSelas?user_id=${response.data.morphed_id}&print=3&transactions_id=${response.data.id}`, '_blank');
    window.location.reload();
  })
  .catch(error => {

    errors.value = error.response.data.errors
  })


}
function getTransfers(){
  axios.get(`/api/transfers`)
  .then(response => {
    allTransfers.value = response.data
  })
  .catch(error => {

    errors.value = error.response.data.errors
  })
}
function conAddExpensesToMainBransh(V){
  axios.post(`/api/addTransfers?amount=${V.amount??0}&sender_note=${V.note??''}`)
  .then(response => {
    window.location.reload();

    showModalAddExpensesToMainBransh.value = false;
  })
  .catch(error => {

    errors.value = error.response.data.errors
  })
}

function getImageUrl(name) {
      // Provide the base URL for your images
      return `/public/uploadsResized/${name}`;
    }
function getDownloadUrl(name) {
      // Provide the base URL for downloading images
      return `/public/uploads/${name}`;
    }
function UpdatePage (){
  refresh();
}
function updateResults(input) {
  // Ensure the input is a number
  if (typeof input !== 'number') {
    // Try converting the input to a number
    input = parseFloat(input) || 0;
  }
  
  // Use toLocaleString to format the number with commas
  return input.toLocaleString();
}

const IN_TYPES = ['in', 'inUser', 'inUserBox'];
const OUT_TYPES = ['out', 'outUser', 'outUserBox', 'debt'];

function formatAmount(tran, direction) {
  if (!tran) {
    return '';
  }
  const amount = Number(tran.amount);
  if (Number.isNaN(amount)) {
    return '';
  }
  if (direction === 'in' && IN_TYPES.includes(tran.type)) {
    return `${updateResults(Math.abs(amount))} ${tran.currency ?? '$'}`.trim();
  }
  if (direction === 'out' && OUT_TYPES.includes(tran.type)) {
    return `${updateResults(Math.abs(amount))} ${tran.currency ?? '$'}`.trim();
  }
  return '';
}

function getAmountClass(direction) {
  return direction === 'in' ? 'amount-pill amount-pill--in dark:text-white text-xl font-bold' : 'amount-pill amount-pill--out dark:text-white text-xl font-bold';
}

function getRowClasses(tran) {
  const base = [
    'transition-colors',
    'duration-150',
    'border-b',
    'border-transparent',
    'hover:shadow-md',
  ];

  if (!tran) {
    return base;
  }

  if (IN_TYPES.includes(tran.type)) {
    base.push(
      'bg-gradient-to-l',
      'from-emerald-700',
      'to-emerald-600',
      'text-white',
      'hover:from-emerald-600',
      'hover:to-emerald-500'
    );
  } else if (OUT_TYPES.includes(tran.type)) {
    base.push(
      'bg-gradient-to-l',
      'from-rose-700',
      'to-rose-600',
      'text-white',
      'hover:from-rose-600',
      'hover:to-rose-500'
    );
  } else {
    base.push(
      'bg-slate-800',
      'text-white',
      'hover:bg-slate-700'
    );
  }

  return base;
}

function getAccountLink(tran) {
  if (!tran) {
    return null;
  }

  const type = tran.morphed_type ?? '';
  const id = tran.morphed?.id ?? tran.morphed_id;
  const tranType = tran.type ?? '';

  if (!id) {
    return null;
  }

  const isBoxTransfer = tranType === 'inUserBox' || tranType === 'outUserBox';

  if (isBoxTransfer) {
    return route('wallet', { id });
  }

  if (type.includes('User')) {
    return route('showClients', { id, q: '' });
  }

  if (type.includes('Wallet')) {
    return route('wallet', { id });
  }

  return null;
}

function startEditingDescription(tran) {
  if (!tran || isSavingDescription.value) {
    return;
  }
  editingDescriptionId.value = tran.id;
  descriptionDraft.value = tran.description ?? '';
  descriptionError.value = '';
}

function cancelEditingDescription() {
  if (isSavingDescription.value) {
    return;
  }
  editingDescriptionId.value = null;
  descriptionDraft.value = '';
  descriptionError.value = '';
}

async function saveDescription(tran) {
  if (!tran || editingDescriptionId.value !== tran.id) {
    return;
  }

  const trimmed = descriptionDraft.value ? descriptionDraft.value.trim() : '';

  if (!trimmed) {
    descriptionError.value = 'الوصف مطلوب';
    return;
  }

  if (trimmed.length > DESCRIPTION_MAX_LENGTH) {
    descriptionError.value = `الوصف يجب ألا يتجاوز ${DESCRIPTION_MAX_LENGTH} حرفًا`;
    return;
  }

  isSavingDescription.value = true;
  descriptionError.value = '';

  try {
    await axios.post('/api/updateTransactionDescription', {
      transaction_id: tran.id,
      description: trimmed,
    });

    tran.description = trimmed;
    tran._descriptionUpdated = true;
    setTimeout(() => {
      if (tran) {
        tran._descriptionUpdated = false;
      }
    }, 3000);

    cancelEditingDescription();
  } catch (error) {
    if (error.response?.data?.errors?.description?.length) {
      descriptionError.value = error.response.data.errors.description[0];
    } else if (error.response?.data?.message) {
      descriptionError.value = error.response.data.message;
    } else {
      descriptionError.value = 'حدث خطأ أثناء حفظ الوصف';
    }
  } finally {
    isSavingDescription.value = false;
  }
}

// دالة للحصول على لون البرتقالي بناءً على الفهرس
function getOrangeColorClass(index) {
  const orangeColors = [
    'bg-orange-300',
    'bg-orange-400',
    'bg-orange-500',
    'bg-orange-600',
    'bg-orange-700',
    'bg-orange-800',
    'bg-orange-900'
  ];
  return orangeColors[index % orangeColors.length];
}
 
 
</script>

<template>
  <Head title="Dashboard" />
  <AuthenticatedLayout>
    <template #header>
 
    </template>
    <ModalTransferWalletTransaction
      :show="showModalTransfer"
      :transaction="tranForTransfer"
      mode="box"
      source-user-name="الصندوق"
      @saved="onTransferSaved"
      @close="showModalTransfer = false; tranForTransfer = null"
    >
      <template #header>
        <h2 class="mb-2 text-center text-lg font-semibold dark:text-white">نقل حركة الصندوق إلى قاصة</h2>
      </template>
    </ModalTransferWalletTransaction>

    <ModalDel
            :show="showModalDel ? true : false"
            :formData="tranId"
            @a="delTransactions($event)"
            @close="showModalDel = false"
            >
          <template #header>
            <h2 class=" mb-5 dark:text-white text-center">

          هل متأكد من الحذف 
          ؟
          </h2>
          </template>
    </ModalDel>
    <ModalUploader
            :show="showModalUploader ? true : false"
            :formData="tranId"
            @a="UpdatePage($event)"
            @close="showModalUploader = false"
            >
          <template #header>
            <h2 class=" mb-5 dark:text-white text-center">

            تحميل ملفات
          </h2>
          </template>
    </ModalUploader>
    

    <ModalAddGenExpenses
            :formData="formData"
            :show="showModalAddGenExpenses ? true : false"
            :expenses_type_id="expenses_type_id"
            :GenExpenses="GenExpenses"
            @a="conGenfirmExpenses($event)"
            @close="showModalAddGenExpenses = false"
            >
        <template #header>
          </template>
    </ModalAddGenExpenses>
    <ModalAddExpensesToMainBransh
            :formData="formData"
            :show="showModalAddExpensesToMainBransh ? true : false"
            :expenses_type_id="expenses_type_id"
            :allTransfers="allTransfers"
            @a="conAddExpensesToMainBransh($event)"
            @close="showModalAddExpensesToMainBransh = false"
            >
        <template #header>
          </template>
    </ModalAddExpensesToMainBransh>
    <ModalExpensesFromOtherBransh
      :formData="formData"
            :show="showModalExpensesFromOtherBransh ? true : false"
            :expenses_type_id="expenses_type_id"
            :GenExpenses="GenExpenses"
            :allTransfers="allTransfers"
            @a="conGenfirmExpenses($event)"
            @refresh="getTransfers"
            @close="showModalExpensesFromOtherBransh = false">
        <template #header>
          </template>
    </ModalExpensesFromOtherBransh>
    <ModalAddSales
            :show="showModalAddSales ? true : false"
            :data="users"
            :accounts="accounts"
            :showExtendedFields="false"
            @a="confirm($event)"
            @close="showModalAddSales = false"
            >
          <template #header>
            <h3 class="text-center">المحاسبة</h3>
            
           </template>
      </ModalAddSales>
      <ModalAddDebt
            :show="showModaldebtSales ? true : false"
            :data="users"
            :accounts="accounts"
            @a="confirmdebt($event)"
            @close="showModaldebtSales = false"
            >
          <template #header>
            
           </template>
      </ModalAddDebt>
      <ModalAddExpenses 
            :show="showModalAddExpenses ? true : false"
            :boxes="boxes"
            @a="confirmdebt($event)"
            @close="showModalAddExpenses = false"
            >
          <template #header>
            <h3 class="text-center text-gray-900 dark:text-gray-100 font-semibold">ادخال مصاريف</h3>
            
           </template>
      </ModalAddExpenses>
      <ModalConvertDollarDinar 
            :show="showModalConvertDollarDinar ? true : false"
            :boxes="boxes"
            @a="confirmConvertDollarDinar($event)"
            @close="showModalConvertDollarDinar = false"
            >
          <template #header>
            <h3 class="text-center">تحويل من الدولار الى دينار</h3>
            
           </template>
      </ModalConvertDollarDinar>
      <ModalConvertDinarDollar 
            :show="showModalConvertDinarDollar ? true : false"
            :boxes="boxes"
            @a="confirmConvertDinarDollar ($event)"
            @close="showModalConvertDinarDollar = false"
            >
          <template #header>
            <h3 class="text-center">تحويل من الدينار الى دولار</h3>
            
           </template>
      </ModalConvertDinarDollar>
    <div v-if="$page.props.success">
      <div
        id="alert-2"
        class="p-4 mb-4 bg-red-100 rounded-lg dark:bg-red-200 text-center"
        role="alert"
      >
        <div class="ml-3 font-medium text-red-700 dark:text-red-800">
          {{ $page.props.success }}
        </div>
      </div>
    </div>
    <div>
      <div class="max-w-9xl mx-auto sm:px-6 lg:px-8">
        <div class="overflow-hidden shadow-sm sm:rounded-lg">
          <!-- تبويبات المحاسبة -->
          <div class="border-b border-gray-200 dark:border-gray-700 px-2 sm:px-4 print:hidden">
            <nav class="-mb-px flex flex-wrap gap-4 sm:gap-6">
              <button
                type="button"
                @click="activeTab = 'movements'"
                :class="[
                  activeTab === 'movements'
                    ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400'
                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400',
                  'whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm',
                ]"
              >
                حركات الصندوق
              </button>
              <button
                type="button"
                @click="openCashBoxHistoryTab()"
                :class="[
                  activeTab === 'history'
                    ? 'border-teal-500 text-teal-700 dark:text-teal-400'
                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400',
                  'whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm',
                ]"
              >
                تاريخ رصيد الصندوق
              </button>
            </nav>
            <p
              v-if="activeTab === 'history'"
              class="mt-2 mb-3 text-sm text-gray-600 dark:text-gray-300 leading-relaxed max-w-3xl"
            >
              يُستخدم لمطابقة رصيد الصندوق مع الكاش الفعلي وتوثيق الحركات.
              عند توثيق صف، تصبح كل الحركات السابقة حتى تلك النقطة موثوقة.
              لا يُحمَّل هذا السجل إلا عند فتح التبويب.
            </p>
          </div>

          <div v-show="activeTab === 'movements'" class="border-b border-gray-200">
          

            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-3 lg:gap-3">
              <div class="pt-5  print:hidden">
              <button style=" width: 100%; margin-top: 4px;" v-if="$page.props.auth.user.type_id==1 || $page.props.auth.user.type_id==2 || $page.props.auth.user.type_id==5|| $page.props.auth.user.type_id==6" className="px-4 py-2 text-white bg-green-500 rounded-md focus:outline-none"
                                            @click="openAddSales()">
                                            وصل قبض
                                            (أضافة)
              </button>
              </div>

              <div class="pt-5  hidden">
              <button style=" width: 100%; margin-top: 4px;"  v-if="$page.props.auth.user.type_id==1 || $page.props.auth.user.type_id==2|| $page.props.auth.user.type_id==5" className="px-4 py-2 text-white bg-yellow-500 rounded-md focus:outline-none"
                                            @click="opendebtSales()">
                                             تحويل لحساب 
              </button>
              </div>
              
              <div class="pt-5  print:hidden">
              <button  style=" width: 100%; margin-top: 4px;"  v-if="$page.props.auth.user.type_id==1 || $page.props.auth.user.type_id==2|| $page.props.auth.user.type_id==5|| $page.props.auth.user.type_id==6" className="px-4 py-2 text-white bg-rose-500 rounded-md focus:outline-none"
                                            @click="openAddExpenses()">
                                             وصل صرف
                                             (سحب)

              </button>
       
              </div>
              
              <div class=" px-4">
                          <div >
                              <InputLabel for="from" value="من تاريخ" />
                              <TextInput
                                id="from"
                                type="date"
                                class="mt-1 block w-full"
                                v-model="from"
                                
                              />
                            </div>
              </div>
              <div class=" px-4">
                            <div >
                              <InputLabel for="to" value="حتى تاريخ" />
                              <TextInput
                                id="to"
                                type="date"
                                class="mt-1 block w-full"
                                v-model="to"
                              />
                            </div>
              </div>
              <div class=" mr-5 print:hidden">
                            <InputLabel for="pay" value="فلترة" />
                            <button
                            @click.prevent="refresh()"
                            class="px-6 mb-6 py-2 mt-1 font-bold text-white bg-gray-500 rounded" style="width: 100%">
                            <span v-if="!isLoading">فلترة</span>
                            <span v-else>جاري الحفظ...</span>
                          </button>
              </div>
              <div class=" mr-5 print:hidden" v-if="$page.props.auth.user.type_id==1 || $page.props.auth.user.type_id==2 || $page.props.auth.user.type_id==5">
                            <InputLabel for="deleted_payments" value="محذوف" />
                            <button
                              type="button"
                              @click.prevent="toggleDeletedPayments()"
                              class="px-6 mb-6 py-2 mt-1 font-bold text-white rounded w-full"
                              :class="deletedOnly ? 'bg-amber-600 hover:bg-amber-700' : 'bg-slate-700 hover:bg-slate-800'"
                            >
                              {{ deletedOnly ? 'إخفاء المحذوف' : 'الدفعات المحذوفة' }}
                            </button>
              </div>
              <div v-if="showAccountingExtraButtons" class=" mr-5 print:hidden" >
                            <InputLabel for="pay" value="قاسه" />
                           <Link
                          v-if="$page.props.auth.user.owner_id==1"
                            type="button"
                          href="/wallet?id=2756"
                            style="min-width:150px;"
                            class="px-6 mb-6 py-2 font-bold text-white bg-orange-800 rounded  w-full mt-1 text-center">
                               حولات حاجى عمران
                          </Link>
              </div>

              <div v-if="showAccountingExtraButtons" class="mr-5">
                <InputLabel for="to" value="مصاريف" />
                          <Link
                          v-if="$page.props.auth.user.owner_id==1"
                            type="button"
                           href="/wallet?id=588"
                            style="min-width:150px;"
                            class="px-6 mb-6 py-2 font-bold text-white bg-red-500 rounded  w-full mt-1 text-center">
                              مصاريف الشركة
                          </Link>
              </div>

              
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-3 lg:gap-3">
              <div v-if="showKirkukTransfers || $page.props.auth.user.type_id==6">
                          <button
                          v-if="showKirkukTransfers && $page.props.auth.user.owner_id==1"
                            type="button"
                            @click="openModalExpensesFromOtherBransh(1)"
                            style="min-width:150px;"
                            className="px-6 mb-6 py-2 font-bold text-white bg-red-500 rounded  w-full">
                              تحويلات كركوك
                          </button>
                          <button
                            v-if="$page.props.auth.user.type_id==6"
                            type="button"
                            @click="openModalAddExpensesToMainBransh(1)"
                            style="min-width:150px;"
                            className="px-6 mb-6 py-2 font-bold text-white bg-red-500 rounded  w-full">
                              تحويل لفرع أربيل
                          </button>
                        </div>
                        <div v-if="showAccountingExtraButtons && $page.props.auth.user.type_id==1">
                          <button
                            type="button"
                            @click="openAddGenExpenses(2)"
                            style="min-width:150px;"
                            className="px-6 mb-6 text-center py-2 font-bold text-white bg-blue-600 rounded  w-full">
                            {{ $t('dubai') }}
                          </button>
                        </div>
                        <div v-if="showAccountingExtraButtons && $page.props.auth.user.type_id==1">
                          <button
                            type="button"
                            @click="openAddGenExpenses(3)"
                            style="min-width:150px;"
                            className="px-6 mb-6 text-center w-full py-2 font-bold text-white bg-blue-600 rounded">
                            {{ $t('iran') }}
                          </button>
                        </div>
                       <div v-if="showAccountingExtraButtons && $page.props.auth.user.type_id==1">
                          <button
                            type="button"
                            @click="openAddGenExpenses(4)"
                            style="min-width:150px;"
                            className="px-6 mb-6 w-full py-2 font-bold text-white bg-indigo-600 rounded">
                            {{ $t('border') }} 
                          </button>
                        </div> 
                        <div v-if="showAccountingExtraButtons && $page.props.auth.user.type_id==1">
                          <button
                            type="button"
                            @click="openAddGenExpenses(5)"
                            style="min-width:150px;"
                            className="px-6 mb-6 w-full py-2 font-bold text-white bg-pink-600 rounded">
                            {{ $t('shipping_coc') }} 
                          </button>
                        </div>
                        <div>
                          <button
                            type="button"
                            @click="openConvertDollarDinar()"
                            style="min-width:150px;"
                            className="px-6 mb-6 w-full py-2 font-bold text-white bg-teal-500 rounded">
                             تحويل دولار الى دينار  
                          </button>
                        </div>
                        <div>
                          <button
                            type="button"
                            @click="openConvertDinarDollar()"
                            style="min-width:150px;"
                            className="px-6 mb-6 w-full py-2 font-bold text-white bg-yellow-500 rounded">
                             تحويل دينار الى دولار  
                          </button>
                        </div>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-3 lg:gap-3" v-if="flaggedWallets && flaggedWallets.length > 0">
              <div v-for="(wallet, index) in flaggedWallets" :key="wallet.id">
                <Link
                  v-if="$page.props.auth.user.owner_id==1"
                  type="button"
                  :href="`/wallet?id=${wallet.id}`"
                  style="min-width:150px;"
                  :class="`px-6 mb-6 py-2 font-bold text-white ${getOrangeColorClass(index)} rounded w-full mt-1 text-center`">
                  {{ wallet.name }}
                </Link>
              </div>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-4 gap-3 lg:gap-3">
             


              <div class=" px-4">
                              <InputLabel for="to" value="رصيد الصندوق بالدولار (من دفتر القيود)" />
                              <TextInput
                                id="to"
                                type="number"
                                disabled
                                class="mt-1 block w-full"
                                :value="cashBoxBalanceDollar"
                              />
              </div>
              <div class=" px-4">
                              <InputLabel for="to" value="رصيد الصندوق بالدينار العراقي (من دفتر القيود)" />
                              <TextInput
                                id="to"
                                type="number"
                                disabled
                                class="mt-1 block w-full"
                                :value="cashBoxBalanceDinar"
                              />
              </div>
              <div class="relative w-full px-4">
                          <InputLabel for="to" value="بحث رقم الوصل او الوصف" />
                          <TextInput
                                id="q"
                                type="text"
                                class="mt-1 block w-full"
                                v-model="q"
                                @input="debouncedGetResultsCar"                              />
             
              </div>
               <div class="relative w-full px-4">
                          <table class="w-full text-sm text-center text-gray-100 border border-gray-700 rounded-lg overflow-hidden bg-slate-900">
                            <thead class="bg-slate-800 text-gray-100">
                              <tr>
                                <th class="border p-2">العملة</th>
                                <th class="border p-2">الدخل</th>
                                <th class="border p-2">الخروج</th>
                                <th class="border p-2">الفرق</th>
                              </tr>
                            </thead>
                            <tbody>
                              <!-- دولار -->
                              <tr class="bg-slate-900">
                                <td class="border border-gray-700 p-2 font-bold text-emerald-300">دولار</td>
                                <td class="border border-gray-700 p-2 text-emerald-200 font-semibold">{{updateResults(transactionInTodayDollar)}}</td>
                                <td class="border border-gray-700 p-2 text-rose-200 font-semibold">{{updateResults(transactionOutTodayDollar)}}</td>
                                <td class="border border-gray-700 p-2 font-semibold">
                                  <span :class="(transactionInTodayDollar + transactionOutTodayDollar) > 0 ? 'text-emerald-300' : 'text-rose-300'" >{{updateResults(transactionInTodayDollar + transactionOutTodayDollar)}}</span>
                                </td>
                              </tr>
                              <!-- دينار -->
                              <tr class="bg-slate-900">
                                <td class="border border-gray-700 p-2 font-bold text-indigo-300">دينار</td>
                                <td class="border border-gray-700 p-2 text-emerald-200 font-semibold">{{updateResults(transactionInTodayDinar)}}</td>
                                <td class="border border-gray-700 p-2 text-rose-200 font-semibold">{{updateResults(transactionOutTodayDinar)}}</td>
                                <td class="border border-gray-700 p-2 font-semibold">
                                  <span :class="(transactionInTodayDinar + transactionOutTodayDinar) > 0 ? 'text-emerald-300' : 'text-rose-300'">{{updateResults(transactionInTodayDinar + transactionOutTodayDinar)}}</span>
                                </td>
                              </tr>
                            </tbody>
                          </table>
                        </div>

            </div>
         
         <div v-if="deletedOnly" class="mt-4 mx-2 px-4 py-2 rounded bg-amber-100 text-amber-900 dark:bg-amber-900/40 dark:text-amber-100 text-sm font-semibold">
              عرض الدفعات المحذوفة فقط — يمكنك استرجاع أي دفعة من عمود التنفيذ
            </div>
         <div class="overflow-x-auto shadow-lg mt-5 rounded-lg">
              <table class="w-full text-right text-gray-100 dark:text-gray-100 text-center bg-slate-900 rounded-lg overflow-hidden">
                <thead class="uppercase bg-slate-800 text-gray-100 text-center">
                  <tr class="rounded-l-lg mb-2 sm:mb-0">
                    <th className="px-2 py-2" style="width: 200px;">حساب
                    </th>
                    <!-- <th className="px-2 py-2">الحساب</th> -->
                    <th className="px-2 py-2" style="width: 180px;">{{ deletedOnly ? 'تاريخ الحذف' : 'التاريخ' }}</th>
                    <th className="px-2 py-2">الوصف</th>
                    <th className="px-2 py-2">ايداع</th>
                    <th className="px-2 py-2">سحب</th>
                    <th className="px-2 py-2" style="width: 200px;">تنفيذ</th>
                    <th
                      scope="col"
                      class="px-1 py-2 text-base print:hidden" style="width: 100px;"
                    >
                      تخزين
                    </th>
                  </tr>
                </thead>
                <tbody>
         
                  <tr
                    v-for="tran in transactions"
                    :key="tran.id"
                    :class="getRowClasses(tran)"
                  >
                    <td class="border border-transparent text-center px-2 py-1 whitespace-nowrap">
                      <Link
                        v-if="getAccountLink(tran)"
                        :href="getAccountLink(tran)"
                        class="account-link"
                      >
                        {{ tran.morphed?.name ?? '—' }}
                      </Link>
                      <span v-else>
                        {{ tran.morphed?.name ?? '—' }}
                      </span>
                    </td>

                    <td class="border border-transparent text-center px-2 py-1 whitespace-nowrap">
                      {{ formatBaghdadTimestamp(deletedOnly ? tran?.deleted_at : tran?.created_at) }}
                    </td>
                    <th class="border border-transparent text-center px-2 py-1 align-top">
                    <div v-if="editingDescriptionId === tran.id" class="space-y-2 text-right">
                      <textarea
                        v-model="descriptionDraft"
                        class="w-full rounded border border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm leading-6 p-2"
                        rows="3"
                        :maxlength="DESCRIPTION_MAX_LENGTH"
                        placeholder="اكتب الوصف الجديد هنا"
                      ></textarea>
                      <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                        <span>الحد الأقصى {{ DESCRIPTION_MAX_LENGTH }} حرفًا</span>
                        <span :class="descriptionDraft.length > DESCRIPTION_MAX_LENGTH ? 'text-red-500' : ''">
                          {{ descriptionDraft.length }}/{{ DESCRIPTION_MAX_LENGTH }}
                        </span>
                      </div>
                      <p v-if="descriptionError" class="text-xs text-red-500">{{ descriptionError }}</p>
                      <div class="flex justify-end gap-2">
                        <button
                          type="button"
                          class="px-3 py-1 text-sm font-semibold rounded bg-gray-200 text-gray-800 dark:bg-gray-700 dark:text-gray-200"
                          @click="cancelEditingDescription"
                          :disabled="isSavingDescription"
                        >
                          إلغاء
                        </button>
                        <button
                          type="button"
                          class="px-3 py-1 text-sm font-semibold text-white bg-green-600 rounded disabled:opacity-70"
                          @click="saveDescription(tran)"
                          :disabled="isSavingDescription"
                        >
                          <span v-if="isSavingDescription">جارٍ الحفظ...</span>
                          <span v-else>حفظ</span>
                        </button>
                      </div>
                    </div>
                    <div v-else class="space-y-1 text-center">
                      <span class="block whitespace-pre-line leading-6 pt-5">{{ tran.description }}</span>
                      <span
                        v-if="tran._descriptionUpdated"
                        class="inline-flex items-center text-xs font-semibold text-green-600"
                      >
                        تم التحديث
                      </span>
                    </div>
                  </th>
                    <td class="border border-transparent text-center px-2 py-1">
                      <span v-if="formatAmount(tran, 'in')" :class="getAmountClass('in')">
                        {{ formatAmount(tran, 'in') }}
                      </span>
                    </td>
                    <td class="border border-transparent text-center px-2 py-1">
                      <span v-if="formatAmount(tran, 'out')" :class="getAmountClass('out')">
                        {{ formatAmount(tran, 'out') }}
                      </span>
                    </td>
                    <td class="border border-transparent text-center px-2 py-1">
                      <div class="action-group">
                        <template v-if="deletedOnly">
                          <button
                            type="button"
                            class="action-btn bg-emerald-600 hover:bg-emerald-700"
                            title="استرجاع الدفعة"
                            :disabled="restoringId === tran.id"
                            @click="restoreDeletedTransaction(tran)"
                          >
                            {{ restoringId === tran.id ? '...' : 'استرجاع' }}
                          </button>
                        </template>
                        <template v-else>
                        <button
                          class="action-btn action-btn--edit"
                          title="تعديل الوصف"
                          @click="startEditingDescription(tran)"
                          :disabled="isSavingDescription && editingDescriptionId === tran.id"
                        >
                          <edit class="w-4 h-4" />
                        </button>
                        <button
                          v-if="canTransferBoxTransaction(tran)"
                          class="action-btn action-btn--transfer"
                          title="نقل"
                          aria-label="نقل"
                          @click="openModalTransfer(tran)"
                        >
                          <transfer />
                        </button>
                        <button class="action-btn action-btn--delete" @click="openModalDel(tran)" title="حذف الحركة">
                          <trash />
                        </button>
                        <button class="action-btn action-btn--upload" @click="openModalUploader(tran)" title="مرفقات الحركة">
                          <imags />
                        </button>
                        <a
                          v-if="tran.type === 'out' || tran.type === 'outUser' || tran.type === 'debt'"
                          :href="`/api/getIndexAccountsSelas?user_id=${boxes[0].id}&print=2&transactions_id=${tran.id}`"
                          target="_blank"
                          class="action-btn action-btn--print"
                          title="طباعة سند الصرف"
                        >
                          <print class="inline-flex" />
                        </a>
                        <a
                          v-if="tran.type === 'in' || tran.type === 'inUser'"
                          :href="`/api/getIndexAccountsSelas?user_id=${boxes[0].id}&print=3&transactions_id=${tran.id}`"
                          target="_blank"
                          class="action-btn action-btn--print"
                          title="طباعة سند القبض"
                        >
                          <print class="inline-flex" />
                        </a>
                        <a
                          v-if="tran.type === 'inUserBox'"
                          :href="`/api/getIndexAccountsSelas?user_id=${tran.morphed_id}&print=3&transactions_id=${tran.id}`"
                          target="_blank"
                          class="action-btn action-btn--print"
                          title="طباعة سند القبض"
                        >
                          <print class="inline-flex" />
                        </a>
                        <a
                          v-if="tran.type === 'outUserBox'"
                          :href="`/api/getIndexAccountsSelas?user_id=${tran.morphed_id}&print=2&transactions_id=${tran.id}`"
                          target="_blank"
                          class="action-btn action-btn--print"
                          title="طباعة سند الصرف"
                        >
                          <print class="inline-flex" />
                        </a>
                        </template>
                      </div>
                   </td>
                  <td>
                    <a
                      v-for="(image, index) in (tran.transactions_images || [])"
                      :key="index"
                      :href="getDownloadUrl(image.name)"
                      style="cursor: pointer;"
                      target="_blank">
                      <img :src="getImageUrl(image.name)" alt="" class="px-1" style="max-width: 80px;max-height: 50px;display: inline;" />
                    </a>
                  </td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div class="spaner">
                          <InfiniteLoading :transactions="transactions" @infinite="getResults" :identifier="resetData" />
            </div>
          </div>

          <!-- تبويب تاريخ رصيد الصندوق (lazy) -->
          <div v-if="activeTab === 'history'" class="p-4 space-y-4">
            <div
              v-if="historyMeta.current_ledger"
              class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3"
            >
              <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-slate-900 p-3">
                <div class="text-xs text-gray-500 dark:text-gray-400">رصيد الدفتر ($)</div>
                <div class="text-lg font-bold text-emerald-700 dark:text-emerald-300" dir="ltr">
                  {{ historyMeta.current_ledger.ledger_balance }}
                </div>
              </div>
              <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-slate-900 p-3">
                <div class="text-xs text-gray-500 dark:text-gray-400">رصيد الدفتر (د.ع)</div>
                <div class="text-lg font-bold text-indigo-700 dark:text-indigo-300" dir="ltr">
                  {{ historyMeta.current_ledger.ledger_balance_dinar }}
                </div>
              </div>
              <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-slate-900 p-3 sm:col-span-2">
                <div class="text-xs text-gray-500 dark:text-gray-400">آخر توثيق مطابقة للكاش</div>
                <div v-if="historyMeta.latest_verification" class="text-sm text-gray-800 dark:text-gray-100 mt-1">
                  حتى الحركة
                  <span class="font-mono font-semibold" dir="ltr">#{{ historyMeta.latest_verification.transaction_id }}</span>
                  —
                  رصيد عند التوثيق:
                  <span dir="ltr">${{ historyMeta.latest_verification.ledger_balance_at_confirm }}</span>
                  /
                  <span dir="ltr">{{ historyMeta.latest_verification.ledger_balance_dinar_at_confirm }} د.ع</span>
                  <span v-if="historyMeta.latest_verification.verified_by_name" class="block text-xs text-gray-500 mt-1">
                    بواسطة {{ historyMeta.latest_verification.verified_by_name }}
                    —
                    {{ formatBaghdadTimestamp(historyMeta.latest_verification.verified_at) }}
                  </span>
                </div>
                <div v-else class="text-sm text-amber-700 dark:text-amber-300 mt-1">
                  لا يوجد توثيق بعد — كل الحركات غير موثوقة حتى يؤكد المسؤول نقطة مطابقة.
                </div>
              </div>
            </div>

            <div v-if="historyError" class="rounded bg-rose-100 dark:bg-rose-900/40 text-rose-800 dark:text-rose-200 px-4 py-2 text-sm">
              {{ historyError }}
            </div>
            <div v-if="historyLoading && !historyRows.length" class="text-center text-gray-500 py-8">
              جاري تحميل تاريخ الرصيد…
            </div>

            <div class="overflow-x-auto shadow-lg rounded-lg">
              <table class="w-full text-right text-gray-100 text-center bg-slate-900 rounded-lg overflow-hidden text-sm">
                <thead class="uppercase bg-slate-800 text-gray-100">
                  <tr>
                    <th class="px-2 py-2">#</th>
                    <th class="px-2 py-2">التاريخ</th>
                    <th class="px-2 py-2">الوصف</th>
                    <th class="px-2 py-2">النوع</th>
                    <th class="px-2 py-2">المبلغ</th>
                    <th class="px-2 py-2">الرصيد الجاري $</th>
                    <th class="px-2 py-2">الرصيد الجاري د.ع</th>
                    <th class="px-2 py-2">الحالة</th>
                    <th class="px-2 py-2 print:hidden">توثيق</th>
                  </tr>
                </thead>
                <tbody>
                  <tr
                    v-for="row in historyRows"
                    :key="'hist-' + row.id"
                    :class="row.is_verification_point
                      ? 'bg-teal-950/50'
                      : (row.is_trusted ? 'bg-emerald-950/20' : 'bg-slate-900')"
                  >
                    <td class="border border-transparent px-2 py-1 font-mono" dir="ltr">{{ row.id }}</td>
                    <td class="border border-transparent px-2 py-1 whitespace-nowrap">
                      {{ formatBaghdadTimestamp(row.created_at) }}
                    </td>
                    <td class="border border-transparent px-2 py-1 text-right max-w-xs truncate" :title="row.description">
                      {{ row.description || '—' }}
                      <span v-if="row.morphed_name" class="block text-xs text-gray-400">{{ row.morphed_name }}</span>
                    </td>
                    <td class="border border-transparent px-2 py-1">
                      <span
                        :class="row.direction === 'in'
                          ? 'text-emerald-300 font-semibold'
                          : 'text-rose-300 font-semibold'"
                      >
                        {{ row.direction === 'in' ? 'إيداع' : 'سحب' }}
                      </span>
                      <span class="block text-xs text-gray-400">{{ row.type }}</span>
                    </td>
                    <td class="border border-transparent px-2 py-1 font-semibold" dir="ltr">
                      <span :class="row.direction === 'in' ? 'text-emerald-300' : 'text-rose-300'">
                        {{ row.amount }}
                      </span>
                      <span class="text-xs text-gray-400 ms-1">{{ row.currency }}</span>
                    </td>
                    <td class="border border-transparent px-2 py-1 font-mono" dir="ltr">
                      {{ row.running_balance }}
                    </td>
                    <td class="border border-transparent px-2 py-1 font-mono" dir="ltr">
                      {{ row.running_balance_dinar }}
                    </td>
                    <td class="border border-transparent px-2 py-1">
                      <span
                        v-if="row.is_trusted"
                        class="inline-block px-2 py-0.5 rounded text-xs font-bold bg-emerald-700/40 text-emerald-200"
                      >
                        موثوق
                      </span>
                      <span
                        v-else
                        class="inline-block px-2 py-0.5 rounded text-xs font-bold bg-amber-700/40 text-amber-200"
                      >
                        غير موثوق
                      </span>
                      <span
                        v-if="row.is_verification_point"
                        class="block mt-1 text-[10px] text-teal-300 font-semibold"
                      >
                        نقطة التوثيق الحالية
                      </span>
                    </td>
                    <td class="border border-transparent px-2 py-1 print:hidden">
                      <button
                        v-if="(historyMeta.can_verify || isAdminUser) && !row.is_trusted"
                        type="button"
                        class="px-3 py-1.5 text-xs font-bold rounded bg-teal-600 hover:bg-teal-500 text-white"
                        @click="openVerifyModal(row)"
                      >
                        تأكيد المطابقة
                      </button>
                      <span v-else class="text-xs text-gray-500">—</span>
                    </td>
                  </tr>
                  <tr v-if="!historyLoading && historyLoadedOnce && !historyRows.length">
                    <td colspan="9" class="py-8 text-gray-400">لا توجد حركات صندوق لعرضها</td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div class="flex justify-center gap-3 pb-4 print:hidden">
              <button
                v-if="historyHasMore || (historyLoading && historyRows.length)"
                type="button"
                class="px-6 py-2 rounded bg-slate-700 text-white font-semibold disabled:opacity-50"
                :disabled="historyLoading"
                @click="loadMoreHistory()"
              >
                {{ historyLoading ? 'جاري التحميل…' : 'تحميل المزيد' }}
              </button>
              <button
                type="button"
                class="px-6 py-2 rounded bg-gray-500 text-white font-semibold disabled:opacity-50"
                :disabled="historyLoading"
                @click="loadCashBoxHistory(true)"
              >
                تحديث
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- نافذة تأكيد مطابقة الكاش -->
    <Modal :show="showVerifyModal" @close="closeVerifyModal">
      <template #header>
        <h3 class="text-center text-lg font-semibold text-gray-900 dark:text-gray-100">
          تأكيد مطابقة الرصيد مع الكاش الفعلي
        </h3>
      </template>
      <template #body>
        <div class="space-y-3 text-sm text-gray-800 dark:text-gray-200 text-right leading-relaxed">
          <p>
            سيتم اعتبار الحركة
            <span class="font-mono font-bold" dir="ltr">#{{ verifyTarget?.id }}</span>
            نقطة مطابقة مع الكاش الحقيقي.
          </p>
          <p class="text-amber-800 dark:text-amber-200 font-semibold">
            كل حركات الصندوق السابقة حتى هذه النقطة (بما فيها) ستصبح موثوقة.
            الحركات الأحدث تبقى غير موثوقة حتى توثيق لاحق.
          </p>
          <div v-if="verifyTarget" class="rounded border border-gray-200 dark:border-gray-700 p-3 bg-gray-50 dark:bg-slate-800 text-xs space-y-1">
            <div>الوصف: {{ verifyTarget.description || '—' }}</div>
            <div dir="ltr">المبلغ: {{ verifyTarget.amount }} {{ verifyTarget.currency }}</div>
            <div dir="ltr">الرصيد الجاري بعد الحركة: ${{ verifyTarget.running_balance }} / {{ verifyTarget.running_balance_dinar }} IQD</div>
          </div>
          <div>
            <InputLabel for="verify_note" value="ملاحظة (اختياري)" />
            <TextInput
              id="verify_note"
              type="text"
              class="mt-1 block w-full"
              v-model="verifyNote"
              maxlength="1000"
              placeholder="مثال: جرد صندوق صباح 22/8"
            />
          </div>
          <p v-if="verifyError" class="text-rose-600 dark:text-rose-300 text-sm">{{ verifyError }}</p>
        </div>
      </template>
      <template #footer>
        <div class="flex flex-row w-full">
          <div class="basis-1/2 px-2">
            <button
              type="button"
              class="w-full py-3 bg-gray-500 text-white rounded"
              :disabled="verifySubmitting"
              @click="closeVerifyModal"
            >
              إغلاق
            </button>
          </div>
          <div class="basis-1/2 px-2">
            <button
              type="button"
              class="w-full py-3 bg-teal-600 text-white rounded font-bold disabled:opacity-50"
              :disabled="verifySubmitting"
              @click="confirmVerify"
            >
              {{ verifySubmitting ? 'جاري التأكيد…' : 'تأكيد' }}
            </button>
          </div>
        </div>
      </template>
    </Modal>
  </AuthenticatedLayout>
</template>

<style>
.td {
  max-width: 200px; /* can be 100% ellipsis will happen when contents exceed it */
  text-overflow: ellipsis;
  overflow: hidden;
  white-space: nowrap;
}

.amount-pill {
  display: inline-block;
  padding: 0.25rem 0.6rem;
  border-radius: 9999px;
  font-weight: 600;
  font-size: 0.875rem;
}

.amount-pill--in {
  background-color: rgba(16, 185, 129, 0.15);
  color: #047857;
}

.amount-pill--out {
  background-color: rgba(239, 68, 68, 0.15);
  color: #b91c1c;
}

.action-group {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  align-items: center;
  gap: 0.35rem;
}

.action-btn {
  width: 2.25rem;
  height: 2.25rem;
  border-radius: 0.5rem;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  color: #fff;
  transition: transform 0.2s ease, filter 0.2s ease;
}

.action-btn:hover {
  transform: translateY(-1px);
  filter: brightness(1.05);
}

.action-btn:disabled,
.action-btn[disabled] {
  opacity: 0.5;
  cursor: not-allowed;
}

.action-btn--edit {
  background: linear-gradient(135deg, #3b82f6, #2563eb);
}

.action-btn--delete {
  background: linear-gradient(135deg, #f43f5e, #e11d48);
}

.action-btn--upload {
  background: linear-gradient(135deg, #8b5cf6, #7c3aed);
}

.action-btn--print {
  background: linear-gradient(135deg, #22c55e, #16a34a);
}

.action-btn--transfer {
  background: linear-gradient(135deg, #6366f1, #4f46e5);
}

.account-link {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 0.35rem 0.9rem;
  border-radius: 999px;
  font-weight: 700;
  color: #f8fafc;
  background: linear-gradient(135deg, rgba(59, 130, 246, 0.35), rgba(14, 165, 233, 0.45));
  border: 1px solid rgba(148, 163, 184, 0.45);
  box-shadow: 0 10px 24px -14px rgba(14, 116, 144, 0.7);
  transition: transform 0.15s ease, box-shadow 0.15s ease, filter 0.15s ease;
}

.account-link:hover {
  transform: translateY(-1px);
  box-shadow: 0 18px 32px -18px rgba(59, 130, 246, 0.7);
  filter: brightness(1.08);
}
</style>
<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Modal from "@/Components/Modal.vue";
import ModalEditInternalSale from "@/Components/ModalEditInternalSale.vue";
import ModalAddPayment from "@/Components/ModalAddPayment.vue";
import ModalPaymentDetails from "@/Components/ModalPaymentDetails.vue";
import ModalBulkEditInternalSales from "@/Components/ModalBulkEditInternalSales.vue";
import { Head, Link } from "@inertiajs/inertia-vue3";
import { onMounted, ref, computed } from 'vue';
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import axios from "axios";
import trash from "@/Components/icon/trash.vue";
import edit from "@/Components/icon/edit.vue";
import { useToast } from "vue-toastification";
import { ModelListSelect } from "vue-search-select";
import "vue-search-select/dist/VueSearchSelect.css";

let toast = useToast();

// Props
const props = defineProps({
  url: String,
  client_id: String,
  client: Object,
  clients: Array,
});

// Refs
let showAddInternalSaleForm = ref(false);
let showModalEditInternalSale = ref(false);
let internalSalesData = ref([]);
let internalSalesTotals = ref({});
let hasInternalSales = ref(false);
let isLoadingInternalSales = ref(false);
let unsoldCars = ref([]);
let isLoadingUnsoldCars = ref(false);
let allClients = ref([]);
let isLoadingClients = ref(false);
let showNewClientForm = ref(false);
let internalSalesBuyers = ref([]);
let isLoadingBuyers = ref(false);
let internalSalesClient = ref(null);
let internalSaleForm = ref({});
let saleToEdit = ref(null);
let selectedSales = ref([]);
let showBulkEditModal = ref(false);
let showAddPaymentModal = ref(false);
let showPaymentDetailsModal = ref(false);
let selectedBuyer = ref(null);
let isLoadingPayment = ref(false);
let showBulkSaleModal = ref(false);
let bulkSaleForm = ref({
  client_id: null,
  client_name: '',
  client_phone: '',
  cars: [],
  sale_date: '',
  showNewClientForm: false
});
let searchTerm = ref('');
let searchUnsoldCars = ref('');

const currentClientId = computed(() => {
  return props.client_id || props.client?.id;
});

// إحصائيات المبيعات الداخلية
const salesStatistics = computed(() => {
  const sales = internalSalesData.value;
  
  // تجميع نوع السيارات وعدد المبيعات
  const carTypesStats = {};
  sales.forEach(sale => {
    const carType = sale.car?.car_type || 'غير محدد';
    if (!carTypesStats[carType]) {
      carTypesStats[carType] = 0;
    }
    carTypesStats[carType]++;
  });
  
  // حساب الدين الإجمالي
  const totalDebt = internalSalesBuyers.value.reduce((sum, buyer) => {
    return sum + (buyer.remaining_debt > 0 ? buyer.remaining_debt : 0);
  }, 0);
  
  // مجموع المبيعات
  const totalSales = internalSalesTotals.value.total_sales || 0;
  
  // مجموع المصاريف
  const totalExpenses = (internalSalesTotals.value.total_expenses || 0) + 
                       sales.reduce((sum, sale) => sum + (parseFloat(sale.additional_expenses) || 0), 0);
  
  // الربح
  const totalProfit = internalSalesTotals.value.total_profit || 0;
  
  return {
    carTypes: Object.entries(carTypesStats).map(([type, count]) => ({ type, count })),
    totalDebt,
    totalSales,
    totalExpenses,
    totalProfit,
    totalCarsSold: sales.length
  };
});

const availableCars = computed(() => {
  if (unsoldCars.value.length > 0) {
    return unsoldCars.value.map(car => ({
      value: car.id,
      text: `${car.car_type || ''} ${car.year || ''} - ${car.vin || car.car_number || 'بدون رقم'}`,
      vin: car.vin || '',
      car_number: car.car_number || '',
      car_type: car.car_type || '',
      year: car.year || '',
      total_s: car.total_s || 0,
      total: car.total || 0,
      car_price: car.car_price || car.total || 0,
      expenses: car.expenses || 0
    }));
  }
  return [];
});

const availableClients = computed(() => {
  if (allClients.value.length > 0) {
    return allClients.value.map(client => ({
      value: client.id,
      text: `${client.name || ''}${client.phone ? ' - ' + client.phone : ''}`,
      name: client.name || '',
      phone: client.phone || ''
    }));
  }
  return [];
});

// تصفية المبيعات بناءً على البحث
const filteredInternalSales = computed(() => {
  if (!searchTerm.value || searchTerm.value.trim() === '') {
    return internalSalesData.value;
  }
  
  const search = searchTerm.value.toLowerCase().trim();
  
  return internalSalesData.value.filter(sale => {
    // البحث في رقم الشاصي (VIN)
    const vin = (sale.car?.vin || '').toLowerCase();
    // البحث في رقم السيارة (car_number)
    const carNumber = String(sale.car?.car_number || '').toLowerCase();
    // البحث في نوع السيارة
    const carType = (sale.car?.car_type || '').toLowerCase();
    // البحث في سنة السيارة
    const year = (sale.car?.year || '').toString().toLowerCase();
    // البحث في اسم الزبون
    const clientName = (sale.client?.name || '').toLowerCase();
    // البحث في رقم هاتف الزبون
    const clientPhone = (sale.client?.phone || '').toLowerCase();
    
    return vin.includes(search) || 
           carNumber.includes(search) || 
           carType.includes(search) || 
           year.includes(search) ||
           clientName.includes(search) ||
           clientPhone.includes(search);
  });
});

// تصفية السيارات المتاحة للبيع بناءً على البحث
const filteredUnsoldCars = computed(() => {
  if (!searchUnsoldCars.value || searchUnsoldCars.value.trim() === '') {
    return unsoldCars.value;
  }
  
  const search = searchUnsoldCars.value.toLowerCase().trim();
  
  return unsoldCars.value.filter(car => {
    // البحث في رقم الشاصي (VIN)
    const vin = (car.vin || '').toLowerCase();
    // البحث في رقم السيارة (car_number)
    const carNumber = String(car.car_number || '').toLowerCase();
    // البحث في نوع السيارة
    const carType = (car.car_type || '').toLowerCase();
    // البحث في سنة السيارة
    const year = (car.year || '').toString().toLowerCase();
    
    return vin.includes(search) || 
           carNumber.includes(search) || 
           carType.includes(search) || 
           year.includes(search);
  });
});

function onCarSelected() {
  const selectedCar = availableCars.value.find(c => c.value === internalSaleForm.value.car_id);
  if (selectedCar) {
    // رأس المال = تكلفة الشراء (total) من بيانات السيارة
    internalSaleForm.value.car_price = selectedCar.car_price || selectedCar.total || 0;
    internalSaleForm.value.shipping = 0;
    const totalS = selectedCar.total_s;
    
    // المصاريف = مصاريف السيارة (شحن، كوك، إلخ) وليس سعر البيع
    internalSaleForm.value.expenses = selectedCar.expenses || 0;
  }
}

function getTodayDate() {
  const today = new Date();
  const year = today.getFullYear();
  const month = String(today.getMonth() + 1).padStart(2, '0');
  const day = String(today.getDate()).padStart(2, '0');
  return `${year}-${month}-${day}`;
}

function formatSaleDate(dateString) {
  if (!dateString) return '-';
  try {
    if (typeof dateString === 'string' && dateString.includes('T')) {
      return dateString.split('T')[0];
    }
    const date = new Date(dateString);
    if (isNaN(date.getTime())) return dateString;
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
  } catch (error) {
    return dateString;
  }
}

function formatNumber(value) {
  if (value === null || value === undefined) return '0';
  const num = typeof value === 'number' ? value : parseFloat(value) || 0;
  const formatted = num.toFixed(2);
  // إزالة .00 إذا كان الرقم صحيحاً
  return formatted.replace(/\.00$/, '');
}

// Load Unsold Cars
async function loadUnsoldCars() {
  if (!currentClientId.value) return;
  
  isLoadingUnsoldCars.value = true;
  unsoldCars.value = [];
  
  try {
    const response = await axios.get('/api/getUnsoldCars', {
      params: { client_id: currentClientId.value }
    });
    if (response.data && response.data.cars && Array.isArray(response.data.cars)) {
      unsoldCars.value = response.data.cars;
    }
  } catch (error) {
    console.error('Error loading unsold cars:', error);
    toast.error('فشل تحميل السيارات', {
      timeout: 3000,
      position: "bottom-right",
      rtl: true,
    });
  } finally {
    isLoadingUnsoldCars.value = false;
  }
}

// Load Internal Sales
async function loadInternalSales() {
  if (!currentClientId.value) return;
  
  isLoadingInternalSales.value = true;
  try {
    const response = await axios.get('/api/getInternalSales', {
      params: { client_id: currentClientId.value }
    });
    internalSalesData.value = response.data.sales || [];
    internalSalesTotals.value = response.data.totals || {};
    hasInternalSales.value = response.data.has_internal_sales || false;
    if (response.data.client) {
      internalSalesClient.value = response.data.client;
    }
  } catch (error) {
    console.error('Error loading internal sales:', error);
    toast.error('فشل تحميل المبيعات الداخلية', {
      timeout: 3000,
      position: "bottom-right",
      rtl: true,
    });
  } finally {
    isLoadingInternalSales.value = false;
  }
}

async function loadInternalSalesBuyers() {
  isLoadingBuyers.value = true;
  try {
    // Pass currentClientId to the API
    const response = await axios.get('/api/getInternalSalesBuyers', {
      params: { client_id: currentClientId.value }
    });
    internalSalesBuyers.value = response.data.buyers || [];
  } catch (error) {
    console.error('Error loading internal sales buyers:', error);
    toast.error('فشل تحميل قائمة الزبائن', {
      timeout: 3000,
      position: "bottom-right",
      rtl: true,
    });
  } finally {
    isLoadingBuyers.value = false;
  }
}

async function loadAllClients() {
  if (!currentClientId.value) return;
  
  isLoadingClients.value = true;
  try {
    const response = await axios.get('/api/getAllClients', {
      params: { client_id: currentClientId.value }
    });
    if (response.data && response.data.clients) {
      allClients.value = response.data.clients;
    } else if (props.clients && Array.isArray(props.clients)) {
      allClients.value = props.clients;
    }
  } catch (error) {
    console.error('Error loading clients:', error);
    if (props.clients && Array.isArray(props.clients)) {
      allClients.value = props.clients;
    }
  } finally {
    isLoadingClients.value = false;
  }
}

async function toggleInternalSalesStatus() {
  if (!currentClientId.value) return;
  
  try {
    const response = await axios.post('/api/toggleInternalSales', {
      client_id: currentClientId.value,
      has_internal_sales: !hasInternalSales.value
    });
    hasInternalSales.value = response.data.has_internal_sales;
    toast.success('تم تحديث حالة المبيعات الداخلية', {
      timeout: 2000,
      position: "bottom-right",
      rtl: true,
    });
    if (hasInternalSales.value) {
      loadInternalSales();
      loadUnsoldCars();
    }
  } catch (error) {
    console.error('Error toggling internal sales:', error);
    toast.error('فشل تحديث حالة المبيعات الداخلية', {
      timeout: 3000,
      position: "bottom-right",
      rtl: true,
    });
  }
}

async function openAddInternalSale(car = null) {
  if (car && car.id) {
    // تحويل السيارة إلى الصيغة المطلوبة
    const carFormatted = {
      id: car.id,
      car_type: car.car_type,
      year: car.year,
      vin: car.vin,
      car_number: car.car_number,
      total_s: car.total_s || 0,
      car_price: car.car_price || 0
    };
    unsoldCars.value = [carFormatted];
    await loadAllClients();
    const selectedCar = availableCars.value.find(c => c.value === car.id);
    internalSaleForm.value = {
      client_id: currentClientId.value,
      client_name: '',
      client_phone: '',
      car_id: car.id,
      car_price: selectedCar?.car_price || selectedCar?.total || car.total || 0,
      shipping: 0,
      sale_price: selectedCar?.total_s || car.total_s || 0,
      paid_amount: 0,
      expenses: selectedCar?.expenses || car.expenses || 0,
      additional_expenses: 0,
      note: '',
      payment_note: '',
      sale_date: getTodayDate()
    };
    showNewClientForm.value = false;
    showAddInternalSaleForm.value = true;
    return;
  }

  if (!car && unsoldCars.value.length === 0) {
    await loadUnsoldCars();
  }
  
  await loadAllClients();
  
  if (car && car.id) {
    // تحويل السيارة إلى الصيغة المطلوبة
    // إذا كانت السيارة قادمة من جدول السيارات غير المباعة، فهي تحتوي بالفعل على البيانات المطلوبة
    // ولكن إذا كانت قادمة من مكان آخر، نحتاج للتأكد
    
    // البحث عن السيارة في القائمة المحدثة
    const selectedCar = unsoldCars.value.find(c => c.id === car.id) || car;
    
    internalSaleForm.value = {
      client_id: currentClientId.value,
      client_name: '',
      client_phone: '',
      car_id: car.id,
      car_price: selectedCar.car_price || selectedCar.total || 0,
      shipping: 0,
      sale_price: selectedCar.total_s || 0,
      paid_amount: 0,
      expenses: selectedCar.expenses || 0,
      additional_expenses: 0,
      note: '',
      payment_note: '',
      sale_date: getTodayDate()
    };
    showNewClientForm.value = false;
    showAddInternalSaleForm.value = true;
    return;
  }

  internalSaleForm.value = {
    client_id: currentClientId.value,
    client_name: '',
    client_phone: '',
    car_id: null,
    car_price: 0,
    shipping: 0,
    sale_price: 0,
    paid_amount: 0,
    expenses: 0,
    additional_expenses: 0,
    note: '',
    payment_note: '',
    sale_date: getTodayDate()
  };
  showNewClientForm.value = false;
  showAddInternalSaleForm.value = true;
}

function cancelAddInternalSale() {
  showAddInternalSaleForm.value = false;
  internalSaleForm.value = {
    client_id: null,
    client_name: '',
    client_phone: '',
    car_id: null,
    car_price: 0,
    shipping: 0,
    sale_price: 0,
    paid_amount: 0,
    expenses: 0,
    additional_expenses: 0,
    note: '',
    payment_note: '',
    sale_date: getTodayDate()
  };
  unsoldCars.value = [];
  showNewClientForm.value = false;
}

function openEditInternalSale(sale) {
  saleToEdit.value = sale;
  showModalEditInternalSale.value = true;
}

function handleUpdateInternalSale(updatedSale) {
  internalSaleForm.value = updatedSale;
  confirmUpdateInternalSale();
}

async function confirmAddInternalSale() {
  // التحقق من اختيار عميل موجود
  const hasSelectedClient = internalSaleForm.value.client_id && 
                            internalSaleForm.value.client_id !== '' && 
                            internalSaleForm.value.client_id !== null && 
                            internalSaleForm.value.client_id !== undefined;
  
  if (!showNewClientForm.value && !hasSelectedClient) {
    toast.error('يجب اختيار عميل أو إضافة عميل جديد', {
      timeout: 2000,
      position: "bottom-right",
      rtl: true,
    });
    return;
  }

  if (showNewClientForm.value && !internalSaleForm.value.client_name) {
    toast.error('يجب إدخال اسم العميل', {
      timeout: 2000,
      position: "bottom-right",
      rtl: true,
    });
    return;
  }

  if (!internalSaleForm.value.car_id) {
    toast.error('يجب اختيار سيارة', {
      timeout: 2000,
      position: "bottom-right",
      rtl: true,
    });
    return;
  }
  
  // إعداد البيانات المرسلة
  const formData = { ...internalSaleForm.value };
  
  // إذا كان إضافة عميل جديد، إزالة client_id
  if (showNewClientForm.value) {
    formData.client_id = null;
    formData.client_name = formData.client_name || '';
    formData.client_phone = formData.client_phone || '';
  } else {
    // تحويل client_id إلى رقم للتأكد من إرساله بشكل صحيح
    formData.client_id = parseInt(formData.client_id) || null;
    // إزالة client_name و client_phone عند اختيار عميل موجود
    formData.client_name = null;
    formData.client_phone = null;
  }
  
  try {
    await axios.post('/api/addInternalSale', formData);
    toast.success('تم إضافة المبيعة الداخلية بنجاح', {
      timeout: 2000,
      position: "bottom-right",
      rtl: true,
    });
    cancelAddInternalSale();
    loadInternalSales();
    loadInternalSalesBuyers();
    loadUnsoldCars();
  } catch (error) {
    console.error('Error adding internal sale:', error);
    toast.error(error.response?.data?.error || 'فشل إضافة المبيعة الداخلية', {
      timeout: 3000,
      position: "bottom-right",
      rtl: true,
    });
  }
}

async function confirmUpdateInternalSale() {
  try {
    await axios.post('/api/updateInternalSale', internalSaleForm.value);
    toast.success('تم تحديث المبيعة الداخلية بنجاح', {
      timeout: 2000,
      position: "bottom-right",
      rtl: true,
    });
    showModalEditInternalSale.value = false;
    loadInternalSales();
    loadInternalSalesBuyers();
  } catch (error) {
    console.error('Error updating internal sale:', error);
    toast.error('فشل تحديث المبيعة الداخلية', {
      timeout: 3000,
      position: "bottom-right",
      rtl: true,
    });
  }
}

async function deleteInternalSale(sale) {
  if (!confirm('هل أنت متأكد من حذف هذه المبيعة الداخلية؟')) {
    return;
  }
  
  try {
    await axios.post('/api/deleteInternalSale', { id: sale.id });
    toast.success('تم حذف المبيعة الداخلية بنجاح', {
      timeout: 2000,
      position: "bottom-right",
      rtl: true,
    });
    loadInternalSales();
    loadInternalSalesBuyers();
    loadUnsoldCars();
  } catch (error) {
    console.error('Error deleting internal sale:', error);
    toast.error('فشل حذف المبيعة الداخلية', {
      timeout: 3000,
      position: "bottom-right",
      rtl: true,
    });
  }
}

function toggleSaleSelection(saleId) {
  const index = selectedSales.value.indexOf(saleId);
  if (index > -1) {
    selectedSales.value.splice(index, 1);
  } else {
    selectedSales.value.push(saleId);
  }
}

function toggleSelectAll() {
  const filteredIds = filteredInternalSales.value.map(sale => sale.id);
  const allSelected = filteredIds.length > 0 && filteredIds.every(id => selectedSales.value.includes(id));
  
  if (allSelected) {
    // إزالة المحددات من النتائج المصفاة فقط
    selectedSales.value = selectedSales.value.filter(id => !filteredIds.includes(id));
  } else {
    // إضافة جميع النتائج المصفاة
    filteredIds.forEach(id => {
      if (!selectedSales.value.includes(id)) {
        selectedSales.value.push(id);
      }
    });
  }
}

function openBulkEditModal() {
  if (selectedSales.value.length === 0) {
    toast.error('يرجى اختيار مبيعات للتعديل', {
      timeout: 2000,
      position: "bottom-right",
      rtl: true,
    });
    return;
  }
  showBulkEditModal.value = true;
}

function closeBulkEditModal() {
  showBulkEditModal.value = false;
}

async function handleBulkEditConfirm(formData) {
  if (selectedSales.value.length === 0) {
    toast.error('يرجى اختيار مبيعات للتعديل', {
      timeout: 2000,
      position: "bottom-right",
      rtl: true,
    });
    return;
  }

  // التحقق من أن هناك قيمة واحدة على الأقل للتعديل
  const hasValue = formData.car_price !== null || 
                   formData.expenses !== null || 
                   formData.additional_expenses !== null;
  
  if (!hasValue) {
    toast.error('يرجى إدخال قيمة واحدة على الأقل للتعديل', {
      timeout: 2000,
      position: "bottom-right",
      rtl: true,
    });
    return;
  }

  try {
    const updateData = {
      sale_ids: selectedSales.value,
      car_price: formData.car_price,
      expenses: formData.expenses,
      additional_expenses: formData.additional_expenses,
    };

    await axios.post('/api/bulkUpdateInternalSales', updateData);
    toast.success(`تم تعديل ${selectedSales.value.length} مبيعة بنجاح`, {
      timeout: 2000,
      position: "bottom-right",
      rtl: true,
    });
    closeBulkEditModal();
    selectedSales.value = [];
    loadInternalSales();
    loadInternalSalesBuyers();
  } catch (error) {
    console.error('Error bulk updating internal sales:', error);
    toast.error(error.response?.data?.error || 'فشل التعديل المجمع', {
      timeout: 3000,
      position: "bottom-right",
      rtl: true,
    });
  }
}

function openAddPaymentModal(buyer) {
  if (buyer.remaining_debt <= 0) {
    toast.error('لا يوجد دين لهذا الزبون', {
      timeout: 2000,
      position: "bottom-right",
      rtl: true,
    });
    return;
  }
  selectedBuyer.value = buyer;
  showAddPaymentModal.value = true;
}

function closeAddPaymentModal() {
  showAddPaymentModal.value = false;
  selectedBuyer.value = null;
}

async function handleConfirmPayment(paymentData) {
  if (!selectedBuyer.value) {
    toast.error('لم يتم تحديد المشتري', {
      timeout: 2000,
      position: "bottom-right",
      rtl: true,
    });
    return;
  }
  
  if (!currentClientId.value) {
    toast.error('لم يتم تحديد التاجر', {
      timeout: 2000,
      position: "bottom-right",
      rtl: true,
    });
    console.error('currentClientId is missing:', {
      client_id: props.client_id,
      client: props.client,
    });
    return;
  }
  
  // Handle both old format (just amount) and new format (object with amount and note)
  const amount = typeof paymentData === 'object' ? paymentData.amount : paymentData;
  const note = typeof paymentData === 'object' ? (paymentData.note || '') : '';
  
  if (!amount || amount <= 0) {
    toast.error('يرجى إدخال مبلغ صحيح', {
      timeout: 2000,
      position: "bottom-right",
      rtl: true,
    });
    return;
  }

  if (amount > selectedBuyer.value.remaining_debt) {
    toast.error('المبلغ أكبر من الدين المتبقي', {
      timeout: 2000,
      position: "bottom-right",
      rtl: true,
    });
    return;
  }

  isLoadingPayment.value = true;
  try {
    const requestData = {
      buyer_id: parseInt(selectedBuyer.value.id),
      merchant_id: parseInt(currentClientId.value),
      amount: parseFloat(amount),
      note: note
    };
    
    console.log('Sending payment request:', requestData);
    
    await axios.post('/api/addPaymentToBuyer', requestData);
    
    toast.success('تم إضافة الدفعة بنجاح', {
      timeout: 2000,
      position: "bottom-right",
      rtl: true,
    });
    
    closeAddPaymentModal();
    loadInternalSales();
    loadInternalSalesBuyers();
  } catch (error) {
    console.error('Error adding payment:', {
      error: error,
      response: error.response,
      message: error.message,
      requestData: {
        buyer_id: selectedBuyer.value?.id,
        merchant_id: currentClientId.value,
        amount: amount,
      }
    });
    
    const errorMessage = error.response?.data?.error || 
                        error.response?.data?.message || 
                        error.message || 
                        'فشل إضافة الدفعة';
    
    toast.error(errorMessage, {
      timeout: 3000,
      position: "bottom-right",
      rtl: true,
    });
  } finally {
    isLoadingPayment.value = false;
  }
}

function openPaymentDetailsModal(buyer) {
  selectedBuyer.value = buyer;
  showPaymentDetailsModal.value = true;
}

function closePaymentDetailsModal() {
  showPaymentDetailsModal.value = false;
  selectedBuyer.value = null;
}

function handlePaymentDetailsRefresh() {
  // إعادة تحميل البيانات بعد حذف دفعة
  loadInternalSales();
  loadInternalSalesBuyers();
}

// Bulk Sale Functions
function openBulkSaleModal() {
  if (unsoldCars.value.length === 0) {
    loadUnsoldCars();
  }
  loadAllClients();
  bulkSaleForm.value = {
    client_id: null,
    client_name: '',
    client_phone: '',
    cars: [],
    sale_date: getTodayDate()
  };
  showBulkSaleModal.value = true;
}

function closeBulkSaleModal() {
  showBulkSaleModal.value = false;
  bulkSaleForm.value = {
    client_id: null,
    client_name: '',
    client_phone: '',
    cars: [],
    sale_date: getTodayDate()
  };
}

function addCarToBulkSale(car) {
  const existingIndex = bulkSaleForm.value.cars.findIndex(c => c.car_id === car.id);
  if (existingIndex === -1) {
    bulkSaleForm.value.cars.push({
      car_id: car.id,
      car_type: car.car_type,
      year: car.year,
      vin: car.vin || car.car_number,
      total_s: car.total_s || 0,
      sale_price: 0,
      paid_amount: 0,
      expenses: car.total_s || 0,
      additional_expenses: 0
    });
  }
}

function removeCarFromBulkSale(index) {
  bulkSaleForm.value.cars.splice(index, 1);
}

async function confirmBulkSale() {
  if (!bulkSaleForm.value.client_id && !bulkSaleForm.value.client_name) {
    toast.error('يجب اختيار عميل أو إضافة عميل جديد', {
      timeout: 2000,
      position: "bottom-right",
      rtl: true,
    });
    return;
  }

  if (bulkSaleForm.value.cars.length === 0) {
    toast.error('يجب اختيار سيارة واحدة على الأقل', {
      timeout: 2000,
      position: "bottom-right",
      rtl: true,
    });
    return;
  }

  try {
    const formData = {
      client_id: bulkSaleForm.value.client_id,
      client_name: bulkSaleForm.value.client_name || null,
      client_phone: bulkSaleForm.value.client_phone || null,
      cars: bulkSaleForm.value.cars,
      sale_date: bulkSaleForm.value.sale_date
    };

    await axios.post('/api/addBulkInternalSale', formData);
    toast.success(`تم إضافة ${bulkSaleForm.value.cars.length} مبيعة بنجاح`, {
      timeout: 2000,
      position: "bottom-right",
      rtl: true,
    });
    closeBulkSaleModal();
    loadInternalSales();
    loadInternalSalesBuyers();
    loadUnsoldCars();
  } catch (error) {
    console.error('Error adding bulk sale:', error);
    toast.error(error.response?.data?.error || 'فشل إضافة المبيعات', {
      timeout: 3000,
      position: "bottom-right",
      rtl: true,
    });
  }
}

// Load data on mount
onMounted(() => {
  if (currentClientId.value) {
    loadInternalSales();
    loadInternalSalesBuyers();
    loadUnsoldCars();
    loadAllClients();
    
    // التحقق من وجود car_id في query parameter
    const urlParams = new URLSearchParams(window.location.search);
    const carId = urlParams.get('car_id');
    if (carId) {
      // انتظار تحميل السيارات ثم فتح نموذج البيع
      setTimeout(async () => {
        await loadUnsoldCars();
        const car = unsoldCars.value.find(c => c.id == carId);
        if (car) {
          openAddInternalSale(car);
        }
        // إزالة car_id من URL
        const newUrl = window.location.pathname;
        window.history.replaceState({}, '', newUrl);
      }, 500);
    }
  }
});
</script>

<template>
  <Head title="المبيعات الداخلية" />
  <AuthenticatedLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="text-xl font-bold text-gray-800 dark:text-gray-200">
          المبيعات الداخلية - {{ props.client?.name || 'غير محدد' }}
        </h2>
        <Link :href="`/showClients/${currentClientId}`" class="text-blue-600 hover:text-blue-800 dark:text-blue-400">
          العودة إلى صفحة التاجر
        </Link>
      </div>
    </template>
     <div class="max-w-9xl mx-auto sm:px-6 lg:px-8 p-6 dark:bg-gray-900">
      <div class="overflow-hidden shadow-sm sm:rounded-lg">
        <div class="mb-4 flex items-center justify-between">
          <h2 class="text-xl font-bold dark:text-gray-400">المبيعات الداخلية</h2>
          <div class="flex items-center gap-4">
            <label class="flex items-center cursor-pointer">
              <span class="mr-3 text-sm font-medium text-gray-700 dark:text-gray-300">تفعيل المبيعات الداخلية</span>
              <div class="relative">
                <input 
                  type="checkbox" 
                  class="sr-only peer" 
                  :checked="hasInternalSales"
                  @change="toggleInternalSalesStatus"
                >
                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
              </div>
            </label>
          </div>
        </div>

        <!-- إحصائيات المبيعات الداخلية -->
        <div v-if="hasInternalSales && internalSalesData.length > 0" class="mb-6">
          <h3 class="text-lg font-bold mb-4 dark:text-gray-300">إحصائيات المبيعات الداخلية</h3>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
            <!-- كارد مجموع المبيعات -->
            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-sm text-gray-600 dark:text-gray-400">مجموع المبيعات</p>
                  <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ formatNumber(salesStatistics.totalSales) }} $</p>
                </div>
                <div class="text-blue-500 text-3xl">💰</div>
              </div>
            </div>
            
            <!-- كارد مجموع المصاريف -->
            <div class="bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-800 rounded-lg p-4">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-sm text-gray-600 dark:text-gray-400">مجموع المصاريف</p>
                  <p class="text-2xl font-bold text-orange-600 dark:text-orange-400">{{ formatNumber(salesStatistics.totalExpenses) }} $</p>
                </div>
                <div class="text-orange-500 text-3xl">📊</div>
              </div>
            </div>
            
            <!-- كارد الربح -->
            <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-sm text-gray-600 dark:text-gray-400">الربح</p>
                  <p class="text-2xl font-bold" :class="salesStatistics.totalProfit >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'">
                    {{ formatNumber(salesStatistics.totalProfit) }} $
                  </p>
                </div>
                <div class="text-green-500 text-3xl">📈</div>
              </div>
            </div>
            
            <!-- كارد الدين -->
            <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-sm text-gray-600 dark:text-gray-400">الدين الإجمالي</p>
                  <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ formatNumber(salesStatistics.totalDebt) }} $</p>
                </div>
                <div class="text-red-500 text-3xl">⚠️</div>
              </div>
            </div>
          </div>
          
          <!-- إحصائيات نوع السيارات -->
          <div v-if="salesStatistics.carTypes.length > 0" class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
            <h4 class="text-md font-bold mb-3 dark:text-gray-300">إحصائيات نوع السيارات المباعة</h4>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
              <div 
                v-for="stat in salesStatistics.carTypes" 
                :key="stat.type"
                class="bg-white dark:bg-gray-700 rounded-lg p-3 border border-gray-200 dark:border-gray-600"
              >
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ stat.type }}</p>
                <p class="text-lg font-bold text-gray-800 dark:text-gray-200">{{ stat.count }} سيارة</p>
              </div>
            </div>
          </div>
        </div>

        <!-- السيارات المتاحة للبيع -->
        <div v-if="hasInternalSales && unsoldCars.length > 0" class="mb-6 p-4 bg-green-50 dark:bg-green-900/20 rounded-lg border border-green-200 dark:border-green-800">
          <div class="flex justify-between items-center mb-4">
             <h3 class="text-lg font-bold dark:text-green-300">السيارات المتاحة للبيع ({{ unsoldCars.length }})</h3>
             <button
               @click="openBulkSaleModal()"
               class="px-3 py-1 text-sm text-white bg-purple-600 rounded hover:bg-purple-700 dark:bg-purple-700 dark:hover:bg-purple-800"
             >
               بيع مجمع
             </button>
          </div>
          
          <!-- حقل البحث في السيارات المتاحة -->
          <div class="mb-4 p-3 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="flex items-center gap-2">
              <InputLabel for="search_unsold_cars" value="بحث في السيارات المتاحة:" class="!mb-0" />
              <TextInput
                id="search_unsold_cars"
                v-model="searchUnsoldCars"
                type="text"
                placeholder="ابحث بالشانصي (VIN)، رقم السيارة، نوع السيارة، السنة..."
                class="flex-1"
              />
              <button
                v-if="searchUnsoldCars"
                @click="searchUnsoldCars = ''"
                class="px-3 py-2 text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200"
              >
                ✕
              </button>
            </div>
            <p v-if="searchUnsoldCars" class="mt-2 text-sm text-gray-600 dark:text-gray-400">
              تم العثور على {{ filteredUnsoldCars.length }} من {{ unsoldCars.length }} سيارة
            </p>
          </div>
         
          <div v-if="isLoadingUnsoldCars" class="text-center py-4">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-green-500"></div>
          </div>
          <div v-else-if="unsoldCars.length === 0" class="text-center py-4 text-gray-500 dark:text-gray-400">
            لا توجد سيارات متاحة للبيع حالياً
          </div>
          <div v-else-if="filteredUnsoldCars.length === 0" class="text-center py-4 text-gray-500 dark:text-gray-400">
            <span v-if="searchUnsoldCars">لا توجد نتائج للبحث "{{ searchUnsoldCars }}"</span>
            <span v-else>لا توجد سيارات متاحة للبيع حالياً</span>
          </div>
          <div v-else class="overflow-x-auto max-h-96 overflow-y-auto">
            <table class="w-full text-sm text-right text-gray-500 dark:text-gray-200 dark:text-gray-400 text-center">
              <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400 text-center sticky top-0">
                <tr>
                  <th class="px-2 py-3">نوع السيارة</th>
                  <th class="px-2 py-3">السنة</th>
                  <th class="px-2 py-3">رقم الشاصي / السيارة</th>
                  <!-- <th class="px-2 py-3">سعر السيارة</th> -->
                  <th class="px-2 py-3">التكلفة (Total S)</th>
                  <th class="px-2 py-3">إجراء</th>
                </tr>
              </thead>
              <tbody>
                <tr 
                  v-for="car in filteredUnsoldCars" 
                  :key="car.id"
                  class="bg-white border-b dark:bg-gray-900 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600"
                >
                  <td class="px-2 py-3 font-medium">{{ car.car_type }}</td>
                  <td class="px-2 py-3">{{ car.year }}</td>
                  <td class="px-2 py-3">
                    <div class="font-mono text-xs">{{ car.vin }}</div>
                    <div v-if="car.car_number" class="text-xs text-gray-500">{{ car.car_number }}</div>
                  </td>
                  <!-- <td class="px-2 py-3">{{ typeof car.car_price === 'number' ? car.car_price.toFixed(2) : (parseFloat(car.car_price) || 0).toFixed(2) }} $</td> -->
                  <td class="px-2 py-3 font-bold">{{ formatNumber(car.total_s) }} $</td>
                  <td class="px-2 py-3">
                    <button
                      @click="openAddInternalSale(car)"
                      class="px-3 py-1 text-white bg-blue-600 rounded hover:bg-blue-700 text-xs flex items-center justify-center mx-auto gap-1"
                    >
                      <span class="text-lg">+</span> بيع
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <div v-if="hasInternalSales" class="mt-4">
          <!-- Form إضافة مبيعة داخلية -->
          <div v-if="showAddInternalSaleForm" class="mb-6 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="flex justify-between items-center mb-4">
              <h3 class="text-lg font-bold dark:text-gray-100">إضافة مبيعة داخلية</h3>
              <button
                @click="cancelAddInternalSale"
                class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
              >
                ✕
              </button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <!-- اختيار العميل -->
              <div>
                <div class="flex justify-between items-center mb-2">
                  <InputLabel for="client_select" value="العميل" class="!mb-0" />
                  <button
                    type="button"
                    @click="showNewClientForm = !showNewClientForm"
                    class="text-xs font-bold text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 transition-colors duration-200 flex items-center gap-1"
                  >
                    <span v-if="!showNewClientForm">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                      </svg>
                      إضافة عميل جديد
                    </span>
                    <span v-else>
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                      </svg>
                      اختيار عميل موجود
                    </span>
                  </button>
                </div>
                
                <div v-if="!showNewClientForm" class="relative">
                  <ModelListSelect
                    v-model="internalSaleForm.client_id"
                    :list="availableClients"
                    option-value="value"
                    option-text="text"
                    placeholder="ابحث عن عميل بالاسم أو رقم الهاتف..."
                    :is-disabled="isLoadingClients"
                    class="dark:bg-gray-700 dark:text-gray-100 w-full"
                  />
                </div>
                <div v-else class="space-y-3 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-200 dark:border-gray-600">
                  <div>
                    <InputLabel for="client_name" value="اسم العميل الجديد" class="text-xs mb-1" />
                    <TextInput
                      id="client_name"
                      v-model="internalSaleForm.client_name"
                      placeholder="الاسم الكامل"
                      class="block w-full text-sm"
                      required
                    />
                  </div>
                  <div>
                    <InputLabel for="client_phone" value="رقم الهاتف" class="text-xs mb-1" />
                    <TextInput
                      id="client_phone"
                      v-model="internalSaleForm.client_phone"
                      placeholder="رقم الهاتف (اختياري)"
                      class="block w-full text-sm"
                    />
                  </div>
                </div>
              </div>
              <div>
                <InputLabel for="car_select" value="السيارة" />
                <ModelListSelect
                  v-model="internalSaleForm.car_id"
                  :list="availableCars"
                  option-value="value"
                  option-text="text"
                  placeholder="ابحث عن سيارة بالشانص (VIN) أو الكاتي (رقم السيارة)..."
                  :is-disabled="isLoadingUnsoldCars"
                  @change="onCarSelected"
                  class="dark:bg-gray-700 dark:text-gray-100"
                />
              </div>
              <div>
                <InputLabel for="car_price" value="رأس المال / تكلفة السيارة ($)" />
                <TextInput
                  id="car_price"
                  type="number"
                  step="0.01"
                  v-model="internalSaleForm.car_price"
                  class="mt-1 block w-full"
                  placeholder="يُملأ تلقائياً من تكلفة الشراء"
                />
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">قابل للتعديل عند الحاجة</p>
              </div>
              <!-- حقل النقل مخفي -->
              <input
                type="hidden"
                v-model="internalSaleForm.shipping"
              />
              <div>
                <InputLabel for="sale_price" value="سعر البيع ($)" />
                <TextInput
                  id="sale_price"
                  type="number"
                  step="0.01"
                  v-model="internalSaleForm.sale_price"
                  class="mt-1 block w-full"
                />
              </div>
              <div>
                <InputLabel for="paid_amount" value="المبلغ المدفوع ($)" />
                <TextInput
                  id="paid_amount"
                  type="number"
                  step="0.01"
                  v-model="internalSaleForm.paid_amount"
                  class="mt-1 block w-full"
                />
              </div>
              <div v-if="internalSaleForm.paid_amount > 0">
                <InputLabel for="payment_note" value="ملاحظة الدفعة (اختياري)" />
                <textarea
                  id="payment_note"
                  v-model="internalSaleForm.payment_note"
                  rows="2"
                  placeholder="أدخل ملاحظة للدفعة الأولى (اختياري)"
                  class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
                ></textarea>
              </div>
              <!-- تم إخفاء حقل المصاريف ويتم تعبئته تلقائياً -->
              <input
                type="hidden"
                v-model="internalSaleForm.expenses"
              />
              <div>
                <InputLabel for="additional_expenses" value="مصاريف إضافية ($)" />
                <TextInput
                  id="additional_expenses"
                  type="number"
                  step="0.01"
                  v-model="internalSaleForm.additional_expenses"
                  class="mt-1 block w-full"
                />
              </div>
              <div>
                <InputLabel for="sale_date" value="تاريخ البيع" />
                <TextInput
                  id="sale_date"
                  type="date"
                  v-model="internalSaleForm.sale_date"
                  class="mt-1 block w-full"
                />
              </div>
              <div class="md:col-span-2">
                <InputLabel for="note" value="ملاحظات" />
                <textarea
                  id="note"
                  v-model="internalSaleForm.note"
                  rows="3"
                  class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
                ></textarea>
              </div>
            </div>
            <div class="flex justify-end gap-2 mt-4">
              <button
                @click="cancelAddInternalSale"
                class="px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-200 dark:bg-gray-700 rounded hover:bg-gray-300 dark:hover:bg-gray-600"
              >
                إلغاء
              </button>
              <button
                @click="confirmAddInternalSale"
                class="px-4 py-2 text-white bg-green-600 rounded hover:bg-green-700 dark:bg-green-700 dark:hover:bg-green-800"
              >
                إضافة
              </button>
            </div>
          </div>

          <div class="mb-4 flex justify-end">
            <!-- تمت إزالة زر الإضافة المكرر لأنه موجود الآن في قسم السيارات المتاحة -->
          </div>

          <div v-if="isLoadingInternalSales" class="text-center py-10">
            <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500"></div>
            <p class="mt-4 text-gray-600 dark:text-gray-400">جاري تحميل البيانات...</p>
          </div>

          <div v-else class="overflow-x-auto shadow-md sm:rounded-lg">
            <!-- حقل البحث -->
            <div class="mb-4 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
              <div class="flex items-center gap-2">
                <InputLabel for="search_sale" value="بحث عن بيع السيارة:" class="!mb-0" />
                <TextInput
                  id="search_sale"
                  v-model="searchTerm"
                  type="text"
                  placeholder="ابحث بالشانصي (VIN)، رقم السيارة، نوع السيارة، اسم الزبون..."
                  class="flex-1"
                />
                <button
                  v-if="searchTerm"
                  @click="searchTerm = ''"
                  class="px-3 py-2 text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200"
                >
                  ✕
                </button>
              </div>
              <p v-if="searchTerm" class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                تم العثور على {{ filteredInternalSales.length }} من {{ internalSalesData.length }} مبيعة
              </p>
            </div>
            
            <div v-if="selectedSales.length > 0" class="mb-4 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800 flex justify-between items-center">
              <span class="text-blue-700 dark:text-blue-300 font-medium">
                تم اختيار {{ selectedSales.length }} مبيعة
              </span>
              <button
                @click="openBulkEditModal"
                class="px-4 py-2 text-white bg-blue-600 rounded hover:bg-blue-700 dark:bg-blue-700 dark:hover:bg-blue-800"
              >
                تعديل مجمع
              </button>
            </div>
            <table class="w-full text-sm text-right text-gray-500 dark:text-gray-200 dark:text-gray-400 text-center">
              <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400 text-center">
                <tr>
                  <th class="px-2 py-3 print:hidden">
                    <input
                      type="checkbox"
                      :checked="selectedSales.length === filteredInternalSales.length && filteredInternalSales.length > 0"
                      @change="toggleSelectAll"
                      class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500"
                    />
                  </th>
                  <th class="px-2 py-3">#</th>
                  <th class="px-2 py-3">اسم الزبون المشتري</th>
                  <th class="px-2 py-3">السيارة</th>
                  <th class="px-2 py-3">الشانصي (VIN)</th>
                  <th class="px-2 py-3">رأس المال</th>
                  <th class="px-2 py-3">سعر البيع</th>
                  <th class="px-2 py-3">المبلغ المدفوع</th>
                  <th class="px-2 py-3">المصاريف</th>
                  <th class="px-2 py-3">مصاريف إضافية</th>
                  <th class="px-2 py-3">الربح</th>
                  <th class="px-2 py-3">تاريخ البيع</th>
                  <th class="px-2 py-3">ملاحظات</th>
                  <th class="px-2 py-3 print:hidden">تنفيذ</th>
                </tr>
              </thead>
              <tbody>
                <tr 
                  v-for="(sale, index) in filteredInternalSales" 
                  :key="sale.id"
                  class="bg-white border-b dark:bg-gray-900 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600"
                >
                  <td class="px-2 py-3 print:hidden">
                    <input
                      type="checkbox"
                      :checked="selectedSales.includes(sale.id)"
                      @change="toggleSaleSelection(sale.id)"
                      class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500"
                    />
                  </td>
                  <td class="px-2 py-3">{{ index + 1 }}</td>
                  <td class="px-2 py-3 font-medium">
                    <div>
                      {{ sale.client?.name || 'غير محدد' }}
                    </div>
                    <div v-if="sale.client?.phone" class="text-xs text-gray-500 dark:text-gray-400">
                      {{ sale.client?.phone }}
                    </div>
                  </td>
                  <td class="px-2 py-3">{{ sale.car?.car_type }} {{ sale.car?.year }}</td>
                  <td class="px-2 py-3 font-mono text-xs">{{ sale.car?.vin || sale.car?.car_number || '-' }}</td>
                  <td class="px-2 py-3">{{ formatNumber(sale.car_price) }} $</td>
                  <td class="px-2 py-3">{{ formatNumber(sale.sale_price) }} $</td>
                  <td class="px-2 py-3">{{ formatNumber(sale.paid_amount) }} $</td>
                  <td class="px-2 py-3">{{ formatNumber(sale.expenses) }} $</td>
                  <td class="px-2 py-3">{{ formatNumber(sale.additional_expenses) }} $</td>
                  <td 
                    class="px-2 py-3 font-bold"
                    :class="(parseFloat(sale.profit) || 0) >= 0 ? 'text-green-600' : 'text-red-600'"
                  >
                    {{ formatNumber(sale.profit) }} $
                  </td>
                  <td class="px-2 py-3">{{ formatSaleDate(sale.sale_date || sale.created_at) }}</td>
                  <td class="px-2 py-3">
                    <div v-if="sale.note" class="mb-1">{{ sale.note }}</div>
                    <div v-if="sale.payment_notes && sale.payment_notes.length > 0" class="text-xs text-blue-600 dark:text-blue-400">
                      <div v-for="(paymentNote, idx) in sale.payment_notes" :key="idx" class="mt-1">
                        <span class="font-semibold">دفعة:</span> {{ paymentNote }}
                      </div>
                    </div>
                  </td>
                  <td class="px-2 py-3 print:hidden">
                    <button
                      @click="openEditInternalSale(sale)"
                      class="px-2 py-1 text-white bg-blue-500 rounded hover:bg-blue-600 mr-1"
                    >
                      <edit class="w-4 h-4 inline" />
                    </button>
                    <button
                      @click="deleteInternalSale(sale)"
                      class="px-2 py-1 text-white bg-red-500 rounded hover:bg-red-600"
                    >
                      <trash class="w-4 h-4 inline" />
                    </button>
                  </td>
                </tr>
                <tr v-if="filteredInternalSales.length === 0" class="bg-gray-100 dark:bg-gray-800">
                  <td colspan="13" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                    <span v-if="searchTerm">لا توجد نتائج للبحث "{{ searchTerm }}"</span>
                    <span v-else>لا توجد مبيعات داخلية</span>
                  </td>
                </tr>
              </tbody>
              <tfoot v-if="internalSalesData.length > 0" class="bg-gray-100 dark:bg-gray-800 font-bold">
                <tr>
                  <td colspan="3" class="px-2 py-3 text-left">الإجمالي:</td>
                  <td class="px-2 py-3">{{ formatNumber(internalSalesData.reduce((sum, sale) => sum + (parseFloat(sale.car_price) || 0), 0)) }} $</td>
                  <td class="px-2 py-3">{{ formatNumber(internalSalesTotals.total_sales) }} $</td>
                  <td class="px-2 py-3">{{ formatNumber(internalSalesTotals.total_paid) }} $</td>
                  <td class="px-2 py-3">{{ formatNumber(internalSalesTotals.total_expenses) }} $</td>
                  <td class="px-2 py-3">{{ formatNumber(internalSalesData.reduce((sum, sale) => sum + (parseFloat(sale.additional_expenses) || 0), 0)) }} $</td>
                  <td 
                    class="px-2 py-3"
                    :class="(parseFloat(internalSalesTotals.total_profit) || 0) >= 0 ? 'text-green-600' : 'text-red-600'"
                  >
                    {{ formatNumber(internalSalesTotals.total_profit) }} $
                  </td>
                  <td colspan="3"></td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>

        <!-- الزبائن المطلوبون في المبيعات الداخلية -->
        <div v-if="hasInternalSales && internalSalesBuyers.length > 0" class="mb-6 mt-6">
          <h3 class="text-lg font-bold mb-4 dark:text-blue-300">الزبائن المطلوبون في المبيعات الداخلية</h3>
          <div v-if="isLoadingBuyers" class="text-center py-4">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
          </div>
          <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div 
              v-for="buyer in internalSalesBuyers" 
              :key="buyer.id"
              :class="[
                'p-4 rounded-lg border shadow-sm transition-all duration-200 hover:shadow-md',
                buyer.remaining_debt > 0 
                  ? 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800' 
                  : 'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800'
              ]"
            >
              <div class="flex justify-between items-start mb-3">
                <div>
                  <h4 class="font-bold text-lg text-gray-800 dark:text-gray-200">{{ buyer.name }}</h4>
                  <p class="text-sm text-gray-500 dark:text-gray-400">{{ buyer.phone }}</p>
                </div>
                <div class="text-center bg-white dark:bg-gray-800 rounded-full px-3 py-1 text-xs font-bold border" 
                     :class="buyer.remaining_debt > 0 ? 'text-red-600 border-red-100' : 'text-green-600 border-green-100'">
                  {{ buyer.sales_count }} سيارة
                </div>
              </div>
              
              <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                  <span class="text-gray-600 dark:text-gray-400">إجمالي المبيعات:</span>
                  <span class="font-medium">{{ formatNumber(buyer.total_sales) }} $</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-600 dark:text-gray-400">المدفوع:</span>
                  <span class="font-medium text-green-600">{{ formatNumber(buyer.total_paid) }} $</span>
                </div>
                <div class="border-t pt-2 mt-2 flex justify-between items-center font-bold text-lg">
                  <span :class="buyer.remaining_debt > 0 ? 'text-red-600' : 'text-green-600'">
                    {{ buyer.remaining_debt > 0 ? 'المتبقي (دين):' : 'واصل:' }}
                  </span>
                  <span :class="buyer.remaining_debt > 0 ? 'text-red-600' : 'text-green-600'">
                    {{ formatNumber(Math.abs(buyer.remaining_debt)) }} $
                  </span>
                </div>
              </div>
              
              <!-- أزرار إضافة دفع وتفاصيل الدفع -->
              <div class="mt-3 pt-3 border-t">
                <div class="flex gap-2">
                  <button
                    v-if="buyer.remaining_debt > 0"
                    @click="openAddPaymentModal(buyer)"
                    class="flex-1 px-4 py-2 text-white bg-green-600 rounded hover:bg-green-700 dark:bg-green-700 dark:hover:bg-green-800 font-medium text-sm"
                  >
                    إضافة دفع
                  </button>
                  <button
                    @click="openPaymentDetailsModal(buyer)"
                    class="flex-1 px-4 py-2 text-white bg-blue-600 rounded hover:bg-blue-700 dark:bg-blue-700 dark:hover:bg-blue-800 font-medium text-sm"
                  >
                    تفاصيل الدفع
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div v-else-if="!hasInternalSales" class="text-center py-10 text-gray-500 dark:text-gray-400">
          يجب تفعيل المبيعات الداخلية أولاً
        </div>
      </div>
    </div>

    <!-- Modal Edit Internal Sale -->
    <ModalEditInternalSale
      :show="showModalEditInternalSale"
      :sale="saleToEdit"
      :clients="allClients.map(client => ({
        value: client.id,
        text: `${client.name || ''}${client.phone ? ' - ' + client.phone : ''}`,
        name: client.name || '',
        phone: client.phone || ''
      }))"
      @close="showModalEditInternalSale = false"
      @update="handleUpdateInternalSale"
    />

    <!-- Modal Bulk Edit -->
    <ModalBulkEditInternalSales
      :show="showBulkEditModal"
      :selected-count="selectedSales.length"
      @close="closeBulkEditModal"
      @confirm="handleBulkEditConfirm"
    />

    <!-- Modal Add Payment -->
    <ModalAddPayment
      :show="showAddPaymentModal"
      :buyer="selectedBuyer"
      :merchant-id="currentClientId"
      :is-loading="isLoadingPayment"
      @close="closeAddPaymentModal"
      @confirm="handleConfirmPayment"
    />

    <!-- Modal Payment Details -->
    <ModalPaymentDetails
      :show="showPaymentDetailsModal"
      :buyer="selectedBuyer"
      :merchant-id="currentClientId"
      @close="closePaymentDetailsModal"
      @refresh="handlePaymentDetailsRefresh"
    />

    <!-- Modal Bulk Sale -->
    <Modal :show="showBulkSaleModal" @close="closeBulkSaleModal">
      <template #header>
        <h2 class="text-xl font-bold dark:text-gray-100">بيع مجمع - بيع عدة سيارات لنفس الزبون</h2>
      </template>
      
      <template #body>
        <div class="space-y-4">
          <!-- اختيار العميل -->
          <div>
            <div class="flex justify-between items-center mb-2">
              <InputLabel for="bulk_client_select" value="العميل" class="!mb-0" />
              <button
                type="button"
                @click="bulkSaleForm.showNewClientForm = !bulkSaleForm.showNewClientForm"
                class="text-xs font-bold text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
              >
                <span v-if="!bulkSaleForm.showNewClientForm">+ إضافة عميل جديد</span>
                <span v-else>اختيار عميل موجود</span>
              </button>
            </div>
            
            <div v-if="!bulkSaleForm.showNewClientForm" class="relative">
              <ModelListSelect
                v-model="bulkSaleForm.client_id"
                :list="availableClients"
                option-value="value"
                option-text="text"
                placeholder="ابحث عن عميل بالاسم أو رقم الهاتف..."
                :is-disabled="isLoadingClients"
                class="dark:bg-gray-700 dark:text-gray-100 w-full"
              />
            </div>
            <div v-else class="space-y-3 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-200 dark:border-gray-600">
              <div>
                <InputLabel for="bulk_client_name" value="اسم العميل الجديد" class="text-xs mb-1" />
                <TextInput
                  id="bulk_client_name"
                  v-model="bulkSaleForm.client_name"
                  placeholder="الاسم الكامل"
                  class="block w-full text-sm"
                  required
                />
              </div>
              <div>
                <InputLabel for="bulk_client_phone" value="رقم الهاتف" class="text-xs mb-1" />
                <TextInput
                  id="bulk_client_phone"
                  v-model="bulkSaleForm.client_phone"
                  placeholder="رقم الهاتف (اختياري)"
                  class="block w-full text-sm"
                />
              </div>
            </div>
          </div>

          <!-- اختيار السيارات -->
          <div>
            <InputLabel for="bulk_car_select" value="السيارات المتاحة" />
            <div v-if="isLoadingUnsoldCars" class="text-center py-4">
              <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
            </div>
            <div v-else-if="unsoldCars.length === 0" class="text-center py-4 text-gray-500 dark:text-gray-400">
              لا توجد سيارات متاحة للبيع
            </div>
            <div v-else class="max-h-60 overflow-y-auto border border-gray-200 dark:border-gray-600 rounded-lg p-2">
              <div 
                v-for="car in unsoldCars" 
                :key="car.id"
                class="flex items-center justify-between p-2 hover:bg-gray-50 dark:hover:bg-gray-700 rounded mb-1"
              >
                <div class="flex-1">
                  <span class="font-medium">{{ car.car_type }} {{ car.year }}</span>
                  <span class="text-xs text-gray-500 dark:text-gray-400 mr-2">- {{ car.vin || car.car_number || 'بدون رقم' }}</span>
                  <span class="text-xs text-blue-600 dark:text-blue-400">({{ formatNumber(car.total_s) }} $)</span>
                </div>
                <button
                  @click="addCarToBulkSale(car)"
                  :disabled="bulkSaleForm.cars.some(c => c.car_id === car.id)"
                  class="px-3 py-1 text-xs text-white bg-green-600 rounded hover:bg-green-700 disabled:bg-gray-400 disabled:cursor-not-allowed"
                >
                  {{ bulkSaleForm.cars.some(c => c.car_id === car.id) ? 'مضافة' : 'إضافة' }}
                </button>
              </div>
            </div>
          </div>

          <!-- السيارات المختارة -->
          <div v-if="bulkSaleForm.cars.length > 0">
            <InputLabel value="السيارات المختارة" />
            <div class="space-y-2 max-h-60 overflow-y-auto border border-gray-200 dark:border-gray-600 rounded-lg p-3">
              <div 
                v-for="(car, index) in bulkSaleForm.cars" 
                :key="index"
                class="bg-gray-50 dark:bg-gray-800 rounded-lg p-3 border border-gray-200 dark:border-gray-700"
              >
                <div class="flex justify-between items-start mb-2">
                  <div>
                    <p class="font-medium">{{ car.car_type }} {{ car.year }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ car.vin }}</p>
                  </div>
                  <button
                    @click="removeCarFromBulkSale(index)"
                    class="text-red-600 hover:text-red-800 text-sm"
                  >
                    ✕ حذف
                  </button>
                </div>
                <div class="grid grid-cols-2 gap-2 mt-2">
                  <div>
                    <InputLabel :for="`sale_price_${index}`" value="سعر البيع ($)" class="text-xs" />
                    <TextInput
                      :id="`sale_price_${index}`"
                      type="number"
                      step="0.01"
                      v-model="car.sale_price"
                      class="mt-1 block w-full text-sm"
                    />
                  </div>
                  <div>
                    <InputLabel :for="`paid_amount_${index}`" value="المبلغ المدفوع ($)" class="text-xs" />
                    <TextInput
                      :id="`paid_amount_${index}`"
                      type="number"
                      step="0.01"
                      v-model="car.paid_amount"
                      class="mt-1 block w-full text-sm"
                    />
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- تاريخ البيع -->
          <div>
            <InputLabel for="bulk_sale_date" value="تاريخ البيع" />
            <TextInput
              id="bulk_sale_date"
              type="date"
              v-model="bulkSaleForm.sale_date"
              class="mt-1 block w-full"
            />
          </div>
        </div>
      </template>
      
      <template #footer>
        <div class="flex justify-end gap-2">
          <button
            @click="closeBulkSaleModal"
            class="px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-200 dark:bg-gray-700 rounded hover:bg-gray-300 dark:hover:bg-gray-600"
          >
            إلغاء
          </button>
          <button
            @click="confirmBulkSale"
            class="px-4 py-2 text-white bg-purple-600 rounded hover:bg-purple-700 dark:bg-purple-700 dark:hover:bg-purple-800"
          >
            إضافة المبيعات ({{ bulkSaleForm.cars.length }})
          </button>
        </div>
      </template>
    </Modal>
  </AuthenticatedLayout>
</template>


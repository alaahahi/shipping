<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/inertia-vue3';
import { useToast } from "vue-toastification";
import axios from 'axios';
import { ref, computed, onMounted } from 'vue';
import TripCompanyUpload from '@/Components/TripCompanyUpload.vue';
import TripExpenseForm from '@/Components/TripExpenseForm.vue';
import TripCarForm from '@/Components/TripCarForm.vue';

const props = defineProps({
  trip: Object,
  stats: Object,
  carsByConsignee: Array,
});

const toast = useToast();
const activeTab = ref('overview');
const companies = ref([]);
const expenses = ref([]);
const isLoading = ref(false);
const companyCars = ref({});
const showCarForm = ref(false);
const selectedCarForm = ref({ tripCompanyId: null, car: null });
const importFileInput = ref(null);
const isImporting = ref(false);

// Load companies and expenses
const loadCompanies = async () => {
  try {
    const response = await axios.get(`/trips/${props.trip.id}/companies`);
    companies.value = response.data.map(company => ({
      ...company,
      showCars: company.showCars || false,
      editingPrice: false,
      tempShippingPrice: company.shipping_price_per_car || 0,
    }));
  } catch (error) {
    console.error(error);
    toast.error('حدث خطأ أثناء جلب الشركات');
  }
};

// تحديث سعر الشحن
const updateShippingPrice = async (company) => {
  try {
    const response = await axios.put(`/api/trips/${props.trip.id}/companies/${company.id}/shipping-price`, {
      shipping_price_per_car: company.tempShippingPrice || 0,
    });

    if (response.data.success) {
      company.shipping_price_per_car = company.tempShippingPrice;
      company.editingPrice = false;
      toast.success('تم تحديث سعر الشحن بنجاح');
      await loadCompanies(); // تحديث القائمة
    } else {
      toast.error(response.data.message || 'حدث خطأ أثناء تحديث سعر الشحن');
    }
  } catch (error) {
    console.error(error);
    const errorMessage = error.response?.data?.message || 'حدث خطأ أثناء تحديث سعر الشحن';
    toast.error(errorMessage);
  }
};

const loadExpenses = async () => {
  try {
    const response = await axios.get(`/api/trips/${props.trip.id}/expenses`);
    expenses.value = response.data;
  } catch (error) {
    console.error(error);
    toast.error('حدث خطأ أثناء جلب المصاريف');
  }
};

onMounted(() => {
  loadCompanies();
  loadExpenses();
});

const formatDate = (date) => {
  if (!date) return '';
  return new Date(date).toLocaleDateString('en-US');
};

const formatCurrency = (amount, currency = 'dollar') => {
  if (!amount) return '0';
  const formatted = parseFloat(amount).toLocaleString('en-US', { maximumFractionDigits: 0 });
  return formatted + ' ' + (currency === 'dollar' ? '$' : 'دينار');
};

// تحديد لون الخلفية حسب عدد السيارات
const getCompanyBackgroundColor = (carsCount) => {
  const count = carsCount || 0;
  if (count === 0) {
    return 'bg-gray-50 dark:bg-gray-800 border-gray-200 dark:border-gray-700';
  } else if (count <= 10) {
    return 'bg-blue-50 dark:bg-blue-900/20 border-blue-200 dark:border-blue-800';
  } else if (count <= 30) {
    return 'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800';
  } else if (count <= 50) {
    return 'bg-yellow-50 dark:bg-yellow-900/20 border-yellow-200 dark:border-yellow-800';
  } else if (count <= 100) {
    return 'bg-orange-50 dark:bg-orange-900/20 border-orange-200 dark:border-orange-800';
  } else {
    return 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800';
  }
};

// تحديد لون النص حسب عدد السيارات
const getCompanyTextColor = (carsCount) => {
  const count = carsCount || 0;
  if (count === 0) {
    return 'text-gray-700 dark:text-gray-300';
  } else if (count <= 10) {
    return 'text-blue-900 dark:text-blue-100';
  } else if (count <= 30) {
    return 'text-green-900 dark:text-green-100';
  } else if (count <= 50) {
    return 'text-yellow-900 dark:text-yellow-100';
  } else if (count <= 100) {
    return 'text-orange-900 dark:text-orange-100';
  } else {
    return 'text-red-900 dark:text-red-100';
  }
};

const expenseTypeNames = {
  shipping: 'شحن',
  fuel: 'وقود',
  port: 'ميناء',
  customs: 'جمارك',
  other: 'أخرى',
};

const handleFileUploaded = () => {
  loadCompanies();
  toast.success('تم استيراد الملف بنجاح');
};

// جلب سيارات شركة معينة
const loadCompanyCars = async (tripCompanyId) => {
  if (companyCars.value[tripCompanyId]) {
    return; // تم تحميلها مسبقاً
  }

  try {
    const response = await axios.get(`/api/trips/${props.trip.id}/companies/${tripCompanyId}/cars`);
    companyCars.value[tripCompanyId] = response.data;
  } catch (error) {
    console.error(error);
    toast.error('حدث خطأ أثناء جلب السيارات');
  }
};

// فتح نموذج إضافة سيارة
const openAddCarForm = (tripCompanyId) => {
  selectedCarForm.value = {
    tripCompanyId: tripCompanyId,
    car: null,
  };
  showCarForm.value = true;
};

// فتح نموذج تعديل سيارة
const openEditCarForm = (car, tripCompanyId) => {
  selectedCarForm.value = {
    tripCompanyId: tripCompanyId,
    car: car,
  };
  showCarForm.value = true;
};

// إغلاق نموذج السيارة
const closeCarForm = () => {
  showCarForm.value = false;
  selectedCarForm.value = { tripCompanyId: null, car: null };
};

// معالجة حفظ السيارة
const handleCarSaved = (savedCar) => {
  const tripCompanyId = selectedCarForm.value.tripCompanyId;
  if (selectedCarForm.value.car) {
    // تحديث
    const index = companyCars.value[tripCompanyId]?.findIndex(c => c.id === savedCar.id);
    if (index !== undefined && index !== -1) {
      companyCars.value[tripCompanyId][index] = savedCar;
    }
  } else {
    // إضافة
    if (!companyCars.value[tripCompanyId]) {
      companyCars.value[tripCompanyId] = [];
    }
    companyCars.value[tripCompanyId].unshift(savedCar);
  }
  closeCarForm();
  loadCompanies(); // تحديث عدد السيارات
};

// حذف سيارة
const deleteCar = async (carId, tripCompanyId) => {
  if (!confirm('هل أنت متأكد من حذف هذه السيارة؟')) return;
  
  try {
    await axios.delete(`/api/trips/cars/${carId}`);
    if (companyCars.value[tripCompanyId]) {
      companyCars.value[tripCompanyId] = companyCars.value[tripCompanyId].filter(c => c.id !== carId);
    }
    toast.success('تم حذف السيارة بنجاح');
    loadCompanies(); // تحديث عدد السيارات
  } catch (error) {
    console.error(error);
    toast.error('حدث خطأ أثناء حذف السيارة');
  }
};

// تصدير سيارات شركة إلى Excel
const exportCompanyCars = async (tripCompanyId) => {
  try {
    const response = await axios.get(`/api/trips/${props.trip.id}/export-excel`, {
      params: { trip_company_id: tripCompanyId },
      responseType: 'blob',
    });
    
    // إنشاء رابط تحميل
    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    
    // الحصول على اسم الملف من الـ response headers
    const contentDisposition = response.headers['content-disposition'];
    let fileName = `trip_${props.trip.id}_cars_${Date.now()}.xlsx`;
    if (contentDisposition) {
      const fileNameMatch = contentDisposition.match(/filename="?(.+)"?/i);
      if (fileNameMatch && fileNameMatch[1]) {
        fileName = fileNameMatch[1];
      }
    }
    
    link.setAttribute('download', fileName);
    document.body.appendChild(link);
    link.click();
    link.remove();
    window.URL.revokeObjectURL(url);
    
    toast.success('تم تصدير السيارات بنجاح');
  } catch (error) {
    console.error(error);
    toast.error('حدث خطأ أثناء تصدير الملف');
  }
};

// استيراد سيارات شركة من ملف Excel
const importCompanyCars = (tripCompanyId) => {
  // إنشاء input file مخفي
  const input = document.createElement('input');
  input.type = 'file';
  input.accept = '.xlsx,.xls';
  input.onchange = async (e) => {
    const file = e.target.files[0];
    if (!file) return;

    // التحقق من نوع الملف
    const validTypes = ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'];
    if (!validTypes.includes(file.type) && !file.name.match(/\.(xlsx|xls)$/i)) {
      toast.error('يرجى اختيار ملف Excel صالح (.xlsx أو .xls)');
      return;
    }

    isImporting.value = true;
    const formData = new FormData();
    formData.append('file', file);
    formData.append('trip_company_id', tripCompanyId);

    try {
      const response = await axios.post(`/api/trips/${props.trip.id}/import-excel`, formData, {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      });

      if (response.data.success) {
        // عرض رسالة النجاح مع عدد السيارات المتخطاة
        const message = response.data.message || 'تم استيراد الملف بنجاح';
        const skippedDuplicates = response.data.skipped_duplicates || 0;
        
        if (skippedDuplicates > 0) {
          toast.success(`${message} (تم تخطي ${skippedDuplicates} سيارة موجودة مسبقاً)`);
        } else {
          toast.success(message);
        }
        
        // تحديث قائمة السيارات
        await loadCompanyCars(tripCompanyId);
        // تحديث قائمة الشركات
        await loadCompanies();
      } else {
        toast.error(response.data.message || 'حدث خطأ أثناء استيراد الملف');
      }
    } catch (error) {
      console.error(error);
      const errorMessage = error.response?.data?.message || 'حدث خطأ أثناء استيراد الملف';
      toast.error(errorMessage);
    } finally {
      isImporting.value = false;
    }
  };
  input.click();
};

const handleExpenseAdded = () => {
  loadExpenses();
  toast.success('تم إضافة المصاريف بنجاح');
};

const handleExpenseDeleted = (expenseId) => {
  expenses.value = expenses.value.filter(e => e.id !== expenseId);
  toast.success('تم حذف المصاريف بنجاح');
};

const deleteExpense = async (expenseId) => {
  if (!confirm('هل أنت متأكد من حذف هذه المصاريف؟')) return;
  
  try {
    await axios.delete(`/api/trips/expenses/${expenseId}`);
    handleExpenseDeleted(expenseId);
  } catch (error) {
    toast.error('حدث خطأ أثناء حذف المصاريف');
  }
};

const totalExpensesByType = computed(() => {
  const totals = {};
  expenses.value.forEach(expense => {
    if (!totals[expense.expense_type]) {
      totals[expense.expense_type] = { dollar: 0, dinar: 0 };
    }
    totals[expense.expense_type][expense.currency] += parseFloat(expense.amount);
  });
  return totals;
});
</script>

<template>
  <Head :title="`رحلة: ${trip.ship_name}`" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div class="flex-1">
          <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ trip.ship_name }}
          </h2>
          <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
            {{ trip.voy_no ? `رقم الرحلة: ${trip.voy_no}` : '' }}
          </p>
        </div>
        <Link
          :href="route('trips')"
          class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150 w-full sm:w-auto justify-center"
        >
          العودة للقائمة
        </Link>
      </div>
    </template>

    <div class="py-6 sm:py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Stats Cards - Responsive Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4 mb-6">
          <!-- Total Cars -->
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 sm:p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
              </div>
              <div class="mr-4 flex-1">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">السيارات</p>
                <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ stats.total_cars || 0 }}</p>
              </div>
            </div>
          </div>

          <!-- Total Companies -->
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 sm:p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
              </div>
              <div class="mr-4 flex-1">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">الشركات</p>
                <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ stats.total_companies || 0 }}</p>
              </div>
            </div>
          </div>

          <!-- Total Consignees -->
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 sm:p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
              </div>
              <div class="mr-4 flex-1">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">العملاء</p>
                <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ stats.total_consignees || 0 }}</p>
              </div>
            </div>
          </div>

          <!-- Total Weight -->
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 sm:p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3" />
                </svg>
              </div>
              <div class="mr-4 flex-1">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">الوزن</p>
                <p class="text-lg font-semibold text-gray-900 dark:text-white">
                  {{ stats.total_weight ? (stats.total_weight/1000) : '0' }} طن
                </p>
              </div>
            </div>
          </div>

          <!-- Expenses Dollar -->
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 sm:p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <div class="mr-4 flex-1">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">المصاريف ($)</p>
                <p class="text-lg font-semibold text-gray-900 dark:text-white">
                  {{ formatCurrency(stats.total_expenses_dollar, 'dollar') }}
                </p>
              </div>
            </div>
          </div>

          <!-- Expenses Dinar -->
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 sm:p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <div class="mr-4 flex-1">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">المصاريف (دينار)</p>
                <p class="text-lg font-semibold text-gray-900 dark:text-white">
                  {{ formatCurrency(stats.total_expenses_dinar, 'dinar') }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Tabs Navigation - Responsive -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow mb-6">
          <div class="border-b border-gray-200 dark:border-gray-700">
            <nav class="-mb-px flex flex-wrap" aria-label="Tabs">
              <button
                @click="activeTab = 'overview'"
                :class="[
                  activeTab === 'overview'
                    ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300',
                  'whitespace-nowrap py-4 px-4 sm:px-6 border-b-2 font-medium text-sm flex-1 sm:flex-none text-center'
                ]"
              >
                نظرة عامة
              </button>
              <button
                @click="activeTab = 'companies'"
                :class="[
                  activeTab === 'companies'
                    ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300',
                  'whitespace-nowrap py-4 px-4 sm:px-6 border-b-2 font-medium text-sm flex-1 sm:flex-none text-center'
                ]"
              >
                الشركات
              </button>
              <button
                @click="activeTab = 'cars'"
                :class="[
                  activeTab === 'cars'
                    ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300',
                  'whitespace-nowrap py-4 px-4 sm:px-6 border-b-2 font-medium text-sm flex-1 sm:flex-none text-center'
                ]"
              >
                السيارات
              </button>
              <button
                @click="activeTab = 'expenses'"
                :class="[
                  activeTab === 'expenses'
                    ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300',
                  'whitespace-nowrap py-4 px-4 sm:px-6 border-b-2 font-medium text-sm flex-1 sm:flex-none text-center'
                ]"
              >
                المصاريف
              </button>
            </nav>
          </div>

          <!-- Tab Content -->
          <div class="p-4 sm:p-6">
            <!-- Overview Tab -->
            <div v-show="activeTab === 'overview'" class="space-y-6">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Trip Details -->
                <div>
                  <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">تفاصيل الرحلة</h3>
                  <dl class="space-y-3">
                    <div class="flex flex-col sm:flex-row sm:items-center">
                      <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 sm:w-32">تاريخ الإبحار:</dt>
                      <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:flex-1">{{ formatDate(trip.sailing_date) }}</dd>
                    </div>
                    <div class="flex flex-col sm:flex-row sm:items-center">
                      <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 sm:w-32">اسم السفينة:</dt>
                      <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:flex-1">{{ trip.ship_name }}</dd>
                    </div>
                    <div v-if="trip.voy_no" class="flex flex-col sm:flex-row sm:items-center">
                      <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 sm:w-32">رقم الرحلة:</dt>
                      <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:flex-1">{{ trip.voy_no }}</dd>
                    </div>
                    <div v-if="trip.captain" class="flex flex-col sm:flex-row sm:items-center">
                      <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 sm:w-32">القبطان:</dt>
                      <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:flex-1">{{ trip.captain }}</dd>
                    </div>
                    <div class="flex flex-col sm:flex-row sm:items-center">
                      <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 sm:w-32">ميناء التحميل:</dt>
                      <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:flex-1">{{ trip.pol }}</dd>
                    </div>
                    <div class="flex flex-col sm:flex-row sm:items-center">
                      <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 sm:w-32">ميناء التفريغ:</dt>
                      <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:flex-1">{{ trip.pod }}</dd>
                    </div>
                    <div v-if="trip.flag" class="flex flex-col sm:flex-row sm:items-center">
                      <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 sm:w-32">علم السفينة:</dt>
                      <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:flex-1">{{ trip.flag }}</dd>
                    </div>
                    <div v-if="trip.note" class="flex flex-col">
                      <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">ملاحظات:</dt>
                      <dd class="text-sm text-gray-900 dark:text-white">{{ trip.note }}</dd>
                    </div>
                  </dl>
                </div>
              </div>
            </div>

            <!-- Companies Tab -->
            <div v-show="activeTab === 'companies'" class="space-y-6">
              <!-- Section: Add Company & Upload Excel -->
              <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 sm:p-6 border border-blue-200 dark:border-blue-800">
                <div class="flex items-center mb-4">
                  <svg class="w-6 h-6 text-blue-600 dark:text-blue-400 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                  </svg>
                  <h3 class="text-lg font-semibold text-blue-900 dark:text-blue-200">إضافة شركة ورفع ملف Excel</h3>
                </div>
                <p class="text-sm text-blue-700 dark:text-blue-300 mb-4">
                  اختر شركة وارفع ملف Excel يحتوي على بيانات السيارات للشركة
                </p>
                <TripCompanyUpload :trip-id="trip.id" @uploaded="handleFileUploaded" />
              </div>
              
              <!-- Section: Companies List -->
              <div v-if="companies.length > 0">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                  الشركات المرتبطة بالرحلة ({{ companies.length }})
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                  <div
                    v-for="company in companies"
                    :key="company.id"
                    :class="['rounded-lg shadow p-4 sm:p-6 border-2 hover:shadow-xl transition-all duration-200', getCompanyBackgroundColor(company.cars_count)]"
                  >
                    <div class="flex items-start justify-between mb-3">
                      <h4 :class="['text-lg font-bold', getCompanyTextColor(company.cars_count)]">
                        {{ company.company?.name || 'شركة غير معروفة' }}
                      </h4>
                      <span :class="['inline-flex items-center px-3 py-1 rounded-full text-sm font-bold', 
                        company.cars_count === 0 ? 'bg-gray-200 text-gray-800 dark:bg-gray-700 dark:text-gray-200' :
                        company.cars_count <= 10 ? 'bg-blue-200 text-blue-900 dark:bg-blue-800 dark:text-blue-100' :
                        company.cars_count <= 30 ? 'bg-green-200 text-green-900 dark:bg-green-800 dark:text-green-100' :
                        company.cars_count <= 50 ? 'bg-yellow-200 text-yellow-900 dark:bg-yellow-800 dark:text-yellow-100' :
                        company.cars_count <= 100 ? 'bg-orange-200 text-orange-900 dark:bg-orange-800 dark:text-orange-100' :
                        'bg-red-200 text-red-900 dark:bg-red-800 dark:text-red-100'
                      ]">
                        {{ company.cars_count || 0 }} سيارة
                      </span>
                    </div>
                    <div :class="['space-y-2 text-sm', 
                      company.cars_count === 0 ? 'text-gray-600 dark:text-gray-400' :
                      company.cars_count <= 10 ? 'text-blue-700 dark:text-blue-300' :
                      company.cars_count <= 30 ? 'text-green-700 dark:text-green-300' :
                      company.cars_count <= 50 ? 'text-yellow-700 dark:text-yellow-300' :
                      company.cars_count <= 100 ? 'text-orange-700 dark:text-orange-300' :
                      'text-red-700 dark:text-red-300'
                    ]">
                      <p v-if="company.company?.phone" class="flex items-center">
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                        </svg>
                        {{ company.company.phone }}
                      </p>
                      <p v-if="company.uploaded_at" class="flex items-center">
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        تاريخ الرفع: {{ formatDate(company.uploaded_at) }}
                      </p>
                    </div>
                    
                    <!-- Shipping Price Section -->
                    <div class="mt-4 p-3 bg-white dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600">
                      <div class="flex items-center justify-between mb-2">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                          سعر الشحن لكل سيارة:
                        </label>
                        <button
                          v-if="!company.editingPrice"
                          @click="company.editingPrice = true"
                          class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 text-sm"
                        >
                          {{ company.shipping_price_per_car ? 'تعديل' : 'إضافة' }}
                        </button>
                      </div>
                      <div v-if="company.editingPrice" class="flex gap-2">
                        <input
                          v-model.number="company.tempShippingPrice"
                          type="number"
                           min="0"
                          placeholder="0"
                          class="flex-1 px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-blue-500 dark:bg-gray-800 dark:text-white"
                          @keyup.enter="updateShippingPrice(company)"
                        />
                        <button
                          @click="updateShippingPrice(company)"
                          class="px-3 py-2 text-sm text-white bg-green-600 rounded-md hover:bg-green-700"
                        >
                          حفظ
                        </button>
                        <button
                          @click="company.editingPrice = false; company.tempShippingPrice = company.shipping_price_per_car"
                          class="px-3 py-2 text-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700"
                        >
                          إلغاء
                        </button>
                      </div>
                      <div v-else class="flex items-center justify-between">
                        <span class="text-lg font-bold" :class="getCompanyTextColor(company.cars_count)">
                          {{ company.shipping_price_per_car ? formatCurrency(company.shipping_price_per_car) : 'غير محدد' }}
                        </span>
                        <span v-if="company.shipping_price_per_car && company.cars_count" class="text-sm font-semibold" :class="getCompanyTextColor(company.cars_count)">
                          المجموع: {{ formatCurrency(company.shipping_price_per_car * company.cars_count)  }}
                        </span> 
                      </div>
                    </div>
                    
                    <!-- Actions -->
                    <div class="mt-4 flex gap-2 flex-wrap">
                      <button
                        @click="loadCompanyCars(company.id); company.showCars = !company.showCars"
                        class="flex-1 px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-600"
                      >
                        {{ company.showCars ? 'إخفاء السيارات' : 'عرض السيارات' }}
                      </button>
                      <button
                        @click="openAddCarForm(company.id)"
                        class="flex-1 px-3 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700"
                      >
                        إضافة سيارة
                      </button>
                      <button
                        @click="exportCompanyCars(company.id)"
                        class="flex-1 px-3 py-2 text-sm font-medium text-white bg-green-600 border border-transparent rounded-md hover:bg-green-700"
                        title="تصدير السيارات إلى Excel"
                      >
                        تصدير Excel
                      </button>
                      <button
                        @click="importCompanyCars(company.id)"
                        class="flex-1 px-3 py-2 text-sm font-medium text-white bg-blue-500 border border-transparent rounded-md hover:bg-blue-600"
                        title="استيراد سيارات من ملف Excel"
                      >
                        استيراد Excel
                      </button>
                    </div>

                    <!-- Cars List -->
                    <div v-if="company.showCars && companyCars[company.id]" class="mt-4 border-t border-gray-200 dark:border-gray-700 pt-4">
                      <h5 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">
                        السيارات ({{ companyCars[company.id]?.length || 0 }})
                      </h5>
                      <div v-if="companyCars[company.id] && companyCars[company.id].length > 0" class="space-y-2">
                        <div
                          v-for="car in companyCars[company.id]"
                          :key="car.id"
                          class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600"
                        >
                          <div class="flex items-start justify-between">
                            <div class="flex-1">
                              <div class="grid grid-cols-2 gap-2 text-sm">
                                <div v-if="car.weight">
                                  <span class="text-gray-500 dark:text-gray-400">الوزن:</span>
                                  <span class="text-gray-900 dark:text-white mr-1">{{ car.weight }} كغ</span>
                                </div>
                                <div v-if="car.description">
                                  <span class="text-gray-500 dark:text-gray-400">الوصف:</span>
                                  <span class="text-gray-900 dark:text-white mr-1">{{ car.description }}</span>
                                </div>
                                <div v-if="car.chassis_no">
                                  <span class="text-gray-500 dark:text-gray-400">الشاسيه:</span>
                                  <span class="text-gray-900 dark:text-white mr-1">{{ car.chassis_no }}</span>
                                </div>
                                <div v-if="car.code">
                                  <span class="text-gray-500 dark:text-gray-400">الكود:</span>
                                  <span class="text-gray-900 dark:text-white mr-1">{{ car.code }}</span>
                                </div>
                                <div v-if="car.consignee_name" class="col-span-2">
                                  <span class="text-gray-500 dark:text-gray-400">المستلم:</span>
                                  <span class="text-gray-900 dark:text-white mr-1">{{ car.consignee_name }}</span>
                                </div>
                              </div>
                            </div>
                            <div class="flex gap-2 mr-3">
                              <button
                                @click="openEditCarForm(car, company.id)"
                                class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
                                title="تعديل"
                              >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                              </button>
                              <button
                                @click="deleteCar(car.id, company.id)"
                                class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300"
                                title="حذف"
                              >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                              </button>
                            </div>
                          </div>
                        </div>
                      </div>
                      <div v-else class="text-center py-4 text-sm text-gray-500 dark:text-gray-400">
                        لا توجد سيارات لهذه الشركة
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div v-else class="text-center py-12 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-2">لا توجد شركات مرتبطة بهذه الرحلة</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">ابدأ بإضافة شركة ورفع ملف Excel أعلاه</p>
              </div>
            </div>

            <!-- Cars Tab -->
            <div v-show="activeTab === 'cars'">
              <div v-if="carsByConsignee && carsByConsignee.length > 0" class="space-y-6">
                <div
                  v-for="group in carsByConsignee"
                  :key="group.consignee.id"
                  class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 sm:p-6"
                >
                  <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4">
                    <div>
                      <h4 class="text-lg font-semibold text-gray-900 dark:text-white">
                        {{ group.consignee.name }}
                      </h4>
                      <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                        عدد السيارات: {{ group.cars_count }} | الرصيد: {{ formatCurrency(group.balance, 'dollar') }}
                      </p>
                    </div>
                  </div>
                  
                  <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                      <thead class="bg-gray-100 dark:bg-gray-800">
                        <tr>
                          <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">الوزن</th>
                          <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">الوصف</th>
                          <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">رقم الشاسيه</th>
                          <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">الكود</th>
                        </tr>
                      </thead>
                      <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                        <tr v-for="car in group.cars" :key="car.id">
                          <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ car.weight || '-' }}</td>
                          <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ car.description || '-' }}</td>
                          <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ car.chassis_no || '-' }}</td>
                          <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ car.code || '-' }}</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
              <div v-else class="text-center py-12 text-gray-500 dark:text-gray-400">
                لا توجد سيارات في هذه الرحلة
              </div>
            </div>

            <!-- Expenses Tab -->
            <div v-show="activeTab === 'expenses'">
              <div class="mb-6">
                <TripExpenseForm :trip-id="trip.id" @expense-added="handleExpenseAdded" />
              </div>

              <div v-if="expenses.length > 0" class="space-y-4">
                <div class="overflow-x-auto">
                  <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-100 dark:bg-gray-800">
                      <tr>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">التاريخ</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">النوع</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">المبلغ</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">الملاحظات</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">العمليات</th>
                      </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                      <tr v-for="expense in expenses" :key="expense.id">
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ formatDate(expense.date) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ expenseTypeNames[expense.expense_type] || expense.expense_type }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ formatCurrency(expense.amount, expense.currency) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ expense.note || '-' }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm">
                          <button
                            @click="deleteExpense(expense.id)"
                            class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                          >
                            حذف
                          </button>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>

                <!-- Expenses Summary -->
                <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                    <h4 class="font-semibold text-gray-900 dark:text-white mb-3">المجموع حسب النوع</h4>
                    <div class="space-y-2">
                      <div v-for="(total, type) in totalExpensesByType" :key="type" class="flex justify-between text-sm">
                        <span class="text-gray-600 dark:text-gray-400">{{ expenseTypeNames[type] || type }}:</span>
                        <span class="font-medium text-gray-900 dark:text-white">
                          ${{ total.dollar.toLocaleString('en-US') }} / {{ total.dinar.toLocaleString('en-US') }} دينار
                        </span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div v-else class="text-center py-12 text-gray-500 dark:text-gray-400">
                لا توجد مصاريف مسجلة
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Car Form Modal -->
    <TripCarForm
      v-if="selectedCarForm.tripCompanyId"
      :trip-id="trip.id"
      :trip-company-id="selectedCarForm.tripCompanyId"
      :car="selectedCarForm.car"
      :show="showCarForm"
      @close="closeCarForm"
      @saved="handleCarSaved"
    />
  </AuthenticatedLayout>
</template>

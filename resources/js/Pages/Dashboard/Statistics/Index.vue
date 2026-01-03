<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/inertia-vue3';
import { ref, onMounted, watch, computed } from 'vue';
import axios from 'axios';
import StatCards from '@/Components/Dashboard/StatCards.vue';
import ProfitChart from '@/Components/Dashboard/ProfitChart.vue';
import DiscountTable from '@/Components/Dashboard/DiscountTable.vue';
import CarProfitTable from '@/Components/Dashboard/CarProfitTable.vue';
import TransfersSummary from '@/Components/Dashboard/TransfersSummary.vue';
import TransfersDetailTable from '@/Components/Dashboard/TransfersDetailTable.vue';
import CashFlowCards from '@/Components/Dashboard/CashFlowCards.vue';
import CashFlowChart from '@/Components/Dashboard/CashFlowChart.vue';
import YearClosingSummary from '@/Components/Dashboard/YearClosingSummary.vue';
import Filters from '@/Components/Dashboard/Filters.vue';
import CustomDifferenceTable from '@/Components/Dashboard/CustomDifferenceTable.vue';
import TradersProfitTable from '@/Components/Dashboard/TradersProfitTable.vue';

const statistics = ref({});
const loading = ref(false);
const year = ref(new Date().getFullYear());
const selectedYears = ref([]);
const month = ref(null);
const activeTab = ref('overview'); // overview, transfers, profits, discounts, traders, payments
const tradersProfit = ref([]);
const recalculating = ref(false);
const transferDateFrom = ref(null);
const transferDateTo = ref(null);
const exportingTransfers = ref(false);
const exportingStatistics = ref(false);
const exportingPayments = ref(false);

// Generate years list (only available years)
const years = computed(() => {
  const currentYear = new Date().getFullYear();
  const yearsList = [];
  // Only include years from 2023 onwards (excluding 2022, 2021, 2020, 2019, 2018, 2017)
  for (let i = 0; i < 10; i++) {
    const year = currentYear - i;
    if (year >= 2023) {
      yearsList.push(year);
    }
  }
  return yearsList;
});

const fetchStatistics = async () => {
  loading.value = true;
  try {
    const params = {};
    
    // إرسال year فقط إذا كانت محددة
    if (year.value) {
      params.year = year.value;
    }
    
    // إرسال years (array) إذا كانت محددة
    if (selectedYears.value && selectedYears.value.length > 0) {
      params.years = selectedYears.value;
    }
    
    // إرسال month فقط إذا كانت محددة وليست null
    if (month.value !== null && month.value !== undefined && month.value !== '') {
      params.month = month.value;
    }
    
    console.log('Fetching statistics with params:', params);
    const response = await axios.get('/api/statistics', { params });
    console.log('Statistics API Response:', response.data);
    statistics.value = response.data;
  } catch (error) {
    console.error('Error fetching statistics:', error);
    console.error('Error response:', error.response);
    console.error('Error message:', error.message);
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchStatistics();
});

watch([year, selectedYears, month], () => {
  fetchStatistics();
});

const recalculateProfit = async () => {
  recalculating.value = true;
  try {
    const params = {};
    if (year.value) {
      params.year = year.value;
    }
    if (selectedYears.value && selectedYears.value.length > 0) {
      params.years = selectedYears.value;
    }
    if (month.value !== null && month.value !== undefined && month.value !== '') {
      params.month = month.value;
    }
    
    const response = await axios.post('/api/statistics/recalculate-profit', params);
    alert(`تم تحديث ${response.data.updated_cars} سيارة من ${response.data.total_cars} سيارة`);
    // إعادة تحميل الإحصائيات
    await fetchStatistics();
  } catch (error) {
    console.error('Error recalculating profit:', error);
    alert('حدث خطأ أثناء إعادة حساب الربح');
  } finally {
    recalculating.value = false;
  }
};

const fetchTradersProfit = async () => {
  try {
    const params = {};
    if (year.value) {
      params.year = year.value;
    }
    if (selectedYears.value && selectedYears.value.length > 0) {
      params.years = selectedYears.value;
    }
    if (month.value !== null && month.value !== undefined && month.value !== '') {
      params.month = month.value;
    }
    
    const response = await axios.get('/api/statistics/traders-profit', { params });
    tradersProfit.value = response.data.traders || [];
  } catch (error) {
    console.error('Error fetching traders profit:', error);
    tradersProfit.value = [];
  }
};

watch([activeTab], () => {
  if (activeTab.value === 'traders') {
    fetchTradersProfit();
  }
});

const exportTransfersToExcel = async () => {
  exportingTransfers.value = true;
  try {
    const params = {};
    
    if (transferDateFrom.value) {
      params.from = transferDateFrom.value;
    }
    if (transferDateTo.value) {
      params.to = transferDateTo.value;
    }
    if (year.value) {
      params.year = year.value;
    }
    if (selectedYears.value && selectedYears.value.length > 0) {
      params.years = selectedYears.value;
    }
    if (month.value !== null && month.value !== undefined && month.value !== '') {
      params.month = month.value;
    }
    
    const response = await axios.get('/api/statistics/export-transfers-excel', { 
      params,
      responseType: 'blob'
    });
    
    // إنشاء رابط تحميل
    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    
    // الحصول على اسم الملف من الـ header أو استخدام اسم افتراضي
    const contentDisposition = response.headers['content-disposition'];
    let filename = 'الحولات.xlsx';
    if (contentDisposition) {
      const filenameMatch = contentDisposition.match(/filename="?(.+)"?/i);
      if (filenameMatch && filenameMatch[1]) {
        filename = filenameMatch[1];
      }
    }
    
    link.setAttribute('download', filename);
    document.body.appendChild(link);
    link.click();
    link.remove();
    window.URL.revokeObjectURL(url);
  } catch (error) {
    console.error('Error exporting transfers:', error);
    alert('حدث خطأ أثناء تصدير الحولات');
  } finally {
    exportingTransfers.value = false;
  }
};

const exportStatisticsToExcel = async () => {
  exportingStatistics.value = true;
  try {
    const params = {};
    
    if (year.value) {
      params.year = year.value;
    }
    if (selectedYears.value && selectedYears.value.length > 0) {
      params.years = selectedYears.value;
    }
    if (month.value !== null && month.value !== undefined && month.value !== '') {
      params.month = month.value;
    }
    
    const response = await axios.get('/api/statistics/export-excel', { 
      params,
      responseType: 'blob'
    });
    
    // إنشاء رابط تحميل
    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    
    // الحصول على اسم الملف من الـ header أو استخدام اسم افتراضي
    const contentDisposition = response.headers['content-disposition'];
    let filename = 'الإحصائيات_العامة.xlsx';
    if (contentDisposition) {
      const filenameMatch = contentDisposition.match(/filename="?(.+)"?/i);
      if (filenameMatch && filenameMatch[1]) {
        filename = decodeURIComponent(filenameMatch[1]);
      }
    }
    
    link.setAttribute('download', filename);
    document.body.appendChild(link);
    link.click();
    link.remove();
    window.URL.revokeObjectURL(url);
  } catch (error) {
    console.error('Error exporting statistics:', error);
    alert('حدث خطأ أثناء تصدير الإحصائيات');
  } finally {
    exportingStatistics.value = false;
  }
};

const exportPaymentsToExcel = async () => {
  exportingPayments.value = true;
  try {
    const params = {};
    
    if (year.value) {
      params.year = year.value;
    }
    if (selectedYears.value && selectedYears.value.length > 0) {
      params.years = selectedYears.value;
    }
    if (month.value !== null && month.value !== undefined && month.value !== '') {
      params.month = month.value;
    }
    
    const response = await axios.get('/api/statistics/export-payments-excel', { 
      params,
      responseType: 'blob'
    });
    
    // إنشاء رابط تحميل
    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    
    // الحصول على اسم الملف من الـ header أو استخدام اسم افتراضي
    const contentDisposition = response.headers['content-disposition'];
    let filename = 'دفعات_التجار.xlsx';
    if (contentDisposition) {
      const filenameMatch = contentDisposition.match(/filename="?(.+)"?/i);
      if (filenameMatch && filenameMatch[1]) {
        filename = decodeURIComponent(filenameMatch[1]);
      }
    }
    
    link.setAttribute('download', filename);
    document.body.appendChild(link);
    link.click();
    link.remove();
    window.URL.revokeObjectURL(url);
  } catch (error) {
    console.error('Error exporting payments:', error);
    alert('حدث خطأ أثناء تصدير دفعات التجار');
  } finally {
    exportingPayments.value = false;
  }
};
</script>

<template>
  <Head title="إحصائيات Dashboard" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
        إحصائيات Dashboard
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Filters -->
        <div class="mb-6">
          <Filters
            :selected-year="year"
            :selected-years="selectedYears"
            :selected-month="month"
            :years="years"
            @update:selectedYear="year = $event"
            @update:selectedYears="selectedYears = $event"
            @update:selectedMonth="month = $event"
          />
        </div>

        <!-- Action Buttons -->
        <div class="mb-4 flex justify-end gap-4">
          <button
            v-if="activeTab === 'overview'"
            @click="exportStatisticsToExcel"
            :disabled="exportingStatistics"
            class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <span v-if="exportingStatistics">جاري التصدير...</span>
            <span v-else>تصدير Excel</span>
          </button>
          <button
            v-if="activeTab === 'payments'"
            @click="exportPaymentsToExcel"
            :disabled="exportingPayments"
            class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <span v-if="exportingPayments">جاري التصدير...</span>
            <span v-else>تصدير Excel</span>
          </button>
          <button
            @click="recalculateProfit"
            :disabled="recalculating"
            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <span v-if="recalculating">جاري الحساب...</span>
            <span v-else>إعادة حساب الربح</span>
          </button>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="text-center py-12 bg-white dark:bg-gray-800 rounded-lg shadow">
          <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-gray-900 dark:border-gray-100"></div>
          <p class="mt-4 text-gray-600 dark:text-gray-400">جاري تحميل البيانات...</p>
        </div>

        <!-- Statistics -->
        <div v-else>
          <!-- Tabs -->
          <div class="mb-6 border-b border-gray-200 dark:border-gray-700">
            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
              <button
                @click="activeTab = 'overview'"
                :class="[
                  activeTab === 'overview'
                    ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400'
                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300',
                  'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
                ]"
              >
                نظرة عامة
              </button>
              <button
                @click="activeTab = 'transfers'"
                :class="[
                  activeTab === 'transfers'
                    ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400'
                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300',
                  'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
                ]"
              >
                الحولات
              </button>
              <button
                @click="activeTab = 'profits'"
                :class="[
                  activeTab === 'profits'
                    ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400'
                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300',
                  'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
                ]"
              >
                الأرباح
              </button>
              <button
                @click="activeTab = 'discounts'"
                :class="[
                  activeTab === 'discounts'
                    ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400'
                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300',
                  'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
                ]"
              >
                الخصومات
              </button>
              <button
                @click="activeTab = 'traders'"
                :class="[
                  activeTab === 'traders'
                    ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400'
                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300',
                  'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
                ]"
              >
                أرباح التجار
              </button>
              <button
                @click="activeTab = 'payments'"
                :class="[
                  activeTab === 'payments'
                    ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400'
                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300',
                  'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
                ]"
              >
                دفعات التجار
              </button>
            </nav>
          </div>

          <!-- Overview Tab -->
          <div v-show="activeTab === 'overview'" class="space-y-6">
            <!-- Stat Cards -->
            <StatCards
              :cars-count="statistics.cars_count || 0"
              :total-customs="statistics.total_customs || 0"
              :exchange-profit="statistics.exchange_profit || 0"
              :net-profit="statistics.net_profit || 0"
              :net-transfers="statistics.net_transfers || 0"
              :total-sales="statistics.total_sales || 0"
              :total-purchases="statistics.total_purchases || 0"
              :sales-purchase-difference="statistics.sales_purchase_difference || 0"
              :traders-debt="statistics.traders_debt || 0"
              :traders-payments="statistics.traders_payments || 0"
              :total-payments-and-debt="statistics.total_payments_and_debt || 0"
              :sales-vs-payments-difference="statistics.sales_vs_payments_difference || 0"
            />


            <!-- Charts Row -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
              <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                  <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">
                    الأرباح الشهرية
                  </h3>
                  <ProfitChart
                    v-if="statistics.monthly_profits && statistics.month_labels"
                    :labels="statistics.month_labels"
                    :monthly-profit="statistics.monthly_profits"
                    :yearly-profit="statistics.yearly_profit || 0"
                  />
                </div>
              </div>

              <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                  <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">
                    التدفقات النقدية الشهرية
                  </h3>
                  <CashFlowChart
                    v-if="statistics.cash_flow_chart"
                    :labels="statistics.cash_flow_chart.labels || []"
                    :cash-in-data="statistics.cash_flow_chart.cash_in_data || []"
                    :cash-out-data="statistics.cash_flow_chart.cash_out_data || []"
                  />
                </div>
              </div>
            </div>

            <!-- Year Closing Summary -->
            <div v-if="statistics.year_closing">
              <YearClosingSummary
                :year="statistics.year_closing.year"
                :total-income="statistics.year_closing.total_income || 0"
                :total-expenses="statistics.year_closing.total_expenses || 0"
                :total-discounts="statistics.year_closing.total_discounts || 0"
                :net-year-profit="statistics.year_closing.net_year_profit || 0"
                :carried-profit="statistics.year_closing.carried_profit || 0"
                :is-closed="statistics.year_closing.is_closed || false"
              />
            </div>

            <!-- Custom Difference Table -->
            <div v-if="statistics.custom && statistics.custom.cars_with_difference && statistics.custom.cars_with_difference.length > 0">
              <CustomDifferenceTable
                :cars="statistics.custom.cars_with_difference || []"
                :total-difference="statistics.custom.difference || 0"
              />
            </div>
          </div>

          <!-- Transfers Tab -->
          <div v-show="activeTab === 'transfers'" class="space-y-6">
            <!-- Filters for Transfers -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-4">
              <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    من تاريخ
                  </label>
                  <input
                    type="date"
                    v-model="transferDateFrom"
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    إلى تاريخ
                  </label>
                  <input
                    type="date"
                    v-model="transferDateTo"
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                  />
                </div>
                <div class="flex items-end">
                  <button
                    @click="exportTransfersToExcel"
                    :disabled="exportingTransfers"
                    class="w-full bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded disabled:opacity-50 disabled:cursor-not-allowed"
                  >
                    <span v-if="exportingTransfers">جاري التصدير...</span>
                    <span v-else>تصدير Excel</span>
                  </button>
                </div>
              </div>
            </div>

            <!-- Transfers Summary Cards -->
            <div v-if="statistics.transfers_summary">
              <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">
                ملخص الحولات
              </h3>
              <TransfersSummary
                :gross-transfers="statistics.transfers_summary.gross_transfers || 0"
                :transfer-fees="statistics.transfers_summary.transfer_fees || 0"
                :net-transfers="statistics.transfers_summary.net_transfers || 0"
                :erbil-transfers="statistics.transfers_summary.erbil_transfers || 0"
              />
            </div>

            <!-- Transfers Detail Table -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
              <div class="p-6">
                <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">
                  تفاصيل الحولات
                </h3>
                <TransfersDetailTable
                  v-if="statistics.transfers_summary && statistics.transfers_summary.details"
                  :transfers="statistics.transfers_summary.details"
                />
              </div>
            </div>
          </div>

          <!-- Profits Tab -->
          <div v-show="activeTab === 'profits'" class="space-y-6">
            <!-- أعلى 10 سيارات ربحاً -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
              <div class="p-6">
                <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">
                  أعلى 10 سيارات ربحاً
                </h3>
                <CarProfitTable
                  v-if="statistics.cars_with_profit"
                  :cars="statistics.cars_with_profit"
                  :max-profit="statistics.profit_stats?.max || 0"
                  :min-profit="statistics.profit_stats?.min || 0"
                  :avg-profit="statistics.profit_stats?.avg || 0"
                />
              </div>
            </div>

            <!-- أقل 10 سيارات ربحاً -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
              <div class="p-6">
                <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">
                  أقل 10 سيارات ربحاً
                </h3>
                <CarProfitTable
                  v-if="statistics.cars_with_lowest_profit"
                  :cars="statistics.cars_with_lowest_profit"
                  :max-profit="statistics.profit_stats?.max || 0"
                  :min-profit="statistics.profit_stats?.min || 0"
                  :avg-profit="statistics.profit_stats?.avg || 0"
                />
              </div>
            </div>
          </div>

          <!-- Discounts Tab -->
          <div v-show="activeTab === 'discounts'" class="space-y-6">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
              <div class="p-6">
                <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">
                  سجل الخصومات
                </h3>
                <DiscountTable
                  v-if="statistics.discount_records"
                  :discounts="statistics.discount_records"
                  :total-discounts="statistics.discount_stats?.total || 0"
                  :max-discount="statistics.discount_stats?.max || 0"
                  :min-discount="statistics.discount_stats?.min || 0"
                />
              </div>
            </div>
          </div>

          <!-- Traders Tab -->
          <div v-show="activeTab === 'traders'" class="space-y-6">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
              <div class="p-6">
                <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">
                  مجموع الربح من كل تاجر
                </h3>
                <TradersProfitTable
                  :traders="tradersProfit"
                />
              </div>
            </div>
          </div>

          <!-- Payments Tab -->
          <div v-show="activeTab === 'payments'" class="space-y-6">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
              <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">
                دفعات التجار
              </h3>
              <div class="mb-4 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                <p class="text-sm text-gray-700 dark:text-gray-300">
                  <strong>ملاحظة:</strong> يمكنك تصدير جميع دفعات التجار إلى ملف Excel باستخدام زر "تصدير Excel" أعلاه.
                </p>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">
                  الدفعات تشمل جميع المعاملات من نوع 'out' مع is_pay = 1 و amount &lt; 0
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

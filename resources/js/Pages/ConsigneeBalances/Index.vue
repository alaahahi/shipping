<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/inertia-vue3';
import { useToast } from "vue-toastification";
import axios from 'axios';
import { ref, computed, onMounted } from 'vue';

const toast = useToast();
const balances = ref([]);
const isLoading = ref(false);
const selectedConsignee = ref(null);
const showPaymentModal = ref(false);
const showReceiptModal = ref(false);
const selectedPayment = ref(null);
const paymentForm = ref({
  consignee_id: null,
  trip_id: null,
  amount: '',
  currency: 'dollar',
  notes: '',
  payment_date: new Date().toISOString().split('T')[0],
});

// Load balances
const loadBalances = async () => {
  isLoading.value = true;
  try {
    const response = await axios.get('/api/consignee-balances');
    if (response.data.success) {
      balances.value = response.data.balances;
    } else {
      toast.error(response.data.message || 'حدث خطأ أثناء جلب الأرصدة');
    }
  } catch (error) {
    console.error(error);
    toast.error('حدث خطأ أثناء جلب الأرصدة');
  } finally {
    isLoading.value = false;
  }
};

// Format currency
const formatCurrency = (amount, currency = 'dollar') => {
  if (!amount) return '0';
  const formatted = parseFloat(amount).toLocaleString('en-US', { maximumFractionDigits: 0 });
  return formatted + ' ' + (currency === 'dollar' ? '$' : 'دينار');
};

// Format date
const formatDate = (date) => {
  if (!date) return '';
  return new Date(date).toLocaleDateString('en-US');
};

// Get balance color class
const getBalanceColorClass = (balance) => {
  if (balance > 0) return 'text-red-600 dark:text-red-400';
  if (balance < 0) return 'text-green-600 dark:text-green-400';
  return 'text-gray-600 dark:text-gray-400';
};

// Get balance background color class
const getBalanceBgClass = (balance) => {
  if (balance > 0) return 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800';
  if (balance < 0) return 'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800';
  return 'bg-gray-50 dark:bg-gray-800 border-gray-200 dark:border-gray-700';
};

// Open payment modal
const openPaymentModal = (consignee) => {
  selectedConsignee.value = consignee;
  paymentForm.value = {
    consignee_id: consignee.consignee_id,
    trip_id: null,
    amount: '',
    currency: 'dollar',
    notes: '',
    payment_date: new Date().toISOString().split('T')[0],
  };
  showPaymentModal.value = true;
};

// Add payment
const addPayment = async () => {
  if (!paymentForm.value.amount || parseFloat(paymentForm.value.amount) <= 0) {
    toast.error('يرجى إدخال مبلغ صحيح');
    return;
  }

  try {
    const response = await axios.post('/api/consignee-balances/payments', paymentForm.value);
    if (response.data.success) {
      toast.success('تم إضافة الدفعة بنجاح');
      showPaymentModal.value = false;
      await loadBalances();
    } else {
      toast.error(response.data.message || 'حدث خطأ أثناء إضافة الدفعة');
    }
  } catch (error) {
    console.error(error);
    const errorMessage = error.response?.data?.message || 'حدث خطأ أثناء إضافة الدفعة';
    toast.error(errorMessage);
  }
};

// Delete payment
const deletePayment = async (paymentId) => {
  if (!confirm('هل أنت متأكد من حذف هذه الدفعة؟')) return;

  try {
    const response = await axios.delete(`/api/consignee-balances/payments/${paymentId}`);
    if (response.data.success) {
      toast.success('تم حذف الدفعة بنجاح');
      await loadBalances();
    } else {
      toast.error(response.data.message || 'حدث خطأ أثناء حذف الدفعة');
    }
  } catch (error) {
    console.error(error);
    toast.error('حدث خطأ أثناء حذف الدفعة');
  }
};

// View payments for consignee
const viewPayments = async (consignee) => {
  selectedConsignee.value = consignee;
  try {
    const response = await axios.get(`/api/consignee-balances/${consignee.consignee_id}/payments`);
    if (response.data.success) {
      selectedConsignee.value.payments = response.data.payments;
      showReceiptModal.value = true;
    }
  } catch (error) {
    console.error(error);
    toast.error('حدث خطأ أثناء جلب الدفعات');
  }
};

// Print receipt
const printReceipt = async (paymentId) => {
  try {
    const response = await axios.get(`/api/consignee-balances/payments/${paymentId}/receipt`);
    if (response.data.success) {
      selectedPayment.value = response.data.payment;
      // هنا يمكن إضافة كود طباعة PDF
      // في الوقت الحالي نعرض البيانات
      window.print();
    }
  } catch (error) {
    console.error(error);
    toast.error('حدث خطأ أثناء طباعة الوصل');
  }
};

// Calculate total statistics
const totalStats = computed(() => {
  const stats = {
    totalShippingCost: 0,
    totalPaid: 0,
    totalBalance: 0,
    totalCars: 0,
  };

  balances.value.forEach(balance => {
    stats.totalShippingCost += balance.total_shipping_cost || 0;
    stats.totalPaid += balance.total_paid_dollar || 0;
    stats.totalBalance += balance.balance_dollar || 0;
    stats.totalCars += balance.total_cars || 0;
  });

  return stats;
});

onMounted(() => {
  loadBalances();
});
</script>

<template>
  <Head title="أرصدة الزبائن" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
        أرصدة الزبائن (Consignees)
      </h2>
    </template>

    <div class="py-6 sm:py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Statistics Summary -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 border border-gray-200 dark:border-gray-700">
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">إجمالي المبلغ المستحق</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">
              {{ formatCurrency(totalStats.totalShippingCost) }}
            </p>
          </div>
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 border border-gray-200 dark:border-gray-700">
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">إجمالي الدفعات</p>
            <p class="text-2xl font-bold text-green-600 dark:text-green-400 mt-1">
              {{ formatCurrency(totalStats.totalPaid) }}
            </p>
          </div>
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 border border-gray-200 dark:border-gray-700">
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">إجمالي الرصيد المستحق</p>
            <p :class="['text-2xl font-bold mt-1', getBalanceColorClass(totalStats.totalBalance)]">
              {{ formatCurrency(totalStats.totalBalance) }}
            </p>
          </div>
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 border border-gray-200 dark:border-gray-700">
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">إجمالي السيارات</p>
            <p class="text-2xl font-bold text-blue-600 dark:text-blue-400 mt-1">
              {{ totalStats.totalCars }}
            </p>
          </div>
        </div>

        <!-- Loading State -->
        <div v-if="isLoading" class="text-center py-12">
          <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
          <p class="mt-2 text-gray-600 dark:text-gray-400">جاري التحميل...</p>
        </div>

        <!-- Balances List -->
        <div v-else class="space-y-4">
          <Link
            v-for="balance in balances"
            :key="balance.company_id || balance.consignee_id"
            :href="route('consigneeBalances.show', balance.company_id || balance.consignee_id)"
            class="block bg-white dark:bg-gray-800 rounded-lg shadow-md hover:shadow-lg transition-all duration-200 border-l-4"
            :class="[
              balance.balance_dollar > 0 ? 'border-red-500' : 
              balance.balance_dollar < 0 ? 'border-green-500' : 
              'border-gray-400'
            ]"
          >
            <div class="p-6">
              <!-- Header: Company Name and Balance -->
              <div class="flex items-center justify-between mb-6 pb-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex-1">
                  <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-2" style="text-decoration: underline; text-underline-offset: 4px;">
                    {{ balance.company_name || balance.consignee_name || balance.name || 'شركة غير معروفة' }}
                  </h3>
                  <p v-if="balance.company_phone || balance.consignee_phone || balance.phone" class="text-sm text-gray-500 dark:text-gray-400">
                    📞 {{ balance.company_phone || balance.consignee_phone || balance.phone }}
                  </p>
                </div>
                <div class="text-left ml-6 px-4 py-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                  <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">الرصيد المستحق</p>
                  <p :class="['text-3xl font-bold', getBalanceColorClass(balance.balance_dollar)]">
                    {{ formatCurrency(balance.balance_dollar) }}
                  </p>
                </div>
              </div>

              <!-- Statistics Grid -->
              <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
                  <p class="text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">عدد السيارات</p>
                  <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ balance.total_cars }}</p>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                  <p class="text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">المبلغ المستحق</p>
                  <p class="text-2xl font-bold text-gray-900 dark:text-white">
                    {{ formatCurrency(balance.total_shipping_cost) }}
                  </p>
                </div>
                <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4">
                  <p class="text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">إجمالي الدفعات</p>
                  <p class="text-2xl font-bold text-green-600 dark:text-green-400">
                    {{ formatCurrency(balance.total_paid_dollar) }}
                  </p>
                </div>
                <div class="bg-purple-50 dark:bg-purple-900/20 rounded-lg p-4">
                  <p class="text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">عدد الدفعات</p>
                  <p class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ balance.payments_count || 0 }}</p>
                </div>
              </div>

              <!-- Action Buttons -->
              <div class="flex gap-3" @click.stop>
                <button
                  @click.stop.prevent="openPaymentModal(balance)"
                  class="flex-1 px-5 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors shadow-sm"
                >
                  ➕ إضافة دفعة
                </button>
                <button
                  @click.stop.prevent="viewPayments(balance)"
                  class="flex-1 px-5 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
                >
                  👁️ عرض الدفعات
                </button>
              </div>
            </div>
          </Link>

          <div v-if="balances.length === 0" class="text-center py-12 text-gray-500 dark:text-gray-400">
            لا توجد أرصدة للعرض
          </div>
        </div>
      </div>
    </div>

    <!-- Payment Modal -->
    <div
      v-if="showPaymentModal"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
      @click.self="showPaymentModal = false"
    >
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full p-6">
        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">
          إضافة دفعة - {{ selectedConsignee?.consignee_name }}
        </h3>
        <form @submit.prevent="addPayment">
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                المبلغ
              </label>
              <input
                v-model.number="paymentForm.amount"
                type="number"
                step="0.01"
                min="0.01"
                required
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                placeholder="0.00"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                العملة
              </label>
              <select
                v-model="paymentForm.currency"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
              >
                <option value="dollar">دولار ($)</option>
                <option value="dinar">دينار</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                تاريخ الدفعة
              </label>
              <input
                v-model="paymentForm.payment_date"
                type="date"
                required
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                ملاحظات (اختياري)
              </label>
              <textarea
                v-model="paymentForm.notes"
                rows="3"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                placeholder="ملاحظات إضافية..."
              ></textarea>
            </div>
          </div>
          <div class="flex gap-2 mt-6">
            <button
              type="submit"
              class="flex-1 px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-md hover:bg-green-700"
            >
              حفظ
            </button>
            <button
              type="button"
              @click="showPaymentModal = false"
              class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-600"
            >
              إلغاء
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Payments/Receipt Modal -->
    <div
      v-if="showReceiptModal"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
      @click.self="showReceiptModal = false"
    >
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto p-6">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-bold text-gray-900 dark:text-white">
            دفعات {{ selectedConsignee?.consignee_name }}
          </h3>
          <button
            @click="showReceiptModal = false"
            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
          >
            ✕
          </button>
        </div>

        <div v-if="selectedConsignee?.payments && selectedConsignee.payments.length > 0" class="space-y-3">
          <div
            v-for="payment in selectedConsignee.payments"
            :key="payment.id"
            class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600"
          >
            <div class="flex items-start justify-between">
              <div class="flex-1">
                <div class="flex items-center gap-4 mb-2">
                  <span class="text-sm font-medium text-gray-900 dark:text-white">
                    رقم الوصل: {{ payment.receipt_number }}
                  </span>
                  <span class="text-sm text-gray-500 dark:text-gray-400">
                    {{ formatDate(payment.payment_date) }}
                  </span>
                </div>
                <p class="text-lg font-bold text-green-600 dark:text-green-400 mb-1">
                  {{ formatCurrency(payment.amount, payment.currency) }}
                </p>
                <p v-if="payment.notes" class="text-sm text-gray-600 dark:text-gray-400">
                  {{ payment.notes }}
                </p>
                <p v-if="payment.trip" class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                  الرحلة: {{ payment.trip.ship_name }} - {{ payment.trip.voy_no }}
                </p>
              </div>
              <div class="flex gap-2">
                <button
                  @click="printReceipt(payment.id)"
                  class="px-3 py-1 text-xs font-medium text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 rounded hover:bg-blue-100 dark:hover:bg-blue-900/30"
                >
                  طباعة
                </button>
                <button
                  @click="deletePayment(payment.id)"
                  class="px-3 py-1 text-xs font-medium text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/20 rounded hover:bg-red-100 dark:hover:bg-red-900/30"
                >
                  حذف
                </button>
              </div>
            </div>
          </div>
        </div>
        <div v-else class="text-center py-8 text-gray-500 dark:text-gray-400">
          لا توجد دفعات
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
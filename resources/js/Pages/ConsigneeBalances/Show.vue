<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/inertia-vue3';
import { useToast } from "vue-toastification";
import axios from 'axios';
import { ref, computed, onMounted } from 'vue';
import debounce from 'lodash/debounce';

const props = defineProps({
  company: Object,
  consignee: Object,
  cars: Array,
  carsByTrip: Array,
  payments: Array,
  trips: Array,
  stats: Object,
});

const toast = useToast();
const searchQuery = ref('');
const filteredCarsByTrip = ref([]);
const expandedTrips = ref(new Set());
const isLoading = ref(false);
const showPaymentModal = ref(false);
const paymentForm = ref({
  consignee_id: null,
  trip_id: null,
  amount: '',
  currency: 'dollar',
  notes: '',
  payment_date: new Date().toISOString().split('T')[0],
});

// Initialize filtered cars by trip
onMounted(() => {
  if (props.carsByTrip && props.carsByTrip.length > 0) {
    filteredCarsByTrip.value = props.carsByTrip.map(trip => ({
      ...trip,
      cars: trip.cars || [],
    }));
    // فتح جميع الرحلات افتراضياً
    props.carsByTrip.forEach((_, index) => {
      expandedTrips.value.add(index);
    });
  } else if (props.cars && props.cars.length > 0) {
    // Fallback: تجميع السيارات حسب الرحلة يدوياً
    const grouped = {};
    props.cars.forEach(car => {
      const tripKey = car.tripCompany?.trip?.id || 'unknown';
      const tripName = car.tripCompany?.trip 
        ? `${car.tripCompany.trip.ship_name} - ${car.tripCompany.trip.voy_no}`
        : 'رحلة غير معروفة';
      const key = `${tripKey}_${tripName}`;
      
      if (!grouped[key]) {
        grouped[key] = {
          trip_id: tripKey,
          trip_name: tripName,
          cars: [],
        };
      }
      grouped[key].cars.push(car);
    });
    filteredCarsByTrip.value = Object.values(grouped);
    filteredCarsByTrip.value.forEach((_, index) => {
      expandedTrips.value.add(index);
    });
  }
});

// Calculate total filtered cars count
const totalFilteredCars = computed(() => {
  return filteredCarsByTrip.value.reduce((total, trip) => {
    return total + (trip.cars?.length || 0);
  }, 0);
});

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

// Toggle trip expansion
const toggleTrip = (index) => {
  if (expandedTrips.value.has(index)) {
    expandedTrips.value.delete(index);
  } else {
    expandedTrips.value.add(index);
  }
};

// Search cars
const searchCars = debounce(() => {
  if (!searchQuery.value.trim()) {
    if (props.carsByTrip && props.carsByTrip.length > 0) {
      filteredCarsByTrip.value = props.carsByTrip.map(trip => ({
        ...trip,
        cars: trip.cars || [],
      }));
    } else if (props.cars && props.cars.length > 0) {
      const grouped = {};
      props.cars.forEach(car => {
        const tripKey = car.tripCompany?.trip?.id || 'unknown';
        const tripName = car.tripCompany?.trip 
          ? `${car.tripCompany.trip.ship_name} - ${car.tripCompany.trip.voy_no}`
          : 'رحلة غير معروفة';
        const key = `${tripKey}_${tripName}`;
        
        if (!grouped[key]) {
          grouped[key] = {
            trip_id: tripKey,
            trip_name: tripName,
            cars: [],
          };
        }
        grouped[key].cars.push(car);
      });
      filteredCarsByTrip.value = Object.values(grouped);
    }
    return;
  }

  const query = searchQuery.value.toLowerCase().trim();
  const allCars = props.carsByTrip 
    ? props.carsByTrip.flatMap(trip => trip.cars || [])
    : (props.cars || []);
  
  const matchingCars = allCars.filter(car => {
    return (
      (car.chassis_no && car.chassis_no.toLowerCase().includes(query)) ||
      (car.description && car.description.toLowerCase().includes(query)) ||
      (car.code && car.code.toLowerCase().includes(query)) ||
      (car.consignee_name && car.consignee_name.toLowerCase().includes(query)) ||
      (car.tripCompany?.trip && car.tripCompany.trip.ship_name && car.tripCompany.trip.ship_name.toLowerCase().includes(query)) ||
      (car.tripCompany?.company && car.tripCompany.company.name && car.tripCompany.company.name.toLowerCase().includes(query))
    );
  });

  // تجميع النتائج حسب الرحلة
  const grouped = {};
  matchingCars.forEach(car => {
    const tripKey = car.tripCompany?.trip?.id || 'unknown';
    const tripName = car.tripCompany?.trip 
      ? `${car.tripCompany.trip.ship_name} - ${car.tripCompany.trip.voy_no}`
      : 'رحلة غير معروفة';
    const key = `${tripKey}_${tripName}`;
    
    if (!grouped[key]) {
      grouped[key] = {
        trip_id: tripKey,
        trip_name: tripName,
        cars: [],
      };
    }
    grouped[key].cars.push(car);
  });
  
  filteredCarsByTrip.value = Object.values(grouped);
  
  // فتح الرحلات التي تحتوي على نتائج
  filteredCarsByTrip.value.forEach((_, index) => {
    expandedTrips.value.add(index);
  });
}, 300);

// Open payment modal
const openPaymentModal = () => {
  // استخدام أول زبون من السيارات إذا كان company موجوداً
  const firstConsigneeId = props.cars && props.cars.length > 0 ? props.cars[0].consignee_id : (props.consignee?.id || null);
  paymentForm.value = {
    consignee_id: firstConsigneeId,
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
      // إعادة تحميل الصفحة
      window.location.reload();
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
      window.location.reload();
    } else {
      toast.error(response.data.message || 'حدث خطأ أثناء حذف الدفعة');
    }
  } catch (error) {
    console.error(error);
    toast.error('حدث خطأ أثناء حذف الدفعة');
  }
};

// Print receipt
const printReceipt = async (paymentId) => {
  try {
    const response = await axios.get(`/api/consignee-balances/payments/${paymentId}/receipt`);
    if (response.data.success) {
      // هنا يمكن إضافة كود طباعة PDF
      window.print();
    }
  } catch (error) {
    console.error(error);
    toast.error('حدث خطأ أثناء طباعة الوصل');
  }
};

// Get balance color class
const getBalanceColorClass = (balance) => {
  if (balance > 0) return 'text-red-600 dark:text-red-400';
  if (balance < 0) return 'text-green-600 dark:text-green-400';
  return 'text-gray-600 dark:text-gray-400';
};
</script>

<template>
  <Head :title="`تفاصيل ${company?.name || consignee?.name || 'الشركة'}`" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center gap-4">
        <Link
          :href="route('consigneeBalances.index')"
          class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white"
        >
          ← العودة
        </Link>
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
          تفاصيل الشركة: {{ company?.name || consignee?.name || 'غير معروف' }}
        </h2>
      </div>
    </template>

    <div class="py-6 sm:py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Statistics Summary -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 border border-gray-200 dark:border-gray-700">
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">عدد السيارات</p>
            <p class="text-2xl font-bold text-blue-600 dark:text-blue-400 mt-1">
              {{ stats?.total_cars || 0 }}
            </p>
          </div>
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 border border-gray-200 dark:border-gray-700">
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">المبلغ المستحق</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">
              {{ formatCurrency(stats?.total_shipping_cost) }}
            </p>
          </div>
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 border border-gray-200 dark:border-gray-700">
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">إجمالي الدفعات</p>
            <p class="text-2xl font-bold text-green-600 dark:text-green-400 mt-1">
              {{ formatCurrency(stats?.total_paid_dollar) }}
            </p>
          </div>
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 border border-gray-200 dark:border-gray-700">
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">الرصيد المستحق</p>
            <p :class="['text-2xl font-bold mt-1', getBalanceColorClass(stats?.balance_dollar || 0)]">
              {{ formatCurrency(stats?.balance_dollar) }}
            </p>
          </div>
        </div>

        <!-- Tabs -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow mb-6">
          <div class="border-b border-gray-200 dark:border-gray-700">
            <nav class="flex -mb-px">
              <button class="px-4 py-3 text-sm font-medium text-blue-600 dark:text-blue-400 border-b-2 border-blue-600">
                السيارات ({{ totalFilteredCars }})
              </button>
            </nav>
          </div>

          <!-- Search Bar -->
          <div class="p-4 border-b border-gray-200 dark:border-gray-700">
            <input
              v-model="searchQuery"
              @input="searchCars"
              type="text"
              placeholder="بحث في السيارات: رقم الشاسيه، الوصف، الكود..."
              class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
            />
          </div>

          <!-- Cars List Grouped by Trip -->
          <div class="p-4">
            <div v-if="filteredCarsByTrip.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
              لا توجد سيارات للعرض
            </div>
            <div v-else class="space-y-4">
              <div
                v-for="(tripGroup, tripIndex) in filteredCarsByTrip"
                :key="tripGroup.trip_id || tripIndex"
                class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden"
              >
                <!-- Trip Header -->
                <button
                  @click="toggleTrip(tripIndex)"
                  class="w-full px-4 py-3 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors flex items-center justify-between"
                >
                  <div class="flex items-center gap-3">
                    <span class="text-lg">{{ expandedTrips.has(tripIndex) ? '▼' : '▶' }}</span>
                    <h4 class="text-lg font-bold text-gray-900 dark:text-white">
                      {{ tripGroup.trip_name }}
                    </h4>
                    <span class="px-2 py-1 text-xs font-medium bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded-full">
                      {{ tripGroup.cars?.length || 0 }} سيارة
                    </span>
                  </div>
                </button>
                
                <!-- Trip Cars (Collapsible) -->
                <div v-if="expandedTrips.has(tripIndex)" class="p-4 bg-white dark:bg-gray-800 space-y-3">
                  <div
                    v-for="car in tripGroup.cars"
                    :key="car.id"
                    class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600"
                  >
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                      <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">الوصف</p>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ car.description || '-' }}</p>
                      </div>
                      <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">رقم الشاسيه</p>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ car.chassis_no || '-' }}</p>
                      </div>
                      <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">الوزن</p>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ car.weight ? car.weight + ' كغ' : '-' }}</p>
                      </div>
                      <div v-if="car.code">
                        <p class="text-xs text-gray-500 dark:text-gray-400">الكود</p>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ car.code }}</p>
                      </div>
                      <div v-if="car.consignee">
                        <p class="text-xs text-gray-500 dark:text-gray-400">الزبون</p>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ car.consignee.name || '-' }}</p>
                      </div>
                      <div v-if="car.tripCompany?.company">
                        <p class="text-xs text-gray-500 dark:text-gray-400">الشركة</p>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ car.tripCompany.company.name }}</p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Payments Section -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow mb-6">
          <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">
              الدفعات ({{ payments?.length || 0 }})
            </h3>
            <button
              @click="openPaymentModal"
              class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700"
            >
              إضافة دفعة
            </button>
          </div>

          <div class="p-4">
            <div v-if="!payments || payments.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
              لا توجد دفعات
            </div>
            <div v-else class="space-y-3">
              <div
                v-for="payment in payments"
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
          إضافة دفعة - {{ company?.name || consignee?.name }}
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
  </AuthenticatedLayout>
</template>
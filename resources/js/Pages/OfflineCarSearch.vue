<template>
  <Head title="البحث عن السيارات - Offline" />
  <AuthenticatedLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl dark:text-gray-200 text-gray-800">
          🔍 البحث عن السيارات - يعمل Offline
        </h2>
        <div class="flex gap-2">
          <button
            @click="loadDataForOffline"
            class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600"
            :disabled="isLoading"
          >
            <span v-if="!isLoading">📥 تحميل البيانات</span>
            <span v-else>⏳ جاري التحميل...</span>
          </button>
          
          <div class="px-4 py-2 rounded"
               :class="isOnline ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
            {{ isOnline ? '🌐 متصل' : '📴 Offline' }}
          </div>
        </div>
      </div>
    </template>

    <div class="py-6">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        
        <!-- مربع البحث -->
        <div class="mb-6 bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
          <h3 class="text-lg font-semibold mb-4 dark:text-gray-200">🔍 البحث</h3>
          
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- البحث بالشاصي -->
            <div>
              <label class="block text-sm font-medium mb-2 dark:text-gray-300">رقم الشاصي (VIN)</label>
              <input
                v-model="searchChassis"
                type="text"
                @keyup.enter="searchByChassis"
                placeholder="ادخل رقم الشاصي"
                class="w-full px-3 py-2 border rounded dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600"
              />
            </div>

            <!-- البحث بالكاتي -->
            <div>
              <label class="block text-sm font-medium mb-2 dark:text-gray-300">رقم الكاتي (LOT)</label>
              <input
                v-model="searchLot"
                type="text"
                @keyup.enter="searchByLot"
                placeholder="ادخل رقم الكاتي"
                class="w-full px-3 py-2 border rounded dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600"
              />
            </div>

            <!-- البحث العام -->
            <div>
              <label class="block text-sm font-medium mb-2 dark:text-gray-300">بحث عام</label>
              <input
                v-model="searchGeneral"
                type="text"
                @keyup.enter="performGeneralSearch"
                placeholder="ابحث في كل الحقول"
                class="w-full px-3 py-2 border rounded dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600"
              />
            </div>
          </div>

          <div class="mt-4 flex gap-2">
            <button
              @click="searchByChassis"
              class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600"
              :disabled="isSearching"
            >
              🔍 بحث بالشاصي
            </button>
            <button
              @click="searchByLot"
              class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600"
              :disabled="isSearching"
            >
              🔍 بحث بالكاتي
            </button>
            <button
              @click="performGeneralSearch"
              class="px-4 py-2 bg-purple-500 text-white rounded hover:bg-purple-600"
              :disabled="isSearching"
            >
              🔍 بحث عام
            </button>
            <button
              @click="clearSearch"
              class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600"
            >
              ✕ مسح
            </button>
          </div>
        </div>

        <!-- إحصائيات البحث -->
        <div v-if="searchResults.length > 0" class="mb-6 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-lg shadow-lg p-6 text-white">
          <div class="flex justify-between items-center">
            <div>
              <p class="text-3xl font-bold">{{ searchResults.length }}</p>
              <p class="opacity-80">نتيجة بحث</p>
            </div>
            <div>
              <p class="text-lg">البحث من: {{ searchSource }}</p>
            </div>
          </div>
        </div>

        <!-- نتائج البحث -->
        <div v-if="searchResults.length > 0" class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden">
          <div class="p-6 border-b dark:border-gray-700">
            <h3 class="text-lg font-semibold dark:text-gray-200">📋 نتائج البحث</h3>
          </div>

          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
              <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">الشاصي</th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">الكاتي</th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">الصنع</th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">السنة</th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">اللون</th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">العميل</th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">السعر</th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">الإجراءات</th>
                </tr>
              </thead>
              <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                <tr v-for="car in searchResults" :key="car.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                  <td class="px-6 py-4 text-sm dark:text-gray-300">{{ car.chassis }}</td>
                  <td class="px-6 py-4 text-sm dark:text-gray-300">{{ car.lot }}</td>
                  <td class="px-6 py-4 text-sm dark:text-gray-300">{{ car.make }}</td>
                  <td class="px-6 py-4 text-sm dark:text-gray-300">{{ car.year }}</td>
                  <td class="px-6 py-4 text-sm dark:text-gray-300">{{ car.color }}</td>
                  <td class="px-6 py-4 text-sm dark:text-gray-300">{{ car.client_name }}</td>
                  <td class="px-6 py-4 text-sm dark:text-gray-300">{{ formatCurrency(car.price) }}</td>
                  <td class="px-6 py-4 text-sm">
                    <div class="flex gap-2">
                      <button
                        @click="openPaymentModal(car)"
                        class="px-3 py-1 bg-green-500 text-white text-xs rounded hover:bg-green-600"
                      >
                        💰 دفعة
                      </button>
                      <button
                        @click="openWithdrawalModal(car)"
                        class="px-3 py-1 bg-red-500 text-white text-xs rounded hover:bg-red-600"
                      >
                        💸 سحب
                      </button>
                      <button
                        @click="viewCarDetails(car)"
                        class="px-3 py-1 bg-blue-500 text-white text-xs rounded hover:bg-blue-600"
                      >
                        👁️ عرض
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- رسالة عدم وجود نتائج -->
        <div v-else-if="hasSearched" class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-12 text-center">
          <div class="text-6xl mb-4">🔍</div>
          <p class="text-xl text-gray-600 dark:text-gray-400">لم يتم العثور على نتائج</p>
        </div>

      </div>
    </div>

    <!-- Modal للدفعات -->
    <Modal :show="showPaymentModal" @close="closePaymentModal">
      <div class="p-6">
        <h3 class="text-lg font-semibold mb-4 dark:text-gray-200">💰 إضافة دفعة</h3>
        
        <div v-if="selectedCar" class="mb-4 p-4 bg-gray-100 dark:bg-gray-700 rounded">
          <p class="text-sm dark:text-gray-300"><strong>السيارة:</strong> {{ selectedCar.make }} - {{ selectedCar.year }}</p>
          <p class="text-sm dark:text-gray-300"><strong>الشاصي:</strong> {{ selectedCar.chassis }}</p>
          <p class="text-sm dark:text-gray-300"><strong>العميل:</strong> {{ selectedCar.client_name }}</p>
        </div>

        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium mb-2 dark:text-gray-300">المبلغ</label>
            <input
              v-model="paymentForm.amount"
              type="number"
              step="0.01"
              class="w-full px-3 py-2 border rounded dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600"
              placeholder="0.00"
            />
          </div>

          <div>
            <label class="block text-sm font-medium mb-2 dark:text-gray-300">ملاحظات</label>
            <textarea
              v-model="paymentForm.note"
              rows="3"
              class="w-full px-3 py-2 border rounded dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600"
              placeholder="ملاحظات اختيارية..."
            ></textarea>
          </div>
        </div>

        <div class="mt-6 flex justify-end gap-2">
          <button
            @click="savePayment"
            class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600"
            :disabled="isSaving"
          >
            <span v-if="!isSaving">💾 حفظ</span>
            <span v-else>⏳ جاري الحفظ...</span>
          </button>
          <button
            @click="closePaymentModal"
            class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600"
          >
            إلغاء
          </button>
        </div>
      </div>
    </Modal>

    <!-- Modal للسحوبات -->
    <Modal :show="showWithdrawalModal" @close="closeWithdrawalModal">
      <div class="p-6">
        <h3 class="text-lg font-semibold mb-4 text-red-600">💸 إضافة سحب</h3>
        
        <div v-if="selectedCar" class="mb-4 p-4 bg-gray-100 dark:bg-gray-700 rounded">
          <p class="text-sm dark:text-gray-300"><strong>السيارة:</strong> {{ selectedCar.make }} - {{ selectedCar.year }}</p>
          <p class="text-sm dark:text-gray-300"><strong>الشاصي:</strong> {{ selectedCar.chassis }}</p>
          <p class="text-sm dark:text-gray-300"><strong>العميل:</strong> {{ selectedCar.client_name }}</p>
        </div>

        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium mb-2 dark:text-gray-300">المبلغ</label>
            <input
              v-model="withdrawalForm.amount"
              type="number"
              step="0.01"
              class="w-full px-3 py-2 border rounded dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600"
              placeholder="0.00"
            />
          </div>

          <div>
            <label class="block text-sm font-medium mb-2 dark:text-gray-300">ملاحظات</label>
            <textarea
              v-model="withdrawalForm.note"
              rows="3"
              class="w-full px-3 py-2 border rounded dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600"
              placeholder="ملاحظات اختيارية..."
            ></textarea>
          </div>
        </div>

        <div class="mt-6 flex justify-end gap-2">
          <button
            @click="saveWithdrawal"
            class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600"
            :disabled="isSaving"
          >
            <span v-if="!isSaving">💾 حفظ</span>
            <span v-else>⏳ جاري الحفظ...</span>
          </button>
          <button
            @click="closeWithdrawalModal"
            class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600"
          >
            إلغاء
          </button>
        </div>
      </div>
    </Modal>

  </AuthenticatedLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { Head } from '@inertiajs/inertia-vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import { useToast } from 'vue-toastification';

const toast = useToast();

// البيانات
const isOnline = ref(navigator.onLine);
const isLoading = ref(false);
const isSearching = ref(false);
const isSaving = ref(false);
const hasSearched = ref(false);

const searchChassis = ref('');
const searchLot = ref('');
const searchGeneral = ref('');
const searchResults = ref([]);
const searchSource = ref('');

const showPaymentModal = ref(false);
const showWithdrawalModal = ref(false);
const selectedCar = ref(null);

const paymentForm = ref({
  amount: '',
  note: ''
});

const withdrawalForm = ref({
  amount: '',
  note: ''
});

// تحميل البيانات للعمل Offline
const loadDataForOffline = async () => {
  isLoading.value = true;
  
  try {
    if (!window.$db) {
      throw new Error('قاعدة البيانات المحلية غير متاحة');
    }

    const cars = await window.$db.loadCarsForOffline(true);
    
    toast.success(`✅ تم تحميل ${cars.length} سيارة للعمل Offline`);
  } catch (error) {
    console.error('فشل تحميل البيانات:', error);
    toast.error('❌ فشل تحميل البيانات');
  } finally {
    isLoading.value = false;
  }
};

// البحث بالشاصي
const searchByChassis = async () => {
  if (!searchChassis.value.trim()) {
    toast.warning('الرجاء إدخال رقم الشاصي');
    return;
  }

  isSearching.value = true;
  hasSearched.value = true;

  try {
    if (window.$db) {
      searchResults.value = await window.$db.searchByChassis(searchChassis.value);
      searchSource.value = isOnline.value ? '🌐 السيرفر + المحلي' : '📴 البيانات المحلية';
    }

    console.log('نتائج البحث:', searchResults.value);
  } catch (error) {
    console.error('فشل البحث:', error);
    toast.error('❌ فشل البحث');
  } finally {
    isSearching.value = false;
  }
};

// البحث بالكاتي
const searchByLot = async () => {
  if (!searchLot.value.trim()) {
    toast.warning('الرجاء إدخال رقم الكاتي');
    return;
  }

  isSearching.value = true;
  hasSearched.value = true;

  try {
    if (window.$db) {
      searchResults.value = await window.$db.searchByLot(searchLot.value);
      searchSource.value = isOnline.value ? '🌐 السيرفر + المحلي' : '📴 البيانات المحلية';
    }
  } catch (error) {
    console.error('فشل البحث:', error);
    toast.error('❌ فشل البحث');
  } finally {
    isSearching.value = false;
  }
};

// البحث العام
const performGeneralSearch = async () => {
  if (!searchGeneral.value.trim()) {
    toast.warning('الرجاء إدخال كلمة البحث');
    return;
  }

  isSearching.value = true;
  hasSearched.value = true;

  try {
    if (window.$db) {
      searchResults.value = await window.$db.searchCars(searchGeneral.value);
      searchSource.value = isOnline.value ? '🌐 السيرفر + المحلي' : '📴 البيانات المحلية';
    }
  } catch (error) {
    console.error('فشل البحث:', error);
    toast.error('❌ فشل البحث');
  } finally {
    isSearching.value = false;
  }
};

// مسح البحث
const clearSearch = () => {
  searchChassis.value = '';
  searchLot.value = '';
  searchGeneral.value = '';
  searchResults.value = [];
  hasSearched.value = false;
};

// فتح modal الدفعة
const openPaymentModal = (car) => {
  selectedCar.value = car;
  paymentForm.value = { amount: '', note: '' };
  showPaymentModal.value = true;
};

// إغلاق modal الدفعة
const closePaymentModal = () => {
  showPaymentModal.value = false;
  selectedCar.value = null;
};

// حفظ الدفعة
const savePayment = async () => {
  if (!paymentForm.value.amount || parseFloat(paymentForm.value.amount) <= 0) {
    toast.warning('الرجاء إدخال مبلغ صحيح');
    return;
  }

  isSaving.value = true;

  try {
    if (window.$db) {
      await window.$db.addPayment({
        car_id: selectedCar.value.id,
        user_id: selectedCar.value.user_id,
        amount: parseFloat(paymentForm.value.amount),
        note: paymentForm.value.note
      });

      toast.success('✅ تمت إضافة الدفعة - سيتم المزامنة عند الاتصال');
      closePaymentModal();
    }
  } catch (error) {
    console.error('فشل حفظ الدفعة:', error);
    toast.error('❌ فشل حفظ الدفعة');
  } finally {
    isSaving.value = false;
  }
};

// فتح modal السحب
const openWithdrawalModal = (car) => {
  selectedCar.value = car;
  withdrawalForm.value = { amount: '', note: '' };
  showWithdrawalModal.value = true;
};

// إغلاق modal السحب
const closeWithdrawalModal = () => {
  showWithdrawalModal.value = false;
  selectedCar.value = null;
};

// حفظ السحب
const saveWithdrawal = async () => {
  if (!withdrawalForm.value.amount || parseFloat(withdrawalForm.value.amount) <= 0) {
    toast.warning('الرجاء إدخال مبلغ صحيح');
    return;
  }

  isSaving.value = true;

  try {
    if (window.$db) {
      await window.$db.addWithdrawal({
        car_id: selectedCar.value.id,
        user_id: selectedCar.value.user_id,
        amount: parseFloat(withdrawalForm.value.amount),
        note: withdrawalForm.value.note
      });

      toast.success('✅ تمت إضافة السحب - سيتم المزامنة عند الاتصال');
      closeWithdrawalModal();
    }
  } catch (error) {
    console.error('فشل حفظ السحب:', error);
    toast.error('❌ فشل حفظ السحب');
  } finally {
    isSaving.value = false;
  }
};

// عرض تفاصيل السيارة
const viewCarDetails = (car) => {
  // يمكن الانتقال لصفحة التفاصيل
  console.log('عرض تفاصيل السيارة:', car);
  toast.info('صفحة التفاصيل قيد التطوير');
};

// تنسيق العملة
const formatCurrency = (amount, currency = 'dollar') => {
  if (!amount) return '0';
  const formatted = parseFloat(amount).toLocaleString('en-US', { maximumFractionDigits: 0 });
  return formatted + ' ' + (currency === 'dollar' ? '$' : 'دينار');
};

// مراقبة حالة الاتصال
const handleOnline = () => {
  isOnline.value = true;
  toast.success('🌐 عاد الاتصال!');
};

const handleOffline = () => {
  isOnline.value = false;
  toast.warning('📴 فقدان الاتصال - وضع Offline');
};

// Lifecycle
onMounted(() => {
  window.addEventListener('online', handleOnline);
  window.addEventListener('offline', handleOffline);
});
</script>

<style scoped>
input:focus, textarea:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.5);
}
</style>


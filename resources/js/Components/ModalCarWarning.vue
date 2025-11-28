<script setup>
import { ref, computed, watch } from "vue";
import axios from 'axios';
import { useToast } from "vue-toastification";

const props = defineProps({
  show: Boolean,
  carData: Object,
});

const emit = defineEmits(['close', 'updated']);

const toast = useToast();

const isEditing = ref(false);
const isLoading = ref(false);

// Purchase expenses
const purchaseExpenses = ref({
  expenses: 0,
  checkout: 0,
  shipping_dolar: 0,
  coc_dolar: 0,
  dinar: 0,
  land_shipping: 0,
  land_shipping_dinar: 0,
  dolar_price: 1,
});

// Sales expenses
const salesExpenses = ref({
  expenses_s: 0,
  checkout_s: 0,
  shipping_dolar_s: 0,
  coc_dolar_s: 0,
  dinar_s: 0,
  land_shipping_s: 0,
  land_shipping_dinar_s: 0,
  dolar_price_s: 1,
});

const note = ref('');

// Watch for carData changes
watch(() => props.carData, (newData) => {
  if (newData && newData.car) {
    // Initialize purchase expenses
    if (newData.car.purchase_expenses) {
      Object.assign(purchaseExpenses.value, newData.car.purchase_expenses);
    }
    
    // Initialize sales expenses
    if (newData.car.sales_expenses) {
      Object.assign(salesExpenses.value, newData.car.sales_expenses);
    }
    
    // Initialize note
    note.value = newData.car.note || '';
  }
}, { immediate: true, deep: true });

// Computed profit
const calculatedProfit = computed(() => {
  const totalPurchase = calculatePurchaseTotal();
  const totalSales = calculateSalesTotal();
  return totalSales - totalPurchase;
});

function calculatePurchaseTotal() {
  const rate = purchaseExpenses.value.dolar_price > 9999 
    ? purchaseExpenses.value.dolar_price / 100 
    : (purchaseExpenses.value.dolar_price || 1);
  if (rate === 0) rate = 1;
  
  return (
    (purchaseExpenses.value.checkout || 0) +
    (purchaseExpenses.value.shipping_dolar || 0) +
    (purchaseExpenses.value.coc_dolar || 0) +
    (purchaseExpenses.value.expenses || 0) +
    (purchaseExpenses.value.land_shipping || 0) +
    (parseInt(purchaseExpenses.value.dinar || 0) / rate) +
    (parseInt(purchaseExpenses.value.land_shipping_dinar || 0) / rate)
  );
}

function calculateSalesTotal() {
  const rate = salesExpenses.value.dolar_price_s > 9999 
    ? salesExpenses.value.dolar_price_s / 100 
    : (salesExpenses.value.dolar_price_s || 1);
  if (rate === 0) rate = 1;
  
  return (
    (salesExpenses.value.checkout_s || 0) +
    (salesExpenses.value.shipping_dolar_s || 0) +
    (salesExpenses.value.coc_dolar_s || 0) +
    (salesExpenses.value.expenses_s || 0) +
    (salesExpenses.value.land_shipping_s || 0) +
    (parseInt(salesExpenses.value.dinar_s || 0) / rate) +
    (parseInt(salesExpenses.value.land_shipping_dinar_s || 0) / rate)
  );
}

async function saveExpenses() {
  if (!props.carData?.car?.id) return;
  
  isLoading.value = true;
  
  try {
    const response = await axios.post('/api/updateCarExpenses', {
      car_id: props.carData.car.id,
      purchase_expenses: purchaseExpenses.value,
      sales_expenses: salesExpenses.value,
      note: note.value,
    });
    
    toast.success("تم تحديث المصاريف وإعادة حساب الربح بنجاح", {
      timeout: 3000,
      position: "bottom-right",
      rtl: true
    });
    
    isEditing.value = false;
    emit('updated', response.data);
  } catch (error) {
    console.error(error);
    toast.error("حدث خطأ أثناء تحديث المصاريف", {
      timeout: 3000,
      position: "bottom-right",
      rtl: true
    });
  } finally {
    isLoading.value = false;
  }
}

</script>

<template>
  <Transition name="modal">
    <div v-if="show" class="modal-mask">
      <div class="modal-wrapper max-h-[90vh]">
        <div class="modal-container dark:bg-gray-900 overflow-auto max-h-[90vh]">
          <div class="modal-header">
            <slot name="header">
              <div class="flex items-center justify-between border-b dark:border-gray-700 pb-3">
                <h2 class="text-center dark:text-yellow-400 text-xl font-bold text-red-600 dark:text-yellow-400 flex items-center gap-2">
                  <span class="text-2xl">⚠️</span>
                  <span>تحذير: السيارة موجودة في النظام</span>
                  <span v-if="carData?.from_second_project" class="text-sm bg-orange-500 text-white px-3 py-1 rounded-full">
                    من المشروع الثاني
                  </span>
                </h2>
                <button
                  @click="$emit('close')"
                  class="text-gray-500 hover:text-gray-700 dark:text-gray-300 dark:hover:text-white text-2xl font-bold w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                >
                  ✕
                </button>
              </div>
            </slot>
          </div>
          
          <div class="modal-body" v-if="carData?.car">
            <!-- Message if from second project -->
            <div v-if="carData?.from_second_project" class="bg-orange-100 dark:bg-orange-900/30 dark:border dark:border-orange-700/50 p-4 rounded-lg mb-4 shadow-sm">
              <div class="flex items-center gap-2">
                <span class="text-2xl">ℹ️</span>
                <p class="text-orange-800 dark:text-orange-300 font-medium">
                  {{ carData.message || 'تم العثور على السيارة في المشروع الثاني' }}
                </p>
              </div>
            </div>
            
            <!-- Car Information -->
            <div class="bg-blue-50 dark:bg-blue-900/30 dark:border dark:border-blue-700/50 p-4 rounded-lg mb-4 shadow-sm dark:shadow-lg">
              <h3 class="text-lg font-bold dark:text-blue-300 mb-3 flex items-center gap-2">
                <span class="text-xl">🚗</span>
                <span>معلومات السيارة</span>
              </h3>
              <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                <div class="bg-white dark:bg-gray-800/50 p-2 rounded border dark:border-gray-700">
                  <span class="text-gray-600 dark:text-gray-400 text-sm block mb-1">نوع السيارة:</span>
                  <span class="font-bold dark:text-blue-300 text-base">{{ carData.car.car_type || 'غير محدد' }}</span>
                </div>
                <div class="bg-white dark:bg-gray-800/50 p-2 rounded border dark:border-gray-700">
                  <span class="text-gray-600 dark:text-gray-400 text-sm block mb-1">السنة:</span>
                  <span class="font-bold dark:text-blue-300 text-base">{{ carData.car.year || 'غير محدد' }}</span>
                </div>
                <div class="bg-white dark:bg-gray-800/50 p-2 rounded border dark:border-gray-700">
                  <span class="text-gray-600 dark:text-gray-400 text-sm block mb-1">اللون:</span>
                  <span class="font-bold dark:text-blue-300 text-base">{{ carData.car.car_color || 'غير محدد' }}</span>
                </div>
                <div class="bg-white dark:bg-gray-800/50 p-2 rounded border dark:border-gray-700">
                  <span class="text-gray-600 dark:text-gray-400 text-sm block mb-1">رقم الشانصي:</span>
                  <span class="font-bold dark:text-yellow-400 text-base font-mono">{{ carData.car.vin }}</span>
                </div>
                <div v-if="carData.car.client" class="bg-white dark:bg-gray-800/50 p-2 rounded border dark:border-gray-700">
                  <span class="text-gray-600 dark:text-gray-400 text-sm block mb-1">العميل:</span>
                  <span class="font-bold dark:text-blue-300 text-base">{{ carData.car.client.name }}</span>
                </div>
                <div class="bg-white dark:bg-gray-800/50 p-2 rounded border dark:border-gray-700">
                  <span class="text-gray-600 dark:text-gray-400 text-sm block mb-1">الحالة:</span>
                  <span class="font-bold text-base" :class="{
                    'text-green-600 dark:text-green-400': carData.car.results == 2,
                    'text-yellow-600 dark:text-yellow-400': carData.car.results == 1,
                    'text-red-600 dark:text-red-400': carData.car.results == 0
                  }">
                    {{ carData.car.results == 0 ? 'لم يتم الربط' : carData.car.results == 1 ? 'تم الربط' : 'مكتمل' }}
                  </span>
                </div>
              </div>
            </div>

            <!-- Expenses Section -->
            <div class="space-y-4">
              <!-- Purchase Expenses -->
              <div class="bg-green-50 dark:bg-green-900/30 dark:border dark:border-green-700/50 p-4 rounded-lg shadow-sm dark:shadow-lg">
                <div class="flex justify-between items-center mb-3 border-b dark:border-green-700/50 pb-2">
                  <h3 class="text-lg font-bold dark:text-green-300 flex items-center gap-2">
                    <span class="text-xl">💰</span>
                    <span>مصاريف المشتريات</span>
                  </h3>
                  <button
                    v-if="!isEditing"
                    @click="isEditing = true"
                    class="px-4 py-2 bg-blue-500 dark:bg-blue-600 text-white rounded hover:bg-blue-600 dark:hover:bg-blue-700 transition-colors shadow-sm"
                  >
                    ✏️ تعديل
                  </button>
                </div>
                
                <div v-if="!isEditing" class="grid grid-cols-2 md:grid-cols-4 gap-3">
                  <div class="bg-white dark:bg-gray-800/50 p-3 rounded border dark:border-gray-700">
                    <span class="text-gray-600 dark:text-gray-400 text-sm block mb-1">المصاريف:</span>
                    <span class="font-bold dark:text-gray-200">${{ carData.car.purchase_expenses?.expenses || 0 }}</span>
                  </div>
                  <div class="bg-white dark:bg-gray-800/50 p-3 rounded border dark:border-gray-700">
                    <span class="text-gray-600 dark:text-gray-400 text-sm block mb-1">الجمرك:</span>
                    <span class="font-bold dark:text-gray-200">${{ carData.car.purchase_expenses?.checkout || 0 }}</span>
                  </div>
                  <div class="bg-white dark:bg-gray-800/50 p-3 rounded border dark:border-gray-700">
                    <span class="text-gray-600 dark:text-gray-400 text-sm block mb-1">الشحن:</span>
                    <span class="font-bold dark:text-gray-200">${{ carData.car.purchase_expenses?.shipping_dolar || 0 }}</span>
                  </div>
                </div>

                <div v-else class="space-y-3">
                  <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    <div>
                      <label class="block text-sm dark:text-gray-300 mb-1 font-medium">المصاريف ($)</label>
                      <input
                        v-model.number="purchaseExpenses.expenses"
                        type="number"
                        step="0.01"
                        class="w-full border dark:border-gray-600 rounded px-2 py-2 dark:bg-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-blue-500 dark:focus:border-blue-400"
                      />
                    </div>
                    <div>
                      <label class="block text-sm dark:text-gray-300 mb-1">الجمرك ($)</label>
                      <input
                        v-model.number="purchaseExpenses.checkout"
                        type="number"
                        step="0.01"
                        class="w-full border dark:border-gray-600 rounded px-2 py-2 dark:bg-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-blue-500 dark:focus:border-blue-400"
                      />
                    </div>
                    <div>
                      <label class="block text-sm dark:text-gray-300 mb-1 font-medium">الشحن ($)</label>
                      <input
                        v-model.number="purchaseExpenses.shipping_dolar"
                        type="number"
                        step="0.01"
                        class="w-full border dark:border-gray-600 rounded px-2 py-2 dark:bg-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-blue-500 dark:focus:border-blue-400"
                      />
                    </div>
                    <div>
                      <label class="block text-sm dark:text-gray-300 mb-1 font-medium">COC ($)</label>
                      <input
                        v-model.number="purchaseExpenses.coc_dolar"
                        type="number"
                        step="0.01"
                        class="w-full border dark:border-gray-600 rounded px-2 py-2 dark:bg-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-blue-500 dark:focus:border-blue-400"
                      />
                    </div>
                    <div>
                      <label class="block text-sm dark:text-gray-300 mb-1 font-medium">الدينار</label>
                      <input
                        v-model.number="purchaseExpenses.dinar"
                        type="number"
                        step="0.01"
                        class="w-full border dark:border-gray-600 rounded px-2 py-2 dark:bg-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-blue-500 dark:focus:border-blue-400"
                      />
                    </div>
                    <div>
                      <label class="block text-sm dark:text-gray-300 mb-1 font-medium">سعر الصرف</label>
                      <input
                        v-model.number="purchaseExpenses.dolar_price"
                        type="number"
                        step="0.01"
                        class="w-full border dark:border-gray-600 rounded px-2 py-2 dark:bg-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-blue-500 dark:focus:border-blue-400"
                      />
                    </div>
                    <div>
                      <label class="block text-sm dark:text-gray-300 mb-1 font-medium">شحن بري ($)</label>
                      <input
                        v-model.number="purchaseExpenses.land_shipping"
                        type="number"
                        step="0.01"
                        class="w-full border dark:border-gray-600 rounded px-2 py-2 dark:bg-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-blue-500 dark:focus:border-blue-400"
                      />
                    </div>
                    <div>
                      <label class="block text-sm dark:text-gray-300 mb-1 font-medium">شحن بري (دينار)</label>
                      <input
                        v-model.number="purchaseExpenses.land_shipping_dinar"
                        type="number"
                        step="0.01"
                        class="w-full border dark:border-gray-600 rounded px-2 py-2 dark:bg-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-blue-500 dark:focus:border-blue-400"
                      />
                    </div>
                  </div>
                  <div class="bg-gray-100 dark:bg-gray-800 dark:border dark:border-gray-700 p-3 rounded shadow-sm">
                    <span class="font-bold dark:text-green-400 text-green-700 text-lg">الإجمالي المحسوب: ${{ calculatePurchaseTotal() }}</span>
                  </div>
                </div>
              </div>

              <!-- Sales Expenses -->
              <div class="bg-purple-50 dark:bg-purple-900/30 dark:border dark:border-purple-700/50 p-4 rounded-lg shadow-sm dark:shadow-lg">
                <div class="flex justify-between items-center mb-3 border-b dark:border-purple-700/50 pb-2">
                  <h3 class="text-lg font-bold dark:text-purple-300 flex items-center gap-2">
                    <span class="text-xl">💵</span>
                    <span>مصاريف المبيعات</span>
                  </h3>
                </div>
                
                <div v-if="!isEditing" class="grid grid-cols-2 md:grid-cols-4 gap-3">
                  <div class="bg-white dark:bg-gray-800/50 p-3 rounded border dark:border-gray-700">
                    <span class="text-gray-600 dark:text-gray-400 text-sm block mb-1">المصاريف:</span>
                    <span class="font-bold dark:text-gray-200">${{ carData.car.sales_expenses?.expenses_s || 0 }}</span>
                  </div>
                  <div class="bg-white dark:bg-gray-800/50 p-3 rounded border dark:border-gray-700">
                    <span class="text-gray-600 dark:text-gray-400 text-sm block mb-1">الجمرك:</span>
                    <span class="font-bold dark:text-gray-200">${{ carData.car.sales_expenses?.checkout_s || 0 }}</span>
                  </div>
                  <div class="bg-white dark:bg-gray-800/50 p-3 rounded border dark:border-gray-700">
                    <span class="text-gray-600 dark:text-gray-400 text-sm block mb-1">الشحن:</span>
                    <span class="font-bold dark:text-gray-200">${{ carData.car.sales_expenses?.shipping_dolar_s || 0 }}</span>
                  </div>
                </div>

                <div v-else class="space-y-3">
                  <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    <div>
                      <label class="block text-sm dark:text-gray-300 mb-1 font-medium">المصاريف ($)</label>
                      <input
                        v-model.number="salesExpenses.expenses_s"
                        type="number"
                        step="0.01"
                        class="w-full border dark:border-gray-600 rounded px-2 py-2 dark:bg-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-purple-500 dark:focus:ring-purple-400 focus:border-purple-500 dark:focus:border-purple-400"
                      />
                    </div>
                    <div>
                      <label class="block text-sm dark:text-gray-300 mb-1 font-medium">الجمرك ($)</label>
                      <input
                        v-model.number="salesExpenses.checkout_s"
                        type="number"
                        step="0.01"
                        class="w-full border dark:border-gray-600 rounded px-2 py-2 dark:bg-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-purple-500 dark:focus:ring-purple-400 focus:border-purple-500 dark:focus:border-purple-400"
                      />
                    </div>
                    <div>
                      <label class="block text-sm dark:text-gray-300 mb-1 font-medium">الشحن ($)</label>
                      <input
                        v-model.number="salesExpenses.shipping_dolar_s"
                        type="number"
                        step="0.01"
                        class="w-full border dark:border-gray-600 rounded px-2 py-2 dark:bg-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-purple-500 dark:focus:ring-purple-400 focus:border-purple-500 dark:focus:border-purple-400"
                      />
                    </div>
                    <div>
                      <label class="block text-sm dark:text-gray-300 mb-1 font-medium">COC ($)</label>
                      <input
                        v-model.number="salesExpenses.coc_dolar_s"
                        type="number"
                        step="0.01"
                        class="w-full border dark:border-gray-600 rounded px-2 py-2 dark:bg-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-purple-500 dark:focus:ring-purple-400 focus:border-purple-500 dark:focus:border-purple-400"
                      />
                    </div>
                    <div>
                      <label class="block text-sm dark:text-gray-300 mb-1 font-medium">الدينار</label>
                      <input
                        v-model.number="salesExpenses.dinar_s"
                        type="number"
                        step="0.01"
                        class="w-full border dark:border-gray-600 rounded px-2 py-2 dark:bg-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-purple-500 dark:focus:ring-purple-400 focus:border-purple-500 dark:focus:border-purple-400"
                      />
                    </div>
                    <div>
                      <label class="block text-sm dark:text-gray-300 mb-1 font-medium">سعر الصرف</label>
                      <input
                        v-model.number="salesExpenses.dolar_price_s"
                        type="number"
                        step="0.01"
                        class="w-full border dark:border-gray-600 rounded px-2 py-2 dark:bg-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-purple-500 dark:focus:ring-purple-400 focus:border-purple-500 dark:focus:border-purple-400"
                      />
                    </div>
                    <div>
                      <label class="block text-sm dark:text-gray-300 mb-1 font-medium">شحن بري ($)</label>
                      <input
                        v-model.number="salesExpenses.land_shipping_s"
                        type="number"
                        step="0.01"
                        class="w-full border dark:border-gray-600 rounded px-2 py-2 dark:bg-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-purple-500 dark:focus:ring-purple-400 focus:border-purple-500 dark:focus:border-purple-400"
                      />
                    </div>
                    <div>
                      <label class="block text-sm dark:text-gray-300 mb-1 font-medium">شحن بري (دينار)</label>
                      <input
                        v-model.number="salesExpenses.land_shipping_dinar_s"
                        type="number"
                        step="0.01"
                        class="w-full border dark:border-gray-600 rounded px-2 py-2 dark:bg-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-purple-500 dark:focus:ring-purple-400 focus:border-purple-500 dark:focus:border-purple-400"
                      />
                    </div>
                  </div>
                  <div class="bg-gray-100 dark:bg-gray-800 dark:border dark:border-gray-700 p-3 rounded shadow-sm">
                    <span class="font-bold dark:text-purple-400 text-purple-700 text-lg">الإجمالي المحسوب: ${{ calculateSalesTotal() }}</span>
                  </div>
                </div>
              </div>

              <!-- Summary: Total Purchases, Sales, and Profit -->
              <div class="bg-gradient-to-r from-blue-50 to-green-50 dark:from-blue-900/30 dark:to-green-900/30 dark:border dark:border-blue-700/50 p-4 rounded-lg shadow-sm dark:shadow-lg">
                <h3 class="text-lg font-bold dark:text-gray-200 mb-4 flex items-center gap-2">
                  <span class="text-xl">📊</span>
                  <span>ملخص السيارة</span>
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                  <div class="bg-white dark:bg-gray-800/50 p-4 rounded border dark:border-gray-700 text-center">
                    <span class="text-gray-600 dark:text-gray-400 text-sm block mb-2">مجموع المشتريات (نفس السيارة)</span>
                    <span class="font-bold text-2xl text-blue-600 dark:text-blue-400 block">
                      ${{ carData.car.total_purchases || carData.car.purchase_expenses?.total || 0 }}
                    </span>
                  </div>
                  <div class="bg-white dark:bg-gray-800/50 p-4 rounded border dark:border-gray-700 text-center">
                    <span class="text-gray-600 dark:text-gray-400 text-sm block mb-2">مجموع المبيعات (نفس السيارة)</span>
                    <span class="font-bold text-2xl text-purple-600 dark:text-purple-400 block">
                      ${{ carData.car.total_sales || carData.car.sales_expenses?.total_s || 0 }}
                    </span>
                  </div>
                  <div class="bg-white dark:bg-gray-800/50 p-4 rounded border dark:border-gray-700 text-center">
                    <span class="text-gray-600 dark:text-gray-400 text-sm block mb-2">الربح (نفس السيارة)</span>
                    <span class="font-bold text-2xl block" :class="(carData.car.profit || calculatedProfit) >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'">
                      ${{ carData.car.profit || calculatedProfit }}
                    </span>
                  </div>
                </div>
              </div>

              <!-- Note -->
              <div class="bg-yellow-50 dark:bg-yellow-900/30 dark:border dark:border-yellow-700/50 p-4 rounded-lg shadow-sm dark:shadow-lg">
                <div>
                  <label class="block text-sm dark:text-gray-300 mb-1 font-medium">الملاحظة</label>
                  <textarea
                    v-model="note"
                    :disabled="!isEditing"
                    rows="2"
                    class="w-full border dark:border-gray-600 rounded px-2 py-2 dark:bg-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-blue-500 dark:focus:border-blue-400 disabled:opacity-50"
                  ></textarea>
                </div>
              </div>

              <!-- Additional Expenses -->
              <div v-if="carData.car.additional_expenses?.length > 0" class="bg-gray-50 dark:bg-gray-800/50 dark:border dark:border-gray-700 p-4 rounded-lg shadow-sm dark:shadow-lg">
                <h3 class="text-lg font-bold dark:text-gray-200 mb-3 flex items-center gap-2">
                  <span class="text-xl">📋</span>
                  <span>مصاريف إضافية</span>
                </h3>
                <div class="space-y-2">
                  <div
                    v-for="expense in carData.car.additional_expenses"
                    :key="expense.id"
                    class="flex justify-between items-center p-3 bg-white dark:bg-gray-800 rounded border dark:border-gray-700 shadow-sm hover:shadow-md transition-shadow"
                  >
                    <div>
                      <span class="font-bold dark:text-gray-200 block">{{ expense.note || 'بدون ملاحظة' }}</span>
                      <span class="text-sm text-gray-600 dark:text-gray-400">{{ expense.created }}</span>
                    </div>
                    <div class="text-left">
                      <span v-if="expense.amount_dollar > 0" class="font-bold text-green-600 dark:text-green-400 block">
                        ${{ expense.amount_dollar }}
                      </span>
                      <span v-if="expense.amount_dinar > 0" class="font-bold text-blue-600 dark:text-blue-400">
                        {{ expense.amount_dinar }} دينار
                      </span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Action Buttons -->
            <div v-if="isEditing" class="flex gap-3 mt-4">
              <button
                @click="saveExpenses"
                :disabled="isLoading"
                class="flex-1 px-4 py-3 bg-green-500 dark:bg-green-600 text-white rounded-lg hover:bg-green-600 dark:hover:bg-green-700 disabled:opacity-50 shadow-md hover:shadow-lg transition-all font-medium"
              >
                {{ isLoading ? '⏳ جاري الحفظ...' : '💾 حفظ التعديلات' }}
              </button>
              <button
                @click="isEditing = false"
                class="flex-1 px-4 py-3 bg-gray-500 dark:bg-gray-600 text-white rounded-lg hover:bg-gray-600 dark:hover:bg-gray-700 shadow-md hover:shadow-lg transition-all font-medium"
              >
                ❌ إلغاء
              </button>
            </div>
          </div>

          <div class="modal-footer my-2">
            <button
              class="modal-default-button py-3 bg-rose-500 dark:bg-rose-600 rounded-lg w-full hover:bg-rose-600 dark:hover:bg-rose-700 shadow-md hover:shadow-lg transition-all font-medium"
              @click="$emit('close')"
            >
              ✕ إغلاق
            </button>
          </div>
        </div>
      </div>
    </div>
  </Transition>
</template>

<style scoped>
.modal-mask {
  position: fixed;
  z-index: 9998;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  display: table;
  transition: opacity 0.3s ease;
}

.modal-wrapper {
  display: table-cell;
  vertical-align: middle;
}

.modal-container {
  width: 90%;
  max-width: 1000px;
  min-width: 350px;
  margin: 0px auto;
  padding: 20px 30px;
  padding-bottom: 60px;
  background-color: #fff;
  border-radius: 2px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.33);
  transition: all 0.3s ease;
  border-radius: 10px;
}

.dark .modal-container {
  background-color: #1f2937;
  border: 1px solid #374151;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5);
}

.modal-header h3 {
  margin-top: 0;
  color: #42b983;
}

.modal-body {
  margin: 20px 0;
}

.modal-default-button {
  float: right;
  color: #fff;
}

.modal-enter-from {
  opacity: 0;
}

.modal-leave-to {
  opacity: 0;
}

.modal-enter-from .modal-container,
.modal-leave-to .modal-container {
  -webkit-transform: scale(1.1);
  transform: scale(1.1);
}
</style>


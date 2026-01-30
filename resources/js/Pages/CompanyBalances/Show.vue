<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/inertia-vue3';
import { useToast } from "vue-toastification";
import axios from 'axios';
import { ref, computed } from 'vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
  company: Object,
  shipments: Array,
  stats: Object,
});

const toast = useToast();
const editingClearance = ref(null);
const tempClearance = ref(0);
const showPaymentModal = ref(false);
const currentShipmentId = ref(null);
const editingPaymentId = ref(null);

// Payment form
const paymentForm = ref({
  amount: '',
  driver_name: '',
  cmr_number: '',
  payment_date: new Date().toISOString().split('T')[0],
  notes: '',
});

// Format currency
const formatCurrency = (amount) => {
  if (!amount) return '0';
  return parseFloat(amount).toLocaleString('en-US', { maximumFractionDigits: 2 });
};

// Format date
const formatDate = (date) => {
  if (!date) return '';
  return new Date(date).toLocaleDateString('ar-EG');
};

// Start editing clearance
const startEditClearance = (shipment) => {
  editingClearance.value = shipment.id;
  tempClearance.value = shipment.clearance_per_car || 40;
};

// Save clearance
const saveClearance = async (shipmentId) => {
  try {
    const response = await axios.put(`/api/company-shipments/${shipmentId}/fees`, {
      clearance_per_car: tempClearance.value,
    });

    if (response.data.success) {
      toast.success('تم تحديث التخليص بنجاح');
      editingClearance.value = null;
      window.location.reload();
    }
  } catch (error) {
    console.error(error);
    toast.error('حدث خطأ أثناء التحديث');
  }
};

// Cancel edit clearance
const cancelEditClearance = () => {
  editingClearance.value = null;
};

// Open payment modal
const openPaymentModal = (shipmentId, payment = null) => {
  currentShipmentId.value = shipmentId;
  
  if (payment) {
    editingPaymentId.value = payment.id;
    paymentForm.value = {
      amount: payment.amount,
      driver_name: payment.driver_name,
      cmr_number: payment.cmr_number || '',
      payment_date: payment.payment_date,
      notes: payment.notes || '',
    };
  } else {
    editingPaymentId.value = null;
    paymentForm.value = {
      amount: '',
      driver_name: '',
      cmr_number: '',
      payment_date: new Date().toISOString().split('T')[0],
      notes: '',
    };
  }
  
  showPaymentModal.value = true;
};

// Close payment modal
const closePaymentModal = () => {
  showPaymentModal.value = false;
  currentShipmentId.value = null;
  editingPaymentId.value = null;
};

// Save payment
const savePayment = async () => {
  try {
    let response;
    
    if (editingPaymentId.value) {
      // Update existing payment
      response = await axios.put(`/api/transport-payments/${editingPaymentId.value}`, paymentForm.value);
    } else {
      // Add new payment
      response = await axios.post(`/api/company-shipments/${currentShipmentId.value}/transport-payments`, paymentForm.value);
    }

    if (response.data.success) {
      toast.success(response.data.message);
      closePaymentModal();
      window.location.reload();
    }
  } catch (error) {
    console.error(error);
    toast.error('حدث خطأ أثناء الحفظ');
  }
};

// Delete payment
const deletePayment = async (paymentId) => {
  if (!confirm('هل أنت متأكد من حذف هذه الدفعة؟')) return;
  
  try {
    const response = await axios.delete(`/api/transport-payments/${paymentId}`);
    
    if (response.data.success) {
      toast.success('تم حذف الدفعة بنجاح');
      window.location.reload();
    }
  } catch (error) {
    console.error(error);
    toast.error('حدث خطأ أثناء الحذف');
  }
};
</script>

<template>
  <Head :title="`${company.name} - الحساب`" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <div>
          <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ company.name }}
          </h2>
          <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
            {{ company.phone || 'لا يوجد رقم' }}
          </p>
        </div>
        <Link
          href="/company-balances"
          class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700"
        >
          العودة للقائمة
        </Link>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
          <div class="bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20 rounded-lg p-6 border-2 border-blue-200 dark:border-blue-700">
            <div class="text-sm text-blue-700 dark:text-blue-300 mb-1">عدد الشحنات</div>
            <div class="text-3xl font-bold text-blue-900 dark:text-blue-100">{{ stats.total_shipments }}</div>
          </div>
          
          <div class="bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/20 rounded-lg p-6 border-2 border-green-200 dark:border-green-700">
            <div class="text-sm text-green-700 dark:text-green-300 mb-1">عدد السيارات</div>
            <div class="text-3xl font-bold text-green-900 dark:text-green-100">{{ stats.total_cars }}</div>
          </div>
          
          <div class="bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-800/20 rounded-lg p-6 border-2 border-purple-200 dark:border-purple-700">
            <div class="text-sm text-purple-700 dark:text-purple-300 mb-1">إجمالي التخليص</div>
            <div class="text-2xl font-bold text-purple-900 dark:text-purple-100">{{ formatCurrency(stats.total_clearance) }} $</div>
          </div>
          
          <div class="bg-gradient-to-br from-orange-50 to-orange-100 dark:from-orange-900/20 dark:to-orange-800/20 rounded-lg p-6 border-2 border-orange-200 dark:border-orange-700">
            <div class="text-sm text-orange-700 dark:text-orange-300 mb-1">إجمالي النقل</div>
            <div class="text-2xl font-bold text-orange-900 dark:text-orange-100">{{ formatCurrency(stats.total_transport) }} $</div>
          </div>
        </div>

        <!-- Grand Total -->
        <div class="bg-gradient-to-r from-indigo-500 to-purple-600 rounded-lg p-8 mb-6 text-white shadow-lg">
          <div class="text-center">
            <div class="text-lg mb-2">الإجمالي الكلي</div>
            <div class="text-5xl font-bold">{{ formatCurrency(stats.grand_total) }} $</div>
            <div class="text-sm mt-2 opacity-90">التخليص + مجموع النقل</div>
          </div>
        </div>

        <!-- Shipments List -->
        <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg overflow-hidden">
          <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
              الشحنات ({{ shipments.length }})
            </h3>

            <div class="space-y-6">
              <div
                v-for="shipment in shipments"
                :key="shipment.id"
                class="border border-gray-200 dark:border-gray-700 rounded-lg p-6 hover:shadow-md transition"
              >
                <!-- Shipment Header -->
                <div class="flex justify-between items-start mb-4">
                  <div>
                    <h4 class="text-lg font-bold text-gray-900 dark:text-white">
                      {{ shipment.trip.ship_name }} - {{ shipment.trip.voy_no }}
                    </h4>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                      {{ formatDate(shipment.trip.sailing_date) }}
                    </p>
                  </div>
                  <div class="text-right">
                    <div class="text-sm text-gray-600 dark:text-gray-400">عدد السيارات</div>
                    <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                      {{ shipment.cars_count }}
                    </div>
                  </div>
                </div>

                <!-- Clearance Section -->
                <div class="bg-purple-50 dark:bg-purple-900/20 rounded-lg p-4 mb-4">
                  <div class="flex justify-between items-center mb-2">
                    <h5 class="font-semibold text-gray-900 dark:text-white">التخليص</h5>
                    <button
                      v-if="editingClearance !== shipment.id"
                      @click="startEditClearance(shipment)"
                      class="text-blue-600 dark:text-blue-400 hover:underline text-sm"
                    >
                      تعديل
                    </button>
                  </div>

                  <!-- Edit Mode -->
                  <div v-if="editingClearance === shipment.id" class="space-y-3">
                    <div>
                      <InputLabel for="clearance" value="التخليص لكل سيارة (دولار)" />
                      <TextInput
                        v-model.number="tempClearance"
                        type="number"
                        step="0.01"
                        class="mt-1 block w-full"
                      />
                    </div>
                    <div class="flex gap-2">
                      <button
                        @click="saveClearance(shipment.id)"
                        class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700"
                      >
                        حفظ
                      </button>
                      <button
                        @click="cancelEditClearance"
                        class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600"
                      >
                        إلغاء
                      </button>
                    </div>
                  </div>

                  <!-- View Mode -->
                  <div v-else class="grid grid-cols-2 gap-4">
                    <div>
                      <div class="text-xs text-gray-600 dark:text-gray-400">التخليص/سيارة</div>
                      <div class="text-lg font-bold text-gray-900 dark:text-white">
                        {{ formatCurrency(shipment.clearance_per_car) }} $
                      </div>
                    </div>
                    <div>
                      <div class="text-xs text-gray-600 dark:text-gray-400">الإجمالي</div>
                      <div class="text-lg font-bold text-purple-600 dark:text-purple-400">
                        {{ formatCurrency(shipment.total_clearance) }} $
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Transport Payments Section -->
                <div class="bg-orange-50 dark:bg-orange-900/20 rounded-lg p-4">
                  <div class="flex justify-between items-center mb-3">
                    <h5 class="font-semibold text-gray-900 dark:text-white">دفعات النقل الداخلي</h5>
                    <button
                      @click="openPaymentModal(shipment.id)"
                      class="px-3 py-1 bg-orange-600 text-white rounded-md hover:bg-orange-700 text-sm"
                    >
                      + إضافة دفعة
                    </button>
                  </div>

                  <!-- Payments List -->
                  <div v-if="shipment.transport_payments && shipment.transport_payments.length > 0" class="space-y-2 mb-3">
                    <div
                      v-for="payment in shipment.transport_payments"
                      :key="payment.id"
                      class="bg-white dark:bg-gray-800 rounded p-3 flex justify-between items-center"
                    >
                      <div class="flex-1">
                        <div class="font-semibold text-gray-900 dark:text-white">{{ payment.driver_name }}</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">
                          CMR: {{ payment.cmr_number || '-' }} | {{ formatDate(payment.payment_date) }}
                        </div>
                        <div v-if="payment.notes" class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                          {{ payment.notes }}
                        </div>
                      </div>
                      <div class="text-right mr-4">
                        <div class="text-lg font-bold text-orange-600 dark:text-orange-400">
                          {{ formatCurrency(payment.amount) }} $
                        </div>
                        <div class="flex gap-1 mt-1">
                          <button
                            @click="openPaymentModal(shipment.id, payment)"
                            class="text-xs text-blue-600 dark:text-blue-400 hover:underline"
                          >
                            تعديل
                          </button>
                          <span class="text-gray-400">|</span>
                          <button
                            @click="deletePayment(payment.id)"
                            class="text-xs text-red-600 dark:text-red-400 hover:underline"
                          >
                            حذف
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div v-else class="text-center text-gray-500 dark:text-gray-400 text-sm py-3">
                    لا توجد دفعات بعد
                  </div>

                  <!-- Summary -->
                  <div class="grid grid-cols-3 gap-4 pt-3 border-t border-orange-200 dark:border-orange-700">
                    <div>
                      <div class="text-xs text-gray-600 dark:text-gray-400">المجموع الكلي</div>
                      <div class="text-lg font-bold text-orange-900 dark:text-orange-100">
                        {{ formatCurrency(shipment.internal_transport_total) }} $
                      </div>
                    </div>
                    <div>
                      <div class="text-xs text-gray-600 dark:text-gray-400">لكل سيارة</div>
                      <div class="text-lg font-bold text-orange-900 dark:text-orange-100">
                        {{ formatCurrency(shipment.internal_transport_per_car) }} $
                      </div>
                    </div>
                    <div>
                      <div class="text-xs text-gray-600 dark:text-gray-400">عدد الدفعات</div>
                      <div class="text-lg font-bold text-orange-900 dark:text-orange-100">
                        {{ shipment.transport_payments ? shipment.transport_payments.length : 0 }}
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Total Amount -->
                <div class="mt-4 bg-gradient-to-r from-green-500 to-green-600 rounded-lg p-4 text-white">
                  <div class="flex justify-between items-center">
                    <span class="font-bold">الإجمالي (التخليص + النقل):</span>
                    <span class="text-2xl font-bold">{{ formatCurrency(shipment.total_amount) }} $</span>
                  </div>
                </div>

                <!-- Cars List (collapsed) -->
                <details class="mt-4">
                  <summary class="cursor-pointer text-blue-600 dark:text-blue-400 hover:underline">
                    عرض السيارات ({{ shipment.cars_count }})
                  </summary>
                  <div class="mt-3 space-y-2">
                    <div
                      v-for="car in shipment.cars"
                      :key="car.id"
                      class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded p-3 text-sm"
                    >
                      <div class="grid grid-cols-3 gap-2">
                        <div>
                          <span class="text-gray-600 dark:text-gray-400">المرسل إليه:</span>
                          <span class="font-semibold">{{ car.consignee_name }}</span>
                        </div>
                        <div>
                          <span class="text-gray-600 dark:text-gray-400">الشاصي:</span>
                          <span class="font-semibold">{{ car.chassis_no || '-' }}</span>
                        </div>
                        <div>
                          <span class="text-gray-600 dark:text-gray-400">الوصف:</span>
                          <span>{{ car.description || '-' }}</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </details>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Payment Modal -->
    <div
      v-if="showPaymentModal"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
      @click.self="closePaymentModal"
    >
      <div class="bg-white dark:bg-gray-800 rounded-lg p-6 max-w-md w-full mx-4">
        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">
          {{ editingPaymentId ? 'تعديل دفعة النقل' : 'إضافة دفعة نقل جديدة' }}
        </h3>

        <div class="space-y-4">
          <div>
            <InputLabel for="amount" value="المبلغ (دولار) *" />
            <TextInput
              id="amount"
              v-model.number="paymentForm.amount"
              type="number"
              step="0.01"
              class="mt-1 block w-full"
              required
            />
          </div>

          <div>
            <InputLabel for="driver_name" value="اسم السائق *" />
            <TextInput
              id="driver_name"
              v-model="paymentForm.driver_name"
              type="text"
              class="mt-1 block w-full"
              required
            />
          </div>

          <div>
            <InputLabel for="cmr_number" value="رقم CMR" />
            <TextInput
              id="cmr_number"
              v-model="paymentForm.cmr_number"
              type="text"
              class="mt-1 block w-full"
            />
          </div>

          <div>
            <InputLabel for="payment_date" value="تاريخ الدفعة *" />
            <TextInput
              id="payment_date"
              v-model="paymentForm.payment_date"
              type="date"
              class="mt-1 block w-full"
              required
            />
          </div>

          <div>
            <InputLabel for="notes" value="ملاحظات" />
            <textarea
              id="notes"
              v-model="paymentForm.notes"
              class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
              rows="2"
            ></textarea>
          </div>

          <div class="flex gap-2 pt-4">
            <button
              @click="savePayment"
              class="flex-1 px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700"
            >
              {{ editingPaymentId ? 'تحديث' : 'إضافة' }}
            </button>
            <button
              @click="closePaymentModal"
              class="flex-1 px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600"
            >
              إلغاء
            </button>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

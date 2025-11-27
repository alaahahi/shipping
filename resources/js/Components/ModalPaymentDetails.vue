<script setup>
import Modal from "@/Components/Modal.vue";
import { ref, watch, defineProps, defineEmits } from 'vue';
import axios from "axios";
import { useToast } from "vue-toastification";
import trash from "@/Components/icon/trash.vue";

const toast = useToast();

const props = defineProps({
  show: Boolean,
  buyer: Object,
  merchantId: [String, Number],
});

const emit = defineEmits(['close', 'refresh']);

const payments = ref([]);
const isLoadingPaymentDetails = ref(false);

// Reset data when modal opens/closes
watch(() => props.show, (newVal) => {
  if (newVal && props.buyer && props.merchantId) {
    loadPaymentDetails();
  } else {
    payments.value = [];
  }
});

function formatNumber(value) {
  if (value === null || value === undefined) return '0';
  const num = typeof value === 'number' ? value : parseFloat(value) || 0;
  const formatted = num.toFixed(2);
  return formatted.replace(/\.00$/, '');
}

function formatDate(dateString) {
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

async function loadPaymentDetails() {
  if (!props.buyer || !props.merchantId) return;
  
  isLoadingPaymentDetails.value = true;
  try {
    const response = await axios.get('/api/getBuyerPaymentDetails', {
      params: {
        buyer_id: props.buyer.id,
        merchant_id: props.merchantId
      }
    });
    // استخدام payments من الاستجابة
    payments.value = response.data.payments || [];
  } catch (error) {
    console.error('Error loading payment details:', error);
    payments.value = [];
    toast.error('فشل تحميل تفاصيل الدفع', {
      timeout: 3000,
      position: "bottom-right",
      rtl: true,
    });
  } finally {
    isLoadingPaymentDetails.value = false;
  }
}

async function deletePayment(payment) {
  if (!confirm(`هل أنت متأكد من حذف هذه الدفعة بقيمة ${formatNumber(payment.amount)} $؟`)) {
    return;
  }

  try {
    await axios.post('/api/deletePayment', {
      sale_id: payment.sale_id,
      payment_id: payment.id
    });
    
    toast.success('تم حذف الدفعة بنجاح', {
      timeout: 2000,
      position: "bottom-right",
      rtl: true,
    });
    
    // إعادة تحميل البيانات
    await loadPaymentDetails();
    // إرسال حدث للتحديث في الصفحة الرئيسية
    emit('refresh');
  } catch (error) {
    console.error('Error deleting payment:', error);
    toast.error(error.response?.data?.error || 'فشل حذف الدفعة', {
      timeout: 3000,
      position: "bottom-right",
      rtl: true,
    });
  }
}

function handleClose() {
  emit('close');
}
</script>

<template>
  <Modal :show="show" @close="handleClose">
    <template #header>
      <h2 class="text-xl font-bold dark:text-gray-100">تفاصيل الدفع - {{ buyer?.name }}</h2>
    </template>
    
    <template #body>
      <div v-if="isLoadingPaymentDetails" class="text-center py-4">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
      </div>
      
      <div v-else-if="payments.length === 0" class="text-center py-4 text-gray-500 dark:text-gray-400">
        لا توجد دفعات مسجلة لهذا الزبون
      </div>
      
      <div v-else class="overflow-x-auto">
        <table class="w-full text-sm text-right text-gray-500 dark:text-gray-200 dark:text-gray-400 text-center">
          <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400 text-center">
            <tr>
              <th class="px-2 py-3">#</th>
              <th class="px-2 py-3">السيارة</th>
              <th class="px-2 py-3">الشانصي</th>
              <th class="px-2 py-3">مبلغ الدفعة</th>
              <th class="px-2 py-3">تاريخ الدفعة</th>
              <th class="px-2 py-3">ملاحظة</th>
              <th class="px-2 py-3">إجراء</th>
            </tr>
          </thead>
          <tbody>
            <tr 
              v-for="(payment, index) in payments" 
              :key="payment.id"
              class="bg-white border-b dark:bg-gray-900 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600"
            >
              <td class="px-2 py-3">{{ index + 1 }}</td>
              <td class="px-2 py-3">{{ payment.car?.car_type }} {{ payment.car?.year }}</td>
              <td class="px-2 py-3 font-mono text-xs">{{ payment.car?.vin || payment.car?.car_number || '-' }}</td>
              <td class="px-2 py-3 text-green-600 font-bold">{{ formatNumber(payment.amount) }} $</td>
              <td class="px-2 py-3">{{ formatDate(payment.date) }}</td>
              <td class="px-2 py-3 text-sm">{{ payment.note || '-' }}</td>
              <td class="px-2 py-3">
                <button
                  @click="deletePayment(payment)"
                  class="px-2 py-1 text-white bg-red-500 rounded hover:bg-red-600"
                  title="حذف الدفعة"
                >
                  <trash class="w-4 h-4 inline" />
                </button>
              </td>
            </tr>
          </tbody>
          <tfoot v-if="payments.length > 0" class="bg-gray-100 dark:bg-gray-800 font-bold">
            <tr>
              <td colspan="3" class="px-2 py-3 text-left">إجمالي الدفعات:</td>
              <td class="px-2 py-3 text-green-600">
                {{ formatNumber(payments.reduce((sum, payment) => sum + (parseFloat(payment.amount) || 0), 0)) }} $
              </td>
              <td colspan="3"></td>
            </tr>
          </tfoot>
        </table>
      </div>
    </template>
    
    <template #footer>
      <div class="flex justify-end">
        <button
          @click="handleClose"
          class="px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-200 dark:bg-gray-700 rounded hover:bg-gray-300 dark:hover:bg-gray-600"
        >
          إغلاق
        </button>
      </div>
    </template>
  </Modal>
</template>


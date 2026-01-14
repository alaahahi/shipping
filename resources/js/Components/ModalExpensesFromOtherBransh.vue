<script setup>
import { ref, computed, watch } from 'vue';
import print from "@/Components/icon/print.vue";
import axios from 'axios';
import { useToast } from "vue-toastification";

const toast = useToast();

const activeTab = ref('add'); // Set the default active tab

const setActiveTab = (tab) => {
  activeTab.value = tab;
};
const props = defineProps({
  show: Boolean,
  user:Array,
  allTransfers:Array,
  formData:Object
});

const emit = defineEmits(['close', 'refresh']);

// إنشاء reactive state محلي للتحويلات
const localTransfers = ref([...props.allTransfers]);

// تحديث localTransfers عند تغيير props
watch(() => props.allTransfers, (newTransfers) => {
  localTransfers.value = [...newTransfers];
}, { deep: true, immediate: true });

// التحقق من التحويلات المعلقة عند فتح النافذة
watch(() => props.show, async (newVal) => {
  if (newVal) {
    try {
      const apiUrl = '/api/check-pending-external-transfers';
      
      console.log('🔍 [DEBUG] ModalExpensesFromOtherBransh - Checking pending transfers', {
        apiUrl: apiUrl,
        timestamp: new Date().toISOString()
      });
      
      const response = await axios.post(apiUrl);
      
      console.log('✅ [DEBUG] Response received', {
        status: response.status,
        data: response.data,
        total_received: response.data?.total_received,
        errors: response.data?.errors,
        success: response.data?.success
      });
      
      if (response.data.success && response.data.total_received > 0) {
        toast.success(`تم استقبال ${response.data.total_received} تحويل معلق`, {
          timeout: 3000,
          position: "bottom-right",
          rtl: true,
        });
        emit('refresh');
      } else {
        console.log('ℹ️ [DEBUG] No transfers received', {
          total_received: response.data?.total_received,
          errors: response.data?.errors
        });
      }
    } catch (error) {
      console.error('❌ [DEBUG] Error checking pending transfers:', {
        message: error.message,
        response: error.response?.data,
        status: error.response?.status
      });
      // لا نعرض خطأ للمستخدم، فقط نستمر
    }
  }
});

// تصفية التحويلات حسب الحالة
const activeTransfers = computed(() => {
  return localTransfers.value.filter(t => !t.is_archived);
});

const archivedTransfers = computed(() => {
  return localTransfers.value.filter(t => t.is_archived);
});

function confirmTransfers (tran){
  axios.post(`/api/confirmTransfers`,tran)
  .then(response => {
    // تحديث الحالة المحلية مباشرة
    const index = localTransfers.value.findIndex(t => t.id === tran.id);
    if (index !== -1) {
      // تحديث كامل للتحويل بالبيانات الجديدة من السيرفر
      localTransfers.value[index] = response.data;
    }
  })
  .catch(error => {
    console.error('Error confirming transfer:', error);
    alert('حدث خطأ أثناء تأكيد التحويل');
  })
}
function cancelTransfers(tran){
  axios.post(`/api/cancelTransfers`,tran)
  .then(response => {
    // إزالة التحويل من القائمة المحلية
    localTransfers.value = localTransfers.value.filter(t => t.id !== tran.id);
  })
  .catch(error => {
    console.error('Error canceling transfer:', error);
  })
}

async function archiveTransfer(transfer) {
  try {
    const response = await axios.post('/api/archiveTransfer', { id: transfer.id });
    // تحديث الحالة المحلية مباشرة - استخدام طريقة reactive
    const index = localTransfers.value.findIndex(t => t.id === transfer.id);
    if (index !== -1) {
      // تحديث التحويل بالبيانات المحدثة من السيرفر بشكل reactive
      const updatedTransfer = response.data.transfer || { ...localTransfers.value[index], is_archived: true };
      localTransfers.value.splice(index, 1, updatedTransfer);
      toast.success('تم أرشفة التحويل بنجاح', {
        timeout: 2000,
        position: "bottom-right",
        rtl: true,
      });
    }
  } catch (error) {
    console.error('Error archiving transfer:', error);
    toast.error('حدث خطأ أثناء أرشفة التحويل', {
      timeout: 3000,
      position: "bottom-right",
      rtl: true,
    });
  }
}

async function unarchiveTransfer(transfer) {
  try {
    const response = await axios.post('/api/unarchiveTransfer', { id: transfer.id });
    // تحديث الحالة المحلية مباشرة - استخدام طريقة reactive
    const index = localTransfers.value.findIndex(t => t.id === transfer.id);
    if (index !== -1) {
      // تحديث التحويل بالبيانات المحدثة من السيرفر بشكل reactive
      const updatedTransfer = response.data.transfer || { ...localTransfers.value[index], is_archived: false };
      localTransfers.value.splice(index, 1, updatedTransfer);
      toast.success('تم إعادة التحويل للسجل بنجاح', {
        timeout: 2000,
        position: "bottom-right",
        rtl: true,
      });
    }
  } catch (error) {
    console.error('Error unarchiving transfer:', error);
    toast.error('حدث خطأ أثناء إعادة التحويل للسجل', {
      timeout: 3000,
      position: "bottom-right",
      rtl: true,
    });
  }
}

function printTransfers(type) {
  // طباعة التحويلات حسب النوع
  const transfers = type === 'archive' ? archivedTransfers.value : activeTransfers.value;
  if (transfers.length === 0) {
    toast.warning('لا توجد تحويلات للطباعة', {
      timeout: 2000,
      position: "bottom-right",
      rtl: true,
    });
    return;
  }
  
  // إنشاء نافذة طباعة
  const printWindow = window.open('', '_blank');
  const printContent = `
    <html>
      <head>
        <title>${type === 'archive' ? 'أرشيف الحولات' : 'سجل الحولات'}</title>
        <style>
          body { font-family: Arial, sans-serif; direction: rtl; }
          table { width: 100%; border-collapse: collapse; margin-top: 20px; }
          th, td { border: 1px solid #ddd; padding: 8px; text-align: center; }
          th { background-color: #f2f2f2; }
          h1 { text-align: center; }
        </style>
      </head>
      <body>
        <h1>${type === 'archive' ? 'أرشيف الحولات' : 'سجل الحولات'}</h1>
        <table>
          <thead>
            <tr>
              <th>رقم الوصل</th>
              <th>التاريخ</th>
              <th>المبلغ بالدولار</th>
              <th>أجور الحولات</th>
              <th>المبلغ الصافي</th>
              <th>ملاحظة</th>
              <th>الحالة</th>
            </tr>
          </thead>
          <tbody>
            ${transfers.map(t => `
              <tr>
                <td>${t.id}</td>
                <td>${t.created_at ? t.created_at.slice(0, 19).replace('T', ' ') : ''}</td>
                <td>${t.amount || 0}</td>
                <td>${t.fee || 0}</td>
                <td>${(t.amount || 0) - (t.fee || 0)}</td>
                <td>${t.note || ''}</td>
                <td>${t.stauts || ''}</td>
              </tr>
            `).join('')}
          </tbody>
        </table>
      </body>
    </html>
  `;
  printWindow.document.write(printContent);
  printWindow.document.close();
  printWindow.print();
}
</script>
  <template>
    <Transition name="modal">
      <div v-if="show" class="modal-mask ">
        <div class="modal-wrapper  max-h-[80vh]">
          <div class="modal-container dark:bg-gray-900 overflow-auto  max-h-[80vh]">
            <div class="modal-header"><slot name="header"></slot>
            </div>
            <div class="modal-body">
              
              <div class="text-sm font-medium text-center text-gray-500 border-b border-gray-200 dark:text-gray-400 dark:border-gray-700">
                  <ul class="flex flex-wrap -mb-px">
                    <li class="mr-2" @click="setActiveTab('add')">
                      <button
                        class="inline-block p-4 border-b-2 border-transparent rounded-t-lg"
                        :class="activeTab == 'add'?'dark:text-blue-500 dark:border-blue-500' :'hover:text-gray-600 hover:border-gray-300'"
                      >
                        طلبات قيد التحويل
                      </button>
                    </li>
                    <li class="mr-2" @click="setActiveTab('record')">
                      <button
                        class="inline-block p-4 border-b-2 border-transparent rounded-t-lg "
                        :class="activeTab == 'record'? 'dark:text-blue-500 dark:border-blue-500' :'hover:text-gray-600 hover:border-gray-300'"
                      >
                        السجل
                      </button>
                    </li>
                    <li class="mr-2" @click="setActiveTab('archive')">
                      <button
                        class="inline-block p-4 border-b-2 border-transparent rounded-t-lg "
                        :class="activeTab == 'archive'? 'dark:text-blue-500 dark:border-blue-500' :'hover:text-gray-600 hover:border-gray-300'"
                      >
                        الأرشيف
                      </button>
                    </li>
                  </ul>
                </div>
                <div v-if="activeTab =='add'"> 
                  <h2 class="text-center py-3">طلبات قيد التحويل</h2>
                  <template  v-for="expense in localTransfers" :key="expense.id">
                  <div  v-if="expense.stauts !='تم الأستلام'" id="alert-additional-content-4 my-3" class="p-4 mb-4 text-yellow-800 border border-yellow-300 rounded-lg bg-yellow-50 dark:bg-gray-800 dark:text-yellow-300 dark:border-yellow-800" role="alert">
                    <div class="flex items-center">
                      <svg class="flex-shrink-0 w-4 h-4 me-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"/>
                      </svg>
                      <span class="sr-only ">Info</span>
                      <h3 class="text-lg font-medium px-2">طلب حوالة من فرع كركوك</h3>
                      <h3 class="text-lg font-medium ">  مبلغ 
                        {{ expense.amount }} 
                        دولار
                      </h3>
                    </div>
                    <div class="mt-2 mb-4 text-sm px-4">
                     {{ expense.note }}
                    </div>
                    <label  class="dark:text-gray-200 px-4" for="note" >أجور الحوالة بالدولار</label>
                          <input
                            type="number"
                            class="mt-3 mx-3 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900 "
                            v-model="expense.inputValue"
                            />
                            <input
                            type="text"
                            class="mt-3 mx-3 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900 "
                            v-model="expense.receiver"
                            />
                    <div class="flex items-center py-3" >
                      <svg class="flex-shrink-0 w-4 h-4 me-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"/>
                      </svg>
                      <span class="sr-only ">Info</span>
                      <h3 class="text-lg font-medium px-2">  صافي الحوالة بعد خصم أجر التحويل</h3>
                      <h3 class="text-lg font-medium ">  مبلغ 
                        {{ expense.amount - expense.inputValue }} 
                        دولار
                      </h3>
                    </div>
                    <div class="flex mt-5" >
                      <button @click="confirmTransfers(expense)" type="button" class="mx-2 text-white bg-yellow-800 hover:bg-yellow-900 focus:ring-4 focus:outline-none focus:ring-yellow-300 font-medium rounded-lg text-xs px-3 py-1.5 me-2 text-center inline-flex items-center dark:bg-yellow-300 dark:text-gray-800 dark:hover:bg-yellow-400 dark:focus:ring-yellow-800">
                       تأكيد الحوالات
                      </button >
                      <button @click="cancelTransfers(expense)" type="button" class="text-yellow-800 bg-transparent border border-yellow-800 hover:bg-yellow-900 hover:text-white focus:ring-4 focus:outline-none focus:ring-yellow-300 font-medium rounded-lg text-xs px-3 py-1.5 text-center dark:hover:bg-yellow-300 dark:border-yellow-300 dark:text-yellow-300 dark:hover:text-gray-800 dark:focus:ring-yellow-800" data-dismiss-target="#alert-additional-content-4" aria-label="Close" :disabled="!(formData.fee)">
                      إلغاء
                      </button>
                    </div>
                  </div>
                  </template>

               
                </div>

                <div v-else-if="activeTab == 'record'">
                  <div class="flex justify-between items-center mt-4 mb-2">
                    <h1 class="text-center dark:text-gray-200"> سجل الحولات</h1>
                    <button
                      @click="printTransfers('record')"
                      class="px-4 py-2 text-white bg-blue-600 rounded hover:bg-blue-700 flex items-center gap-2"
                      title="طباعة السجل"
                    >
                      <print class="w-4 h-4" />
                      طباعة
                    </button>
                  </div>
                  <div class="relative overflow-x-auto shadow-md sm:rounded-lg mt-4 mb-5" >
                  <table class="w-full text-sm text-right text-gray-500 dark:text-gray-200 dark:text-gray-400 text-center">
                  <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400 text-center" >
                  <tr  class="bg-rose-500 text-gray-100 rounded-l-lg mb-2 sm:mb-0">
                    <th className="px-1 py-2 text-base">رقم الوصل</th>
                    <th className="px-1 py-2 text-base">{{$t('date')}}</th>
                    <th className="px-1 py-2 text-base">المبلغ بالدولار</th>
                    <th className="px-1 py-2 text-base">أجور الحولات</th>
                    <th className="px-1 py-2 text-base">المبلغ بالدولار الصافي</th>
                    <th className="px-1 py-2 text-base">ملاحظة</th>
                    <th className="px-1 py-2 text-base">النوع</th>
                    <th className="px-1 py-2 text-base">الحالة</th>
                    <th className="px-1 py-2 text-base">تنفيذ</th>
                  </tr>
                </thead>
                <tbody>
                  <template  v-for="expense in activeTransfers" :key="expense.id">
                  <tr class="text-center" :class="expense.is_external ? 'bg-blue-50 dark:bg-blue-900' : ''">
                  <td className="px-4 py-2 border dark:border-gray-800 dark:text-gray-200">
                    {{ expense.id }}
                    <span v-if="expense.is_external" class="ml-2 text-xs text-blue-600 dark:text-blue-300" title="تحويل خارجي">🌐</span>
                  </td>
                  <td className="px-4 py-2 border dark:border-gray-800 dark:text-gray-200">{{ expense?.created_at?.slice(0, 19).replace('T', ' ') }}</td>
                  <td className="px-4 py-2 border dark:border-gray-800 dark:text-gray-200">{{ expense.amount   }}</td>
                  <td className="px-4 py-2 border dark:border-gray-800 dark:text-gray-200">{{ expense.fee   }}</td>
                  <td className="px-4 py-2 border dark:border-gray-800 dark:text-gray-200">{{ expense.amount-expense.fee   }}</td>
                  <td className="px-4 py-2 border dark:border-gray-800 dark:text-gray-200">
                    {{ expense.note }}
                    <div v-if="expense.is_external && expense.external_system_domain" class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                      من: {{ expense.external_system_domain }}
                    </div>
                  </td>
                  <td className="px-4 py-2 border dark:border-gray-800 dark:text-gray-200">
                    <span v-if="expense.is_external" class="px-2 py-1 text-xs font-semibold text-blue-700 bg-blue-100 dark:bg-blue-800 dark:text-blue-200 rounded">
                      خارجي
                    </span>
                    <span v-else class="px-2 py-1 text-xs font-semibold text-gray-700 bg-gray-100 dark:bg-gray-800 dark:text-gray-200 rounded">
                      محلي
                    </span>
                  </td>
                  <td className="px-4 py-2 border dark:border-gray-800 dark:text-gray-200">{{ expense.stauts   }}</td>
                  <td className="px-4 py-2 border dark:border-gray-800 dark:text-gray-200">
                    <button
                      @click="archiveTransfer(expense)"
                      class="px-3 py-1 text-sm font-semibold text-white bg-gray-600 rounded hover:bg-gray-700"
                      title="أرشفة التحويل"
                    >
                      أرشفة
                    </button>
                  </td>
                  </tr>
                  </template>
                  <tr v-if="activeTransfers.length === 0" class="text-center">
                    <td colspan="9" class="px-4 py-4 border dark:border-gray-800 dark:text-gray-200">لا توجد تحويلات</td>
                  </tr>
                </tbody>
              </table>
          </div>
                </div>
                
                <div v-else-if="activeTab == 'archive'">
                  <div class="flex justify-between items-center mt-4 mb-2">
                    <h1 class="text-center dark:text-gray-200"> أرشيف الحولات</h1>
                    <button
                      @click="printTransfers('archive')"
                      class="px-4 py-2 text-white bg-blue-600 rounded hover:bg-blue-700 flex items-center gap-2"
                      title="طباعة الأرشيف"
                    >
                      <print class="w-4 h-4" />
                      طباعة
                    </button>
                  </div>
                  <div class="relative overflow-x-auto shadow-md sm:rounded-lg mt-4 mb-5" >
                  <table class="w-full text-sm text-right text-gray-500 dark:text-gray-200 dark:text-gray-400 text-center">
                  <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400 text-center" >
                  <tr  class="bg-gray-600 text-gray-100 rounded-l-lg mb-2 sm:mb-0">
                    <th className="px-1 py-2 text-base">رقم الوصل</th>
                    <th className="px-1 py-2 text-base">{{$t('date')}}</th>
                    <th className="px-1 py-2 text-base">المبلغ بالدولار</th>
                    <th className="px-1 py-2 text-base">أجور الحولات</th>
                    <th className="px-1 py-2 text-base">المبلغ بالدولار الصافي</th>
                    <th className="px-1 py-2 text-base">ملاحظة</th>
                    <th className="px-1 py-2 text-base">النوع</th>
                    <th className="px-1 py-2 text-base">الحالة</th>
                    <th className="px-1 py-2 text-base">تنفيذ</th>
                  </tr>
                </thead>
                <tbody>
                  <template  v-for="expense in archivedTransfers" :key="expense.id">
                  <tr class="text-center" :class="expense.is_external ? 'bg-blue-50 dark:bg-blue-900' : ''">
                  <td className="px-4 py-2 border dark:border-gray-800 dark:text-gray-200">
                    {{ expense.id }}
                    <span v-if="expense.is_external" class="ml-2 text-xs text-blue-600 dark:text-blue-300" title="تحويل خارجي">🌐</span>
                  </td>
                  <td className="px-4 py-2 border dark:border-gray-800 dark:text-gray-200">{{ expense?.created_at?.slice(0, 19).replace('T', ' ') }}</td>
                  <td className="px-4 py-2 border dark:border-gray-800 dark:text-gray-200">{{ expense.amount   }}</td>
                  <td className="px-4 py-2 border dark:border-gray-800 dark:text-gray-200">{{ expense.fee   }}</td>
                  <td className="px-4 py-2 border dark:border-gray-800 dark:text-gray-200">{{ expense.amount-expense.fee   }}</td>
                  <td className="px-4 py-2 border dark:border-gray-800 dark:text-gray-200">
                    {{ expense.note }}
                    <div v-if="expense.is_external && expense.external_system_domain" class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                      من: {{ expense.external_system_domain }}
                    </div>
                  </td>
                  <td className="px-4 py-2 border dark:border-gray-800 dark:text-gray-200">
                    <span v-if="expense.is_external" class="px-2 py-1 text-xs font-semibold text-blue-700 bg-blue-100 dark:bg-blue-800 dark:text-blue-200 rounded">
                      خارجي
                    </span>
                    <span v-else class="px-2 py-1 text-xs font-semibold text-gray-700 bg-gray-100 dark:bg-gray-800 dark:text-gray-200 rounded">
                      محلي
                    </span>
                  </td>
                  <td className="px-4 py-2 border dark:border-gray-800 dark:text-gray-200">{{ expense.stauts   }}</td>
                  <td className="px-4 py-2 border dark:border-gray-800 dark:text-gray-200">
                    <button
                      @click="unarchiveTransfer(expense)"
                      class="px-3 py-1 text-sm font-semibold text-white bg-green-600 rounded hover:bg-green-700"
                      title="إعادة التحويل للسجل"
                    >
                      إعادة للسجل
                    </button>
                  </td>
                  </tr>
                  </template>
                  <tr v-if="archivedTransfers.length === 0" class="text-center">
                    <td colspan="9" class="px-4 py-4 border dark:border-gray-800 dark:text-gray-200">لا توجد تحويلات مؤرشفة</td>
                  </tr>
                </tbody>
              </table>
          </div>
                </div>


            </div>
  
            <div class="modal-footer my-2">
              <div class="flex flex-row">
                <div class="basis-1/2 px-4"> 
                  <button class="modal-default-button py-3  bg-gray-500 rounded"
                    @click="$emit('close');activeTab = 'add'">{{ $t('cancel') }}</button>
                  </div>
              <div class="basis-1/2 px-4">
                <button class="modal-default-button py-3  bg-rose-500 rounded col-6"  @click="$emit('close');" >{{ $t('yes') }}</button>
                </div>

            </div>
  
     
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </template>
  
  <style>
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
    width: 50%;
    min-width: 350px;
    margin: 0px auto;
    padding: 20px  30px;
    padding-bottom: 60px;
    background-color: #fff;
    border-radius: 2px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.33);
    transition: all 0.3s ease;
    border-radius: 10px;
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
    width: 100%;
    color: #fff;
  }
  
  /*
   * The following styles are auto-applied to elements with
   * transition="modal" when their visibility is toggled
   * by Vue.js.
   *
   * You can easily play with the modal transition by editing
   * these styles.
   */
  
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
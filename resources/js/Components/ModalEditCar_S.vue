<script setup>
import { ref, computed, watch, onMounted } from "vue";
import axios from 'axios';
import Uploader  from 'vue-media-upload';
import { useToast } from "vue-toastification";

const toast = useToast();




const props = defineProps({
  show: Boolean,
  formData: Object,
  client: Array,
});

function getTodayDate() {
  const today = new Date();
  const year = today.getFullYear();
  const month = String(today.getMonth() + 1).padStart(2, "0");
  const day = String(today.getDate()).padStart(2, "0");
  return `${year}-${month}-${day}`;
}
function check_vin(v){
  if(v){
    axios.get(`/api/check_vin?car_vin=${v}`)
  .then(response => {
    showErrorVin.value =  response.data;
  })
  .catch(error => {
    console.error(error);
  })
  }
}
let showClient = ref(false);
let showErrorVin = ref(false);
let exchangeRateError= ref(false);

// Car History Tab
const activeTab = ref('edit');
const carHistory = ref([]);
const loadingHistory = ref(false);
const historyPage = ref(1);
const historyLastPage = ref(1);
function validateExchangeRate(v) {
      const input = props.formData.dolar_price_s;
      if (/^\d{6}$/.test(input)) {
        exchangeRateError.value = false;
      } else {
        exchangeRateError.value = true;
      }
    }

function removeMedia(removedImage){
          axios.get('/api/carsAnnualImageDel?img_type=contract&name='+removedImage.name)
        .then(response => {
          toast.success("تم  حذف الصورة بنجاح", {
              timeout: 5000,
              position: "bottom-right",
              rtl: true

            });
        })
        .catch(error => {
          console.error(error);
        })
}

// Car History Functions
async function loadCarHistory(page = 1) {
  if (!props.formData?.id) return;
  
  loadingHistory.value = true;
  try {
    const response = await axios.get(`/api/car/${props.formData.id}/history`, {
      params: { page }
    });
    carHistory.value = response.data.data || [];
    historyPage.value = response.data.current_page || 1;
    historyLastPage.value = response.data.last_page || 1;
  } catch (error) {
    console.error('Error loading car history:', error);
    toast.error('حدث خطأ أثناء تحميل تاريخ السيارة', {
      timeout: 3000,
      position: "bottom-right",
      rtl: true,
    });
  } finally {
    loadingHistory.value = false;
  }
}

function getActionIcon(action) {
  const icons = {
    create: '➕',
    update: '✏️',
    delete: '🗑️',
    restore: '♻️',
    sale: '💰',
    purchase: '🛒'
  };
  return icons[action] || '📝';
}

function getActionText(action) {
  const actions = {
    create: 'إضافة',
    update: 'تحديث',
    delete: 'حذف',
    restore: 'استعادة',
    sale: 'بيع',
    purchase: 'شراء'
  };
  return actions[action] || action;
}

function formatDateTime(dateString) {
  if (!dateString || dateString === 'null' || dateString === null) return '';
  try {
    const date = new Date(dateString);
    if (isNaN(date.getTime())) return dateString; // إذا كان التاريخ غير صحيح، إرجاع القيمة الأصلية
    return date.toLocaleString('ar-IQ', {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    });
  } catch (error) {
    console.error('Error formatting date:', error);
    return dateString;
  }
}

function filterChanges(changes) {
  if (!changes) return {};
  const filtered = {};
  Object.keys(changes).forEach(key => {
    if (key !== 'created_at' && key !== 'updated_at') {
      filtered[key] = changes[key];
    }
  });
  return filtered;
}

function getFieldLabel(fieldName) {
  const fieldLabels = {
    // مشتريات (Purchase)
    'dinar': 'دينار',
    'dolar_price': 'سعر الصرف',
    'dolar_custom': 'سعر الصرف مخصص',
    'checkout': 'تخليص',
    'shipping_dolar': 'نقل مشتريات',
    'coc_dolar': 'شهادة المنشأ',
    'total': 'المجموع',
    'paid': 'المدفوع',
    'profit': 'الربح',
    'expenses': 'المصروفات',
    'land_shipping': 'نقل بري مشتريات',
    'land_shipping_dinar': 'نقل بري دينار مشتريات',
    
    // مبيعات (Sales)
    'dinar_s': 'دينار مبيعات',
    'dolar_price_s': 'سعر الصرف مبيعات',
    'dolar_custom_s': 'سعر الصرف مخصص مبيعات',
    'checkout_s': 'تخليص مبيعات',
    'shipping_dolar_s': 'نقل مبيعات',
    'coc_dolar_s': 'شهادة المنشأ مبيعات',
    'total_s': 'المجموع مبيعات',
    'expenses_s': 'المصروفات مبيعات',
    'land_shipping_s': 'نقل بري مبيعات',
    'land_shipping_dinar_s': 'نقل بري دينار مبيعات',
    
    // معلومات السيارة
    'car_type': 'نوع السيارة',
    'year': 'السنة',
    'car_color': 'اللون',
    'vin': 'رقم الشاصي',
    'car_number': 'رقم السيارة',
    'date': 'التاريخ',
    'note': 'ملاحظة',
    'client_id': 'الزبون',
    'discount': 'الخصم',
    'car_price': 'سعر السيارة',
  };
  
  return fieldLabels[fieldName] || fieldName;
}

// Watch for modal open and load history
watch(() => props.show, (newVal) => {
  if (newVal && props.formData?.id) {
    if (activeTab.value === 'history') {
      loadCarHistory();
    }
  }
});

watch(() => activeTab.value, (newVal) => {
  if (newVal === 'history' && props.formData?.id && carHistory.value.length === 0) {
    loadCarHistory();
  }
});
</script>
  <template>
  <Transition name="modal">
    <div v-if="show" class="modal-mask">
      <div class="modal-wrapper  max-h-[80vh]">
        <div class="modal-container dark:bg-gray-900 overflow-auto  max-h-[80vh]">
          <div class="modal-header">
            <slot name="header">
              <h2 class="text-center dark:text-gray-200">
                {{ $t("edit_car") }}
              </h2>
            </slot>
          </div>

          <!-- Tabs -->
          <div class="border-b border-gray-200 dark:border-gray-700 mb-4">
            <div class="flex space-x-8" style="direction: rtl;">
              <button
                @click="activeTab = 'edit'"
                :class="[
                  'py-2 px-4 text-sm font-medium border-b-2 transition-colors',
                  activeTab === 'edit'
                    ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                    : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'
                ]"
              >
                ✏️ تعديل
              </button>
              <button
                @click="activeTab = 'history'"
                :class="[
                  'py-2 px-4 text-sm font-medium border-b-2 transition-colors',
                  activeTab === 'history'
                    ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                    : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'
                ]"
              >
                📜 تاريخ السيارة
              </button>
            </div>
          </div>

          <!-- Edit Tab -->
          <div v-if="activeTab === 'edit'" class="modal-body">
            <div
              class="grid grid-cols-1 md:grid-cols-1 lg:grid-cols-1 gap-1 lg:gap-2"
            >
              <div class="mb-4 mx-1">
                <label class="dark:text-gray-200" for="color_id">{{
                  $t("car_owner")
                }}</label>
                <div class="relative">
                  <select
                    v-if="!showClient"
                    v-model="formData.client_id"
                    id="color_id"
                    class="pr-8 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                    disabled>
                    <option selected disabled>
                      {{ $t("selectCustomer") }}
                    </option>
                    <option
                      v-for="(card, index) in client"
                      :key="index"
                      :value="card.id"
                      >
                      {{ card.name }}
                    </option>
                  </select>
                
                </div>
         
              </div>
    
            </div>
            <div
              class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-1 lg:gap-2"
            >
              <div className="mb-4 mx-1">
                <label class="dark:text-gray-200" for="pin">
                  {{ $t("car_type") }}</label
                >
                <input
                  id="car_type"
                  type="text"
                  class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
                  v-model="formData.car_type"
                />
              </div>
              <div className="mb-4 mx-1">
                <label class="dark:text-gray-200" for="pin">
                  {{ $t("year") }}</label
                >
                <input
                  id="year"
                  type="number"
                  class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
                  v-model="formData.year"
                />
              </div>
              <div className="mb-4 mx-1">
                <label class="dark:text-gray-200" for="pin">
                  {{ $t("color") }}</label
                >
                <input
                  id="car_color"
                  type="text"
                  class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
                  v-model="formData.car_color"
                />
              </div>

              <div className="mb-4 mx-1">
                <label class="dark:text-gray-200" for="pin">
                  {{ $t("vin") }}</label
                >
                <input
                  id="vin"
                  type="text"
                  @change="check_vin(formData.vin)"
                  class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
                  v-model="formData.vin"
                />
                <div class="text-red-700" v-if="showErrorVin">
                  رقم الشاصي مستخدم
                </div>
              </div>
              <div className="mb-4 mx-1">
                <label class="dark:text-gray-200" for="car_number">
                  {{ $t("car_number") }}</label
                >
                <input
                  id="car_number"
                  type="text"
                  class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
                  v-model="formData.car_number"
                />
              </div>
              <div className="mb-4 mx-1">
                <label class="dark:text-gray-200" for="expenses">
                  {{ $t("expenses") }}</label
                >
                <input
                  id="expenses"
                  type="number"
                  class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
                  v-model="formData.expenses_s"
                />
              </div>
              <div className="mb-4 mx-1">
                <label class="dark:text-gray-200" for="dinar_s">
                  {{ $t("dinar") }}</label
                >
                <input
                  id="dinar_s"
                  type="number"
                  class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
                  v-model="formData.dinar_s"
                />
              </div>
              <div className="mb-4 mx-1">
                <label class="dark:text-gray-200" for="dolar_price_s">
                  {{ $t("dolar_price") }}</label
                >
                <input
                  id="dolar_price_s"
                  @change="validateExchangeRate"
                  type="number"
                  class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
                  v-model="formData.dolar_price_s"
                />
                <div v-if="exchangeRateError" class="text-red-500">
                          مطلوب رقم من 6 خانة فقط
                </div>
              </div>
              <div className="mb-4 mx-1">
                <label class="dark:text-gray-200" for="shipping_dolar_s">
                  {{ $t("shipping_dolar") }}</label
                >
                <input
                  id="shipping_dolar"
                  type="number"
                  class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
                  v-model="formData.shipping_dolar_s"
                />
              </div>
              <div className="mb-4 mx-1">
                <label class="dark:text-gray-200" for="coc_dolar_s">
                  {{ $t("coc_dolar") }}</label
                >
                <input
                  id="coc_dolar_s"
                  type="number"
                  class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
                  v-model="formData.coc_dolar_s"
                />
              </div>
              <div className="mb-4 mx-1">
                <label class="dark:text-gray-200" for="checkout_s">
                  {{ $t("checkout") }}</label
                >
                <input
                  id="checkout_s"
                  type="number"
                  class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
                  v-model="formData.checkout_s"
                />
              </div>
              <!-- <div className="mb-4 mx-1">
                <label class="dark:text-gray-200" for="paid">
                  {{ $t("paid") }}</label
                >
                <input
                  id="paid"
                  type="number"
                  class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
                  v-model="formData.paid"
                />
              </div> -->
              <div className="mb-4 mx-1">
                <label class="dark:text-gray-200" for="expenses">
                 نقل بري
                  </label
                >
                <input
                  id="expenses"
                  type="number"
                  class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
                  v-model="formData.land_shipping_s"
                />
              </div>
              <div className="mb-4 mx-1">
                <label class="dark:text-gray-200" for="date">
                  {{ $t("date") }}</label
                >
                <input
                  id="date"
                  type="date"
                  class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
                  v-model="formData.date"
                />
              </div>
            </div>

            <div className="mb-4 mx-1">
              <label class="dark:text-gray-200" for="note">
                {{$t("note")}}
              </label>
              <input
                id="note"
                type="text"
                class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
                v-model="formData.note"
              />
            </div>
          </div>

          <!-- History Tab -->
          <div v-if="activeTab === 'history'" class="modal-body">
            <div class="max-h-[60vh] overflow-y-auto">
              <div v-if="loadingHistory" class="text-center py-8">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                <p class="mt-4 text-gray-600 dark:text-gray-400">جاري تحميل التاريخ...</p>
              </div>

              <div v-else-if="carHistory.length === 0" class="text-center py-8">
                <div class="text-5xl mb-2">📭</div>
                <p class="text-lg text-gray-500 dark:text-gray-400">لا يوجد تاريخ للسيارة</p>
              </div>

              <div v-else class="space-y-4">
                <div
                  v-for="(item, index) in carHistory"
                  :key="item.id || index"
                  class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4 border-r-4"
                  :class="{
                    'border-green-500': item.action === 'create',
                    'border-blue-500': item.action === 'update',
                    'border-red-500': item.action === 'delete',
                    'border-yellow-500': item.action === 'restore',
                    'border-purple-500': item.action === 'sale' || item.action === 'purchase'
                  }"
                >
                  <div class="flex items-start justify-between">
                    <div class="flex-1">
                      <div class="flex items-center gap-2 mb-2">
                        <span class="text-2xl">{{ getActionIcon(item.action) }}</span>
                        <span class="font-semibold text-gray-900 dark:text-gray-100">
                          {{ getActionText(item.action) }}
                        </span>
                        <span class="text-xs px-2 py-1 rounded-full bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                          {{ formatDateTime(item.created_at) }}
                        </span>
                      </div>
                      
                      <p class="text-sm text-gray-700 dark:text-gray-300 mb-2">
                        {{ item.description || 'لا يوجد وصف' }}
                      </p>

                      <div v-if="item.user" class="text-xs text-gray-500 dark:text-gray-400">
                        👤 {{ item.user.name || 'مستخدم غير معروف' }}
                      </div>

                      <template v-if="item.changes && Object.keys(filterChanges(item.changes)).length > 0">
                        <div class="mt-3 p-3 bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700 text-xs">
                          <div class="font-semibold mb-2 text-gray-700 dark:text-gray-300 text-sm">التغييرات:</div>
                          <div class="space-y-2">
                            <template v-for="(value, key) in filterChanges(item.changes)" :key="key">
                              <div class="flex items-center gap-2 py-1 px-2 rounded bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                <span class="font-medium text-gray-700 dark:text-gray-300 min-w-[120px]">{{ getFieldLabel(key) }}:</span>
                                <div class="flex items-center gap-2 flex-1">
                                  <span class="text-red-600 dark:text-red-400 line-through bg-red-50 dark:bg-red-900/20 px-2 py-0.5 rounded text-xs font-medium">{{ value.old || 'فارغ' }}</span>
                                  <span class="text-blue-500 dark:text-blue-400 text-lg font-bold">⟵</span>
                                  <span class="text-green-600 dark:text-green-400 bg-green-50 dark:bg-green-900/20 px-2 py-0.5 rounded text-xs font-medium">{{ value.new || 'فارغ' }}</span>
                                </div>
                              </div>
                            </template>
                          </div>
                        </div>
                      </template>
                    </div>
                  </div>
                </div>

                <!-- Pagination -->
                <div v-if="historyLastPage > 1" class="flex justify-center items-center gap-2 mt-4">
                  <button
                    @click="loadCarHistory(historyPage - 1)"
                    :disabled="historyPage === 1"
                    class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed"
                  >
                    السابق
                  </button>
                  <span class="text-sm text-gray-600 dark:text-gray-400">
                    صفحة {{ historyPage }} من {{ historyLastPage }}
                  </span>
                  <button
                    @click="loadCarHistory(historyPage + 1)"
                    :disabled="historyPage >= historyLastPage"
                    class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed"
                  >
                    التالي
                  </button>
                </div>
              </div>
            </div>
          </div>

          <div class="modal-footer my-2" v-if="activeTab === 'edit'">
    

            <div class="grid grid-cols-1 md:grid-cols-1 lg:grid-cols-1 gap-1 lg:gap-2">
              <div class="mb-4">
                <label class="form-label">الصور</label>
                <div class="mt-3">
                    <Uploader 
                        :server="'/api/carsAnnualUpload?img_type=contract&carId='+formData.id"
                        :is-invalid="errors?.media ? true : false"
                        @change="changeMedia"
                        @initMedia="media"
                        location="/public/uploadsResized"
                        :media="formData.car_images"
                        @add="addMedia"
                        @remove="removeMedia"
                    />
                </div>
                <p v-if="errors?.media" class="text-danger">{{ errors?.media[0] }}</p>
            </div>
             </div>

             <div class="flex flex-row">
              <div class="basis-1/2 px-4">
                <button
                  class="modal-default-button py-3 bg-gray-500 rounded"
                  @click="$emit('close')"
                >
                  {{ $t("cancel") }}
                </button>
              </div>
              <div class="basis-1/2 px-4">
                <button
                  class="modal-default-button py-3 bg-rose-500 rounded col-6"
                  @click="
                    formData.date = formData.date
                      ? formData.date
                      : getTodayDate();
                    $emit('a', formData);
                    formData = '';
                  "
                  :disabled="(!formData.client_id)&&(!formData.client_name)">
                  {{ $t("yes") }}
                </button>
              </div>
            </div>
          </div>

          <!-- Footer for History Tab -->
          <div class="modal-footer my-2" v-if="activeTab === 'history'">
            <div class="flex flex-row">
              <div class="basis-full px-4">
                <button
                  class="modal-default-button py-3 bg-gray-500 rounded w-full"
                  @click="$emit('close')"
                >
                  {{ $t("cancel") }}
                </button>
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
  padding: 20px 30px;
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
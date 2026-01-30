<script setup>
import { ref, watch, onMounted } from 'vue';
import axios from 'axios';
import { useToast } from "vue-toastification";
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { ModelListSelect } from "vue-search-select";
import "vue-search-select/dist/VueSearchSelect.css";

const props = defineProps({
  tripId: {
    type: [String, Number],
    required: true,
  },
  tripCompanyId: {
    type: [String, Number],
    required: true,
  },
  car: {
    type: Object,
    default: null,
  },
  show: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['close', 'saved']);

const toast = useToast();

const formData = ref({
  weight: '',
  description: '',
  chassis_no: '',
  consignee_name: '',
  consignee_id: null,
  code: '',
});

const consigneeOptions = ref([]);
const isSearchingConsignees = ref(false);
const isSaving = ref(false);

// جلب جميع العملاء (Consignees) للـ select
const loadAllConsignees = async () => {
  console.log('🔄 Starting loadAllConsignees...');
  isSearchingConsignees.value = true;
  try {
    console.log('📡 Making request to /trips/search-companies with q=""');
    const response = await axios.get('/trips/search-companies', {
      params: { q: '' }, // جلب جميع المستخدمين
    });
    
    console.log('✅ Response received:', response);
    console.log('📦 Response data:', response.data);
    console.log('📦 Response data type:', typeof response.data);
    console.log('📦 Is array?', Array.isArray(response.data));
    
    const allConsignees = Array.isArray(response.data) ? response.data : [];
    
    console.log('📋 All consignees:', allConsignees);
    console.log('📊 Count:', allConsignees.length);
    
    if (allConsignees.length === 0) {
      console.warn('⚠️ No consignees found! Response data:', response.data);
    }
    
    consigneeOptions.value = allConsignees.map(consignee => {
      const option = {
        value: consignee.id,
        text: consignee.name,
        phone: consignee.phone || '',
      };
      console.log('🔹 Mapped option:', option);
      return option;
    });
    
    console.log('✅ Final consigneeOptions:', consigneeOptions.value);
    console.log('✅ Final count:', consigneeOptions.value.length);
  } catch (error) {
    console.error('❌ Error loading consignees:', error);
    console.error('❌ Error message:', error.message);
    console.error('❌ Error response:', error.response);
    console.error('❌ Error response data:', error.response?.data);
    console.error('❌ Error response status:', error.response?.status);
    consigneeOptions.value = [];
    toast.error('حدث خطأ أثناء جلب قائمة المستلمين: ' + (error.response?.data?.message || error.message));
  } finally {
    isSearchingConsignees.value = false;
    console.log('🏁 Finished loadAllConsignees');
  }
};

// البحث في العملاء
const searchConsignees = async (searchText) => {
  if (!searchText || searchText.trim().length < 1) {
    await loadAllConsignees();
    return;
  }

  isSearchingConsignees.value = true;
  try {
    const response = await axios.get('/trips/search-companies', {
      params: { q: searchText.trim() },
    });
    const foundConsignees = Array.isArray(response.data) ? response.data : [];
    
    console.log('Found consignees:', foundConsignees);
    
    consigneeOptions.value = foundConsignees.map(consignee => ({
      value: consignee.id,
      text: consignee.name,
      phone: consignee.phone || '',
    }));
  } catch (error) {
    console.error('Error searching consignees:', error);
    console.error('Error response:', error.response);
    consigneeOptions.value = [];
    toast.error('حدث خطأ أثناء البحث عن المستلمين');
  } finally {
    isSearchingConsignees.value = false;
  }
};

// معالجة اختيار العميل
watch(() => formData.value.consignee_id, (newId) => {
  if (newId) {
    const consignee = consigneeOptions.value.find(c => c.value === newId);
    if (consignee) {
      formData.value.consignee_name = consignee.text;
    }
  }
});

// تحميل البيانات عند فتح النموذج
watch(() => props.show, (isVisible) => {
  if (isVisible) {
    if (props.car) {
      // وضع التعديل
      formData.value = {
        weight: props.car.weight || '',
        description: props.car.description || '',
        chassis_no: props.car.chassis_no || '',
        consignee_name: props.car.consignee_name || '',
        consignee_id: props.car.consignee_id || null,
        code: props.car.code || '',
      };
    } else {
      // وضع الإضافة
      formData.value = {
        weight: '',
        description: '',
        chassis_no: '',
        consignee_name: '',
        consignee_id: null,
        code: '',
      };
    }
    // تحميل قائمة الشركات إذا لم تكن محملة مسبقاً
    if (consigneeOptions.value.length === 0) {
      loadAllConsignees();
    }
  }
});

onMounted(() => {
  // تحميل قائمة الشركات مبدئياً عند تحميل المكون (قبل فتح النموذج)
  loadAllConsignees();
});

// حفظ السيارة
const saveCar = async () => {
  if (!formData.value.consignee_name || formData.value.consignee_name.trim().length < 2) {
    toast.error('يرجى إدخال اسم المستلم');
    return;
  }

  isSaving.value = true;
  try {
    const payload = {
      trip_company_id: props.tripCompanyId,
      weight: formData.value.weight ? parseFloat(formData.value.weight) : null,
      description: formData.value.description || null,
      chassis_no: formData.value.chassis_no || null,
      consignee_name: formData.value.consignee_name.trim(),
      consignee_id: formData.value.consignee_id || null,
      code: formData.value.code || null,
    };

    let response;
    if (props.car) {
      // تحديث
      response = await axios.put(`/api/trips/cars/${props.car.id}`, payload);
    } else {
      // إضافة
      response = await axios.post(`/api/trips/${props.tripId}/cars`, payload);
    }

    if (response.data.success) {
      toast.success(response.data.message || (props.car ? 'تم تحديث السيارة بنجاح' : 'تم إضافة السيارة بنجاح'));
      emit('saved', response.data.car);
      emit('close');
    } else {
      toast.error(response.data.message || 'حدث خطأ أثناء الحفظ');
    }
  } catch (error) {
    console.error(error);
    const errorMessage = error.response?.data?.message || 'حدث خطأ أثناء الحفظ';
    toast.error(errorMessage);
  } finally {
    isSaving.value = false;
  }
};
</script>

<template>
  <Transition name="modal">
    <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
      <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Background overlay -->
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="emit('close')"></div>

        <!-- Center modal -->
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-right overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
          <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                {{ car ? 'تعديل سيارة' : 'إضافة سيارة جديدة' }}
              </h3>
              <button
                @click="emit('close')"
                class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300"
                :disabled="isSaving"
              >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>

            <div class="space-y-4">
              <!-- Weight -->
              <div>
                <InputLabel for="weight" value="الوزن (كغ)" />
                <TextInput
                  id="weight"
                  v-model="formData.weight"
                  type="number"
                  step="0.01"
                  min="0"
                  placeholder="مثال: 1100"
                  class="mt-1 block w-full"
                  :disabled="isSaving"
                />
              </div>

              <!-- Description -->
              <div>
                <InputLabel for="description" value="الوصف" />
                <TextInput
                  id="description"
                  v-model="formData.description"
                  type="text"
                  placeholder="مثال: TOYOTA COROLLA 2025 WHITE"
                  class="mt-1 block w-full"
                  :disabled="isSaving"
                />
              </div>

              <!-- Chassis No -->
              <div>
                <InputLabel for="chassis_no" value="رقم الشاسيه" />
                <TextInput
                  id="chassis_no"
                  v-model="formData.chassis_no"
                  type="text"
                  placeholder="مثال: LFMAAA0C5S0684593"
                  class="mt-1 block w-full"
                  :disabled="isSaving"
                />
              </div>

              <!-- Consignee -->
              <div>
                <InputLabel for="consignee" value="المستلم (Consignee) *" />
                <div class="mt-1">
                  <ModelListSelect
                    v-model="formData.consignee_id"
                    :list="consigneeOptions"
                    optionValue="value"
                    optionText="text"
                    :placeholder="isSearchingConsignees ? 'جاري البحث...' : 'ابحث واختر المستلم...'"
                    :disabled="isSaving"
                    @searchchange="searchConsignees"
                  >
                  </ModelListSelect>
                </div>
                <div v-if="!formData.consignee_id" class="mt-2">
                  <InputLabel for="consignee_name" value="أو أدخل اسم المستلم يدوياً *" />
                  <TextInput
                    id="consignee_name"
                    v-model="formData.consignee_name"
                    type="text"
                    placeholder="مثال: DIANA LOGISTICS INTERNATIONAL TRANSPORT CO LTD"
                    class="mt-1 block w-full"
                    :disabled="isSaving"
                  />
                </div>
              </div>

              <!-- Code -->
              <div>
                <InputLabel for="code" value="الكود" />
                <TextInput
                  id="code"
                  v-model="formData.code"
                  type="text"
                  placeholder="مثال: BURJ KHOR"
                  class="mt-1 block w-full"
                  :disabled="isSaving"
                />
              </div>
            </div>
          </div>

          <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-3">
            <button
              @click="saveCar"
              :disabled="isSaving || !formData.consignee_name || formData.consignee_name.trim().length < 2"
              class="w-full inline-flex justify-center sm:w-auto px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 disabled:cursor-not-allowed transition ease-in-out duration-150"
            >
              <span v-if="isSaving" class="flex items-center">
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                جاري الحفظ...
              </span>
              <span v-else>{{ car ? 'تحديث' : 'إضافة' }}</span>
            </button>
            <button
              @click="emit('close')"
              :disabled="isSaving"
              class="mt-3 w-full inline-flex justify-center sm:mt-0 sm:w-auto px-4 py-2 bg-white dark:bg-gray-600 border border-gray-300 dark:border-gray-500 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-200 uppercase tracking-widest hover:bg-gray-50 dark:hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-25 disabled:cursor-not-allowed transition ease-in-out duration-150"
            >
              إلغاء
            </button>
          </div>
        </div>
      </div>
    </div>
  </Transition>
</template>

<style scoped>
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}
</style>

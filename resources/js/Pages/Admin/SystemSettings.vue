<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/inertia-vue3';
import { ref, onMounted } from 'vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { useToast } from "vue-toastification";
import axios from 'axios';

const toast = useToast();

const activeTab = ref('system-config');
const loading = ref(false);
const saving = ref(false);

// System Config
const systemConfig = ref({
  first_title_ar: '',
  first_title_kr: '',
  second_title_ar: '',
  second_title_kr: '',
  third_title_ar: '',
  third_title_kr: '',
  default_price_s: [],
  default_price_p: [],
  usd_to_aed_rate: 3.6725,
  usd_to_dinar_rate: 150.00,
  contract_terms: [],
  contract_terms_2: [],
  contract_template: 1,
  contract_currency: 'usd',
});

// تحويل JSON arrays إلى arrays من objects {key, value}
const defaultPriceSItems = ref([]);
const defaultPricePItems = ref([]);
const contractTermsItems = ref([]);
const contractTerms2Items = ref([]);

// Connected Systems
const systems = ref([]);
const showModal = ref(false);
const editingSystem = ref(null);
const formData = ref({
  name: '',
  domain: '',
  api_key: '',
  is_active: true
});

onMounted(() => {
  loadSystemConfig();
  loadSystems();
});

function convertArrayToItems(data) {
  // إذا كان array، خذ العنصر الأول
  if (Array.isArray(data) && data.length > 0) {
    data = data[0];
  }
  
  // إذا لم يكن object، أرجع array فارغ
  if (!data || typeof data !== 'object' || Array.isArray(data)) {
    return [];
  }
  
  // تحويل object إلى array من {key, value}
  return Object.entries(data).map(([key, value], index) => ({
    id: Date.now() + index,
    key: key,
    value: value !== null && value !== undefined ? String(value) : ''
  }));
}

function convertItemsToArray(items) {
  // تحويل array من {key, value} إلى object واحد
  const result = {};
  items
    .filter(item => item.key && item.key.trim())
    .forEach(item => {
      // محاولة تحويل القيمة إلى رقم إذا أمكن
      const numValue = Number(item.value);
      result[item.key] = !isNaN(numValue) && item.value.trim() !== '' ? numValue : item.value;
    });
  return result;
}

function loadSystemConfig() {
  loading.value = true;
  axios.get('/api/system-config')
    .then(response => {
      systemConfig.value = response.data;
      // تحويل JSON arrays إلى items للعرض
      const priceS = systemConfig.value.default_price_s || [];
      const priceP = systemConfig.value.default_price_p || [];
      const terms = systemConfig.value.contract_terms || [];
      const terms2 = systemConfig.value.contract_terms_2 || [];
      
      defaultPriceSItems.value = convertArrayToItems(priceS);
      defaultPricePItems.value = convertArrayToItems(priceP);
      
      // تحميل شروط قالب 1
      if (Array.isArray(terms) && terms.length > 0) {
        contractTermsItems.value = terms.map((term, index) => ({ id: Date.now() + index, text: term }));
      } else {
        contractTermsItems.value = [
          { id: 1, text: 'علی البائع و المشتری تسجیل السیارة حسب قوانین مدیریة المرور العامة مع إجراء معاملة نقل الملکیة' },
          { id: 2, text: 'علی المشتری فحص السیارة قبل الشراء و نحن غیر مسؤولین بعد توقیع عقد المعرض' },
          { id: 3, text: 'الطرف الاول مسؤول عن کافة أنواع الغرامات قبل موعد الشراء' },
          { id: 4, text: 'صاحب المعرض غیر مسؤول عن السیارة بعد البیع و کل عقد غیر مختوم من المعرض یعتبر باطل' },
          { id: 5, text: 'علی المشتري تسجیل السیارة خلال شهر واحد' },
        ];
      }
      // تحميل شروط قالب 2
      if (Array.isArray(terms2) && terms2.length > 0) {
        contractTerms2Items.value = terms2.map((term, index) => ({ id: Date.now() + index + 1000, text: term }));
      } else {
        contractTerms2Items.value = [];
      }
      
    })
    .catch(error => {
      console.error('Error loading system config:', error);
      toast.error('حدث خطأ أثناء تحميل إعدادات النظام', {
        timeout: 3000,
        position: "bottom-right",
        rtl: true,
      });
    })
    .finally(() => {
      loading.value = false;
    });
}


function saveSystemConfig() {
  // تحويل items إلى arrays
  const priceS = convertItemsToArray(defaultPriceSItems.value);
  const priceP = convertItemsToArray(defaultPricePItems.value);
  
  const terms = contractTermsItems.value
    .filter(item => item.text && item.text.trim())
    .map(item => item.text.trim());
  const terms2 = contractTerms2Items.value
    .filter(item => item.text && item.text.trim())
    .map(item => item.text.trim());
  
  const dataToSave = {
    ...systemConfig.value,
    default_price_s: priceS,
    default_price_p: priceP,
    contract_terms: terms,
    contract_terms_2: terms2,
    contract_template: systemConfig.value.contract_template ?? 1,
    contract_currency: systemConfig.value.contract_currency ?? 'usd',
  };
  
  saving.value = true;
  axios.put('/api/system-config', dataToSave)
    .then(response => {
      toast.success('تم حفظ إعدادات النظام بنجاح', {
        timeout: 2000,
        position: "bottom-right",
        rtl: true,
      });
      systemConfig.value = response.data.config;
      defaultPriceSItems.value = convertArrayToItems(systemConfig.value.default_price_s || []);
      defaultPricePItems.value = convertArrayToItems(systemConfig.value.default_price_p || []);
      
      const terms = systemConfig.value.contract_terms || [];
      const terms2 = systemConfig.value.contract_terms_2 || [];
      if (Array.isArray(terms) && terms.length > 0) {
        contractTermsItems.value = terms.map((term, index) => ({ id: Date.now() + index, text: term }));
      }
      if (Array.isArray(terms2) && terms2.length > 0) {
        contractTerms2Items.value = terms2.map((term, index) => ({ id: Date.now() + index + 1000, text: term }));
      } else {
        contractTerms2Items.value = [];
      }
    })
    .catch(error => {
      console.error('Error saving system config:', error);
      toast.error('حدث خطأ أثناء حفظ إعدادات النظام', {
        timeout: 3000,
        position: "bottom-right",
        rtl: true,
      });
    })
    .finally(() => {
      saving.value = false;
    });
}

function loadSystems() {
  axios.get('/api/all-connected-systems')
    .then(response => {
      systems.value = response.data;
    })
    .catch(error => {
      console.error('Error loading systems:', error);
      toast.error('حدث خطأ أثناء تحميل الأنظمة', {
        timeout: 3000,
        position: "bottom-right",
        rtl: true,
      });
    });
}

function openAddModal() {
  editingSystem.value = null;
  formData.value = {
    name: '',
    domain: '',
    api_key: '',
    is_active: true
  };
  showModal.value = true;
}

function openEditModal(system) {
  editingSystem.value = system;
  formData.value = {
    name: system.name,
    domain: system.domain,
    api_key: system.api_key,
    is_active: system.is_active
  };
  showModal.value = true;
}

function closeModal() {
  showModal.value = false;
  editingSystem.value = null;
  formData.value = {
    name: '',
    domain: '',
    api_key: '',
    is_active: true
  };
}

function saveSystem() {
  if (!formData.value.name || !formData.value.domain || !formData.value.api_key) {
    toast.error('يرجى ملء جميع الحقول المطلوبة', {
      timeout: 3000,
      position: "bottom-right",
      rtl: true,
    });
    return;
  }

  if (editingSystem.value) {
    axios.put(`/api/connected-systems/${editingSystem.value.id}`, formData.value)
      .then(response => {
        toast.success('تم تحديث النظام بنجاح', {
          timeout: 2000,
          position: "bottom-right",
          rtl: true,
        });
        loadSystems();
        closeModal();
      })
      .catch(error => {
        console.error('Error updating system:', error);
        toast.error('حدث خطأ أثناء تحديث النظام', {
          timeout: 3000,
          position: "bottom-right",
          rtl: true,
        });
      });
  } else {
    axios.post('/api/connected-systems', formData.value)
      .then(response => {
        toast.success('تم إضافة النظام بنجاح', {
          timeout: 2000,
          position: "bottom-right",
          rtl: true,
        });
        loadSystems();
        closeModal();
      })
      .catch(error => {
        console.error('Error adding system:', error);
        toast.error('حدث خطأ أثناء إضافة النظام', {
          timeout: 3000,
          position: "bottom-right",
          rtl: true,
        });
      });
  }
}

function deleteSystem(system) {
  if (!confirm(`هل أنت متأكد من حذف النظام "${system.name}"؟`)) {
    return;
  }

  axios.delete(`/api/connected-systems/${system.id}`)
    .then(response => {
      toast.success('تم حذف النظام بنجاح', {
        timeout: 2000,
        position: "bottom-right",
        rtl: true,
      });
      loadSystems();
    })
    .catch(error => {
      console.error('Error deleting system:', error);
      toast.error('حدث خطأ أثناء حذف النظام', {
        timeout: 3000,
        position: "bottom-right",
        rtl: true,
      });
    });
}

function toggleActive(system) {
  axios.put(`/api/connected-systems/${system.id}`, {
    is_active: !system.is_active
  })
    .then(response => {
      toast.success(`تم ${system.is_active ? 'تعطيل' : 'تفعيل'} النظام بنجاح`, {
        timeout: 2000,
        position: "bottom-right",
        rtl: true,
      });
      loadSystems();
    })
    .catch(error => {
      console.error('Error toggling system:', error);
      toast.error('حدث خطأ أثناء تحديث حالة النظام', {
        timeout: 3000,
        position: "bottom-right",
        rtl: true,
      });
    });
}

function addContractTerm() {
  contractTermsItems.value.push({
    id: Date.now(),
    text: ''
  });
}

function removeContractTerm(index) {
  contractTermsItems.value.splice(index, 1);
}
function addContractTerm2() {
  contractTerms2Items.value.push({ id: Date.now(), text: '' });
}
function removeContractTerm2(index) {
  contractTerms2Items.value.splice(index, 1);
}

const testingConnection = ref(false);

function testConnection() {
  if (!formData.value.domain || !formData.value.api_key) {
    toast.warning('الرجاء إدخال الدومين و API Key أولاً', {
      timeout: 2000,
      position: "bottom-right",
      rtl: true,
    });
    return;
  }

  testingConnection.value = true;
  
  axios.post('/api/test-connection', {
    domain: formData.value.domain,
    api_key: formData.value.api_key
  })
  .then(response => {
    if (response.data.success) {
      toast.success(response.data.message || 'تم التحقق من الاتصال بنجاح', {
        timeout: 2000,
        position: "bottom-right",
        rtl: true,
      });
    } else {
      toast.error(response.data.message || 'فشل التحقق من الاتصال', {
        timeout: 3000,
        position: "bottom-right",
        rtl: true,
      });
    }
  })
  .catch(error => {
    const errorMessage = error.response?.data?.message || error.message || 'حدث خطأ أثناء التحقق من الاتصال';
    toast.error(errorMessage, {
      timeout: 3000,
      position: "bottom-right",
      rtl: true,
    });
  })
  .finally(() => {
    testingConnection.value = false;
  });
}
</script>

<template>
  <Head title="إعدادات النظام" />
  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
        إعدادات النظام
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 text-gray-900 dark:text-gray-100">
            <!-- Tabs -->
            <div class="border-b border-gray-200 dark:border-gray-700 mb-6">
              <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                <button
                  @click="activeTab = 'system-config'"
                  :class="activeTab === 'system-config' 
                    ? 'border-blue-500 text-blue-600 dark:text-blue-400' 
                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'"
                  class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm"
                >
                  إعدادات النظام
                </button>
                <button
                  @click="activeTab = 'connected-systems'"
                  :class="activeTab === 'connected-systems' 
                    ? 'border-blue-500 text-blue-600 dark:text-blue-400' 
                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'"
                  class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm"
                >
                  الأنظمة المتصلة
                </button>
              </nav>
            </div>

            <!-- System Config Tab -->
            <div v-show="activeTab === 'system-config'" class="space-y-6">
              <div class="mb-4">
                <h3 class="text-lg font-semibold mb-4">إعدادات العناوين</h3>
              </div>

              <div v-if="loading" class="text-center py-8">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900 dark:border-white"></div>
              </div>

              <div v-else class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <InputLabel for="first_title_ar" value="العنوان الأول (عربي)" />
                    <TextInput
                      id="first_title_ar"
                      v-model="systemConfig.first_title_ar"
                      type="text"
                      class="mt-1 block w-full"
                      placeholder="العنوان الأول بالعربية"
                    />
                  </div>
                  <div>
                    <InputLabel for="first_title_kr" value="العنوان الأول (كردي)" />
                    <TextInput
                      id="first_title_kr"
                      v-model="systemConfig.first_title_kr"
                      type="text"
                      class="mt-1 block w-full"
                      placeholder="العنوان الأول بالكردية"
                    />
                  </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <InputLabel for="second_title_ar" value="العنوان الثاني (عربي)" />
                    <TextInput
                      id="second_title_ar"
                      v-model="systemConfig.second_title_ar"
                      type="text"
                      class="mt-1 block w-full"
                      placeholder="العنوان الثاني بالعربية"
                    />
                  </div>
                  <div>
                    <InputLabel for="second_title_kr" value="العنوان الثاني (كردي)" />
                    <TextInput
                      id="second_title_kr"
                      v-model="systemConfig.second_title_kr"
                      type="text"
                      class="mt-1 block w-full"
                      placeholder="العنوان الثاني بالكردية"
                    />
                  </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <InputLabel for="third_title_ar" value="العنوان الثالث (عربي)" />
                    <TextInput
                      id="third_title_ar"
                      v-model="systemConfig.third_title_ar"
                      type="text"
                      class="mt-1 block w-full"
                      placeholder="العنوان الثالث بالعربية"
                    />
                  </div>
                  <div>
                    <InputLabel for="third_title_kr" value="العنوان الثالث (كردي)" />
                    <TextInput
                      id="third_title_kr"
                      v-model="systemConfig.third_title_kr"
                      type="text"
                      class="mt-1 block w-full"
                      placeholder="العنوان الثالث بالكردية"
                    />
                  </div>
                </div>

                <!-- سعر الصرف -->
                <div class="mt-6 border-t pt-6">
                  <h4 class="text-md font-semibold mb-4">إعدادات العملات</h4>
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                      <InputLabel for="usd_to_aed_rate" value="سعر صرف الدولار مقابل الدرهم الإماراتي (USD → AED)" />
                      <div class="flex items-center gap-2">
                        <span class="text-2xl">💵</span>
                        <TextInput
                          id="usd_to_aed_rate"
                          v-model="systemConfig.usd_to_aed_rate"
                          type="number"
                          step="0.0001"
                          min="0"
                          class="mt-1 block w-full"
                          placeholder="3.6725"
                        />
                        <span class="text-sm text-gray-600 dark:text-gray-400 whitespace-nowrap">درهم</span>
                      </div>
                      <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        مثال: 1 دولار = {{ systemConfig.usd_to_aed_rate }} درهم إماراتي
                      </p>
                    </div>

                    <div>
                      <InputLabel for="usd_to_dinar_rate" value="سعر 100 دولار بالدينار (USD → IQD)" />
                      <div class="flex items-center gap-2">
                        <span class="text-2xl">💰</span>
                        <TextInput
                          id="usd_to_dinar_rate"
                          v-model="systemConfig.usd_to_dinar_rate"
                          type="number"
                          step="0.01"
                          min="0"
                          class="mt-1 block w-full"
                          placeholder="150.00"
                        />
                        <span class="text-sm text-gray-600 dark:text-gray-400 whitespace-nowrap">دينار</span>
                      </div>
                      <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        مثال: 100 دولار = {{ systemConfig.usd_to_dinar_rate }} دينار عراقي
                      </p>
                    </div>
                  </div>
                </div>

                <!-- قالب العقد -->
                <div class="mt-6 border-t pt-6">
                  <InputLabel for="contract_template" value="قالب العقد" class="mb-2" />
                  <select
                    id="contract_template"
                    v-model.number="systemConfig.contract_template"
                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                  >
                    <option :value="1">قالب 1 (الافتراضي)</option>
                    <option :value="2">قالب 2</option>
                  </select>
                  <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    يحدد شكل عقد البيع عند الطباعة.
                  </p>
                </div>

                <!-- عملة العقد -->
                <div class="mt-4">
                  <InputLabel for="contract_currency" value="عملة عرض السعر والدفع في العقد" class="mb-2" />
                  <select
                    id="contract_currency"
                    v-model="systemConfig.contract_currency"
                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                  >
                    <option value="usd">دولار أمريكي ($)</option>
                    <option value="dinar">دينار عراقي (د.ع)</option>
                  </select>
                  <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    عند اختيار الدينار، يُستخدم سعر 100 دولار بالدينار من الإعدادات للتحويل.
                  </p>
                </div>

                <!-- شروط قالب 1 -->
                <div class="mt-6 border-t pt-6">
                  <div class="flex justify-between items-center mb-4">
                    <h4 class="text-md font-semibold">شروط قالب 1</h4>
                    <button
                      @click="addContractTerm"
                      type="button"
                      class="px-4 py-2 bg-green-600 text-white text-sm rounded hover:bg-green-700"
                    >
                      إضافة شرط جديد
                    </button>
                  </div>
                  <div class="space-y-3">
                    <div
                      v-for="(term, index) in contractTermsItems"
                      :key="term.id || index"
                      class="flex gap-2 items-start"
                    >
                      <div class="flex-1">
                        <textarea
                          v-model="term.text"
                          rows="2"
                          class="w-full text-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm px-3 py-2"
                          :placeholder="`الشرط ${index + 1}`"
                        ></textarea>
                      </div>
                      <button
                        @click="removeContractTerm(index)"
                        type="button"
                        class="px-3 py-2 bg-red-600 text-white text-sm rounded hover:bg-red-700 mt-1"
                      >
                        حذف
                      </button>
                    </div>
                    <p v-if="contractTermsItems.length === 0" class="text-sm text-gray-500 text-center py-4">
                      لا توجد شروط. اضغط "إضافة شرط جديد" لإضافة شرط.
                    </p>
                  </div>
                  <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                    💡 شروط قالب العقد الأول. الشرط الأخير (التاريخ والوقت) يُضاف تلقائياً.
                  </p>
                </div>

                <!-- شروط قالب 2 -->
                <div class="mt-6 border-t pt-6">
                  <div class="flex justify-between items-center mb-4">
                    <h4 class="text-md font-semibold">شروط قالب 2</h4>
                    <button
                      @click="addContractTerm2"
                      type="button"
                      class="px-4 py-2 bg-green-600 text-white text-sm rounded hover:bg-green-700"
                    >
                      إضافة شرط جديد
                    </button>
                  </div>
                  <div class="space-y-3">
                    <div
                      v-for="(term, index) in contractTerms2Items"
                      :key="term.id || index"
                      class="flex gap-2 items-start"
                    >
                      <div class="flex-1">
                        <textarea
                          v-model="term.text"
                          rows="2"
                          class="w-full text-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm px-3 py-2"
                          :placeholder="`الشرط ${index + 1}`"
                        ></textarea>
                      </div>
                      <button
                        @click="removeContractTerm2(index)"
                        type="button"
                        class="px-3 py-2 bg-red-600 text-white text-sm rounded hover:bg-red-700 mt-1"
                      >
                        حذف
                      </button>
                    </div>
                    <p v-if="contractTerms2Items.length === 0" class="text-sm text-gray-500 text-center py-4">
                      لا توجد شروط لقالب 2. اضغط "إضافة شرط جديد" لإضافة شرط.
                    </p>
                  </div>
                  <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                    💡 شروط قالب العقد الثاني. إذا كانت فارغة، يُستخدم قالب 1 كبديل.
                  </p>
                </div>

                <div class="mt-6 border-t pt-6">
                  <h4 class="text-md font-semibold mb-4">الأسعار الافتراضية</h4>
                  
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- الأسعار الافتراضية للمبيعات -->
                    <div>
                      <InputLabel for="default_price_s" value="الأسعار الافتراضية للمبيعات" class="mb-2" />
                      <div class="space-y-2 max-h-96 overflow-y-auto border border-gray-200 dark:border-gray-600 rounded p-2">
                        <div
                          v-for="(item, index) in defaultPriceSItems"
                          :key="item.id || index"
                          class="flex gap-2 items-center p-2 bg-gray-50 dark:bg-gray-700 rounded"
                        >
                          <input
                            v-model="item.key"
                            type="text"
                            placeholder="المفتاح"
                            readonly
                            class="flex-1 text-sm border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm px-3 py-2 bg-gray-100"
                          />
                          <input
                            v-model="item.value"
                            type="text"
                            placeholder="القيمة"
                            class="flex-1 text-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm px-3 py-2"
                          />
                        </div>
                        <p v-if="defaultPriceSItems.length === 0" class="text-sm text-gray-500 text-center py-4">
                          لا توجد عناصر
                        </p>
                      </div>
                    </div>

                    <!-- الأسعار الافتراضية للمشتريات -->
                    <div>
                      <InputLabel for="default_price_p" value="الأسعار الافتراضية للمشتريات" class="mb-2" />
                      <div class="space-y-2 max-h-96 overflow-y-auto border border-gray-200 dark:border-gray-600 rounded p-2">
                        <div
                          v-for="(item, index) in defaultPricePItems"
                          :key="item.id || index"
                          class="flex gap-2 items-center p-2 bg-gray-50 dark:bg-gray-700 rounded"
                        >
                          <input
                            v-model="item.key"
                            type="text"
                            placeholder="المفتاح"
                            readonly
                            class="flex-1 text-sm border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm px-3 py-2 bg-gray-100"
                          />
                          <input
                            v-model="item.value"
                            type="text"
                            placeholder="القيمة"
                            class="flex-1 text-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm px-3 py-2"
                          />
                        </div>
                        <p v-if="defaultPricePItems.length === 0" class="text-sm text-gray-500 text-center py-4">
                          لا توجد عناصر
                        </p>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="flex justify-end mt-6">
                  <button
                    @click="saveSystemConfig"
                    :disabled="saving"
                    class="px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
                  >
                    {{ saving ? 'جاري الحفظ...' : 'حفظ الإعدادات' }}
                  </button>
                </div>
              </div>
            </div>

            <!-- Connected Systems Tab -->
            <div v-show="activeTab === 'connected-systems'" class="space-y-6">
              <div class="mb-4 flex justify-between items-center">
                <h3 class="text-lg font-semibold">قائمة الأنظمة المتصلة</h3>
                <button
                  @click="openAddModal"
                  class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
                >
                  إضافة نظام جديد
                </button>
              </div>

              <div class="overflow-x-auto">
                <table class="w-full text-sm text-right text-gray-500 dark:text-gray-400">
                  <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                      <th class="px-4 py-3">الاسم</th>
                      <th class="px-4 py-3">الدومين</th>
                      <th class="px-4 py-3">API Key</th>
                      <th class="px-4 py-3">الحالة</th>
                      <th class="px-4 py-3">تنفيذ</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr
                      v-for="system in systems"
                      :key="system.id"
                      class="bg-white border-b dark:bg-gray-800 dark:border-gray-700"
                    >
                      <td class="px-4 py-3">{{ system.name }}</td>
                      <td class="px-4 py-3">{{ system.domain }}</td>
                      <td class="px-4 py-3">
                        <code class="text-xs bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded">
                          {{ system.api_key.substring(0, 10) }}...
                        </code>
                      </td>
                      <td class="px-4 py-3">
                        <span
                          :class="system.is_active ? 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'"
                          class="px-2 py-1 text-xs font-semibold rounded"
                        >
                          {{ system.is_active ? 'مفعل' : 'معطل' }}
                        </span>
                      </td>
                      <td class="px-4 py-3">
                        <div class="flex gap-2">
                          <button
                            @click="toggleActive(system)"
                            :class="system.is_active ? 'bg-yellow-500 hover:bg-yellow-600' : 'bg-green-500 hover:bg-green-600'"
                            class="px-3 py-1 text-white text-sm rounded"
                          >
                            {{ system.is_active ? 'تعطيل' : 'تفعيل' }}
                          </button>
                          <button
                            @click="openEditModal(system)"
                            class="px-3 py-1 bg-blue-500 text-white text-sm rounded hover:bg-blue-600"
                          >
                            تعديل
                          </button>
                          <button
                            @click="deleteSystem(system)"
                            class="px-3 py-1 bg-red-500 text-white text-sm rounded hover:bg-red-600"
                          >
                            حذف
                          </button>
                        </div>
                      </td>
                    </tr>
                    <tr v-if="systems.length === 0">
                      <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                        لا توجد أنظمة متصلة
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal -->
    <div
      v-if="showModal"
      class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
      @click.self="closeModal"
    >
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
        <div class="mt-3">
          <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
            {{ editingSystem ? 'تعديل النظام' : 'إضافة نظام جديد' }}
          </h3>
          
          <div class="mb-4">
            <InputLabel for="name" value="اسم النظام" />
            <TextInput
              id="name"
              v-model="formData.name"
              type="text"
              class="mt-1 block w-full"
              placeholder="مثال: نظام أربيل"
            />
          </div>

          <div class="mb-4">
            <InputLabel for="domain" value="الدومين" />
            <TextInput
              id="domain"
              v-model="formData.domain"
              type="text"
              class="mt-1 block w-full"
              placeholder="https://system2.example.com"
            />
          </div>

          <div class="mb-4">
            <InputLabel for="api_key" value="API Key" />
            <div class="flex gap-2">
              <TextInput
                id="api_key"
                v-model="formData.api_key"
                type="text"
                class="mt-1 block flex-1"
                placeholder="alaa"
              />
              <button
                @click="testConnection"
                :disabled="testingConnection || !formData.domain || !formData.api_key"
                class="mt-1 px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 disabled:bg-gray-400 disabled:cursor-not-allowed flex items-center gap-2"
                type="button"
              >
                <span v-if="testingConnection" class="inline-block animate-spin rounded-full h-4 w-4 border-b-2 border-white"></span>
                <span>{{ testingConnection ? 'جارٍ التحقق...' : 'التحقق من الاتصال' }}</span>
              </button>
            </div>
          </div>

          <div class="mb-4">
            <label class="flex items-center">
              <input
                v-model="formData.is_active"
                type="checkbox"
                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
              />
              <span class="mr-2 text-sm text-gray-600 dark:text-gray-400">نشط</span>
            </label>
          </div>

          <div class="flex justify-end gap-2">
            <button
              @click="closeModal"
              class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600"
            >
              إلغاء
            </button>
            <button
              @click="saveSystem"
              class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
            >
              حفظ
            </button>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

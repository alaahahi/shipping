<script setup>
import { ref, watch, onMounted } from 'vue';
import axios from 'axios';
import { useToast } from "vue-toastification";
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { ModelListSelect } from "vue-search-select";
import "vue-search-select/dist/VueSearchSelect.css";
import * as XLSX from 'xlsx';

const props = defineProps({
  tripId: {
    type: [String, Number],
    required: true,
  },
});

const emit = defineEmits(['uploaded']);

const toast = useToast();

const selectedCompanyId = ref(null);
const selectedCompany = ref(null);
const isSearching = ref(false);
const companyOptions = ref([]);
const file = ref(null);
const isDragging = ref(false);
const isUploading = ref(false);
const uploadProgress = ref(0);
const showCreateCompanyForm = ref(false);
const companyPhone = ref('');
const isCreatingCompany = ref(false);
const newCompanyName = ref('');
const previewData = ref([]);
const previewHeaders = ref([]);
const showPreview = ref(false);
const isLoadingPreview = ref(false);
const snoRow = ref(null);

// جلب جميع الشركات للـ select
const loadAllCompanies = async () => {
  isSearching.value = true;
  try {
    const response = await axios.get('/trips/search-companies', {
      params: { q: '' }, // جلب جميع الشركات
    });
    const allCompanies = Array.isArray(response.data) ? response.data : [];
    
    // تحويل البيانات إلى تنسيق ModelListSelect
    companyOptions.value = allCompanies.map(company => ({
      value: company.id,
      text: company.name,
      phone: company.phone,
    }));
  } catch (error) {
    console.error(error);
    companyOptions.value = [];
    toast.error('حدث خطأ أثناء جلب الشركات');
  } finally {
    isSearching.value = false;
  }
};

// البحث في الشركات (لـ ModelListSelect)
const searchCompaniesForSelect = async (searchText) => {
  if (!searchText || searchText.trim().length < 1) {
    await loadAllCompanies();
    return;
  }

  isSearching.value = true;
  try {
    const response = await axios.get('/trips/search-companies', {
      params: { q: searchText.trim() },
    });
    const foundCompanies = Array.isArray(response.data) ? response.data : [];
    
    companyOptions.value = foundCompanies.map(company => ({
      value: company.id,
      text: company.name,
      phone: company.phone,
    }));
  } catch (error) {
    console.error(error);
    companyOptions.value = [];
  } finally {
    isSearching.value = false;
  }
};

// اختيار شركة من الـ select
watch(selectedCompanyId, (newId) => {
  if (newId) {
    const company = companyOptions.value.find(c => c.value === newId);
    if (company) {
      selectedCompany.value = {
        id: company.value,
        name: company.text,
        phone: company.phone,
      };
    }
  } else {
    selectedCompany.value = null;
  }
});

// معالجة اختيار الشركة من ModelListSelect
const onCompanySelect = (item) => {
  if (item) {
    selectedCompany.value = {
      id: item.value || item.id,
      name: item.text || item.name,
      phone: item.phone,
    };
  } else {
    selectedCompany.value = null;
  }
};

// تحميل الشركات عند تحميل المكون
onMounted(() => {
  loadAllCompanies();
});

// رفع الملف
const handleFileSelect = (event) => {
  const selectedFile = event.target.files[0];
  if (selectedFile) {
    validateAndSetFile(selectedFile);
  }
};

// Drag & Drop Handlers
const handleDragEnter = (e) => {
  e.preventDefault();
  e.stopPropagation();
  isDragging.value = true;
};

const handleDragLeave = (e) => {
  e.preventDefault();
  e.stopPropagation();
  isDragging.value = false;
};

const handleDragOver = (e) => {
  e.preventDefault();
  e.stopPropagation();
};

const handleDrop = (e) => {
  e.preventDefault();
  e.stopPropagation();
  isDragging.value = false;

  const droppedFiles = e.dataTransfer.files;
  if (droppedFiles && droppedFiles.length > 0) {
    validateAndSetFile(droppedFiles[0]);
  }
};

// التحقق من صحة الملف
const validateAndSetFile = (selectedFile) => {
  const validExtensions = ['.xlsx', '.xls'];
  const fileExtension = selectedFile.name.substring(selectedFile.name.lastIndexOf('.')).toLowerCase();
  
  if (!validExtensions.includes(fileExtension)) {
    toast.error('يرجى اختيار ملف Excel (.xlsx أو .xls)');
    return;
  }

  const maxSize = 10 * 1024 * 1024; // 10MB
  if (selectedFile.size > maxSize) {
    toast.error('حجم الملف كبير جداً. الحد الأقصى 10MB');
    return;
  }

  file.value = selectedFile;
  previewData.value = [];
  previewHeaders.value = [];
  showPreview.value = false;
  snoRow.value = null;
  toast.success('تم اختيار الملف بنجاح');
  
  // تحميل معاينة تلقائياً
  previewExcel(selectedFile);
};

// معاينة ملف Excel
const previewExcel = async (excelFile) => {
  if (!excelFile) return;
  
  isLoadingPreview.value = true;
  showPreview.value = false;
  
  try {
    const reader = new FileReader();
    
    reader.onload = (e) => {
      try {
        const data = new Uint8Array(e.target.result);
        const workbook = XLSX.read(data, { type: 'array' });
        const firstSheetName = workbook.SheetNames[0];
        const worksheet = workbook.Sheets[firstSheetName];
        
        // البحث عن صف S.NO
        const snoRowIndex = findSnoRow(worksheet);
        snoRow.value = snoRowIndex;
        
        // تحويل البيانات إلى JSON - استخدام snoRowIndex كصف الرأس (1-based)
        // نستخدم raw option للحصول على جميع البيانات
        const rawData = XLSX.utils.sheet_to_json(worksheet, { 
          header: 1, // استخدام array format
          defval: '', // القيمة الافتراضية للخلايا الفارغة
          raw: false, // الحصول على القيم كـ strings
        });
        
        console.log('📊 Raw Excel data:', rawData.slice(0, 10)); // Log first 10 rows for debug
        
        // البحث عن صف S.NO والحصول على البيانات من بعده
        if (snoRowIndex > 0 && rawData.length >= snoRowIndex) {
          // صف الرأس (S.NO row)
          const headerRow = rawData[snoRowIndex - 1]; // snoRowIndex is 1-based
          previewHeaders.value = headerRow.map(h => String(h || '').trim()).filter(h => h);
          
          console.log('📋 Headers found:', previewHeaders.value);
          console.log('📋 S.NO row index:', snoRowIndex);
          
          // البيانات تبدأ من الصف بعد S.NO
          const dataRows = rawData.slice(snoRowIndex); // من بعد صف S.NO
          
          // تحويل البيانات إلى objects
          previewData.value = dataRows
            .map(row => {
              const rowObj = {};
              previewHeaders.value.forEach((header, index) => {
                rowObj[header] = String(row[index] || '').trim();
              });
              return rowObj;
            })
            .filter(row => {
              // تخطي الصفوف الفارغة
              const values = Object.values(row);
              const hasData = values.some(val => {
                const cleaned = String(val || '').trim().replace(/^["']|["']$/g, '');
                return cleaned.length > 0;
              });
              return hasData;
            })
            .slice(0, 100); // عرض أول 100 صف للمعاينة (بدلاً من 20)
          
          console.log('✅ Preview data prepared:', previewData.value.length, 'rows');
        } else {
          console.warn('⚠️ S.NO row not found or invalid, using default parsing');
          // Fallback إلى الطريقة القديمة
          const jsonData = XLSX.utils.sheet_to_json(worksheet, { 
            defval: '',
          });
          if (jsonData.length > 0) {
            previewHeaders.value = Object.keys(jsonData[0]);
            previewData.value = jsonData
              .filter(row => {
                const values = Object.values(row);
                const hasData = values.some(val => {
                  const cleaned = String(val || '').trim().replace(/^["']|["']$/g, '');
                  return cleaned.length > 0;
                });
                return hasData;
              })
              .slice(0, 100);
          }
        }
        
        showPreview.value = true;
        toast.success(`تم تحميل المعاينة (${previewData.value.length} صف)`);
      } catch (error) {
        console.error('Error parsing Excel:', error);
        toast.error('حدث خطأ أثناء قراءة الملف');
      } finally {
        isLoadingPreview.value = false;
      }
    };
    
    reader.onerror = () => {
      toast.error('حدث خطأ أثناء قراءة الملف');
      isLoadingPreview.value = false;
    };
    
    reader.readAsArrayBuffer(excelFile);
  } catch (error) {
    console.error('Error reading file:', error);
    toast.error('حدث خطأ أثناء قراءة الملف');
    isLoadingPreview.value = false;
  }
};

// البحث عن صف S.NO
const findSnoRow = (worksheet) => {
  // البحث في أول 30 صف
  for (let row = 1; row <= 30; row++) {
    // البحث في أول 10 أعمدة
    for (let col = 0; col < 10; col++) {
      const cellAddress = XLSX.utils.encode_cell({ r: row - 1, c: col });
      const cell = worksheet[cellAddress];
      
      if (cell && cell.v) {
        const cellValue = String(cell.v).toUpperCase().trim();
        
        // البحث عن S.NO أو S NO أو S/NO
        if (cellValue.match(/^S[.\s\/]*NO[.\s:]*$/) || 
            cellValue === 'S.NO' || 
            cellValue === 'S NO' ||
            cellValue === 'S/NO' ||
            cellValue === 'S.NO.' ||
            cellValue === 'S.NO:') {
          return row; // إرجاع رقم الصف (1-based)
        }
      }
    }
  }
  
  // إذا لم يتم العثور، استخدام الصف 10 كافتراضي
  return 10;
};

// رفع الملف
const uploadFile = async () => {
  if (!selectedCompany.value) {
    toast.error('يرجى اختيار شركة');
    return;
  }

  if (!file.value) {
    toast.error('يرجى اختيار ملف Excel');
    return;
  }

  isUploading.value = true;
  uploadProgress.value = 0;

  try {
    const formData = new FormData();
    formData.append('file', file.value);
    formData.append('company_id', selectedCompany.value.id);

    const response = await axios.post(`/trips/${props.tripId}/upload-excel`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
      onUploadProgress: (progressEvent) => {
        if (progressEvent.total) {
          uploadProgress.value = Math.round((progressEvent.loaded * 100) / progressEvent.total);
        }
      },
    });

    if (response.data.success) {
      toast.success(response.data.message || 'تم استيراد الملف بنجاح');
      emit('uploaded', response.data.trip_company);
      // Reset form
      file.value = null;
      selectedCompany.value = null;
      uploadProgress.value = 0;
      // إعادة تحميل قائمة الشركات
      loadAllCompanies();
    } else {
      toast.error(response.data.message || 'حدث خطأ أثناء الاستيراد');
    }
  } catch (error) {
    console.error(error);
    const errorMessage = error.response?.data?.message || 'حدث خطأ أثناء رفع الملف';
    toast.error(errorMessage);
  } finally {
    isUploading.value = false;
    uploadProgress.value = 0;
  }
};

// مسح الملف
const clearFile = () => {
  file.value = null;
  previewData.value = [];
  previewHeaders.value = [];
  showPreview.value = false;
  snoRow.value = null;
};

// إنشاء شركة جديدة من القسم المنفصل
const createNewCompany = async () => {
  if (!newCompanyName.value || newCompanyName.value.length < 2) {
    toast.error('يرجى إدخال اسم الشركة (حرفين على الأقل)');
    return;
  }

  isCreatingCompany.value = true;
  try {
    const response = await axios.post('/trips/create-company', {
      name: newCompanyName.value.trim(),
      phone: companyPhone.value.trim() || null,
    });

    if (response.data.success) {
      toast.success(response.data.message || 'تم إنشاء الشركة بنجاح');
      
      // إضافة الشركة الجديدة إلى القائمة
      const newCompany = {
        value: response.data.company.id,
        text: response.data.company.name,
        phone: response.data.company.phone,
      };
      companyOptions.value.unshift(newCompany);
      
      // اختيار الشركة الجديدة تلقائياً
      selectedCompanyId.value = response.data.company.id;
      selectedCompany.value = response.data.company;
      showCreateCompanyForm.value = false;
      companyPhone.value = '';
      newCompanyName.value = '';
    } else {
      toast.error(response.data.message || 'حدث خطأ أثناء إنشاء الشركة');
      // إذا كانت الشركة موجودة، اختيارها
      if (response.data.company) {
        selectedCompanyId.value = response.data.company.id;
        selectedCompany.value = response.data.company;
        showCreateCompanyForm.value = false;
        newCompanyName.value = '';
      }
    }
  } catch (error) {
    console.error(error);
    const errorMessage = error.response?.data?.message || 'حدث خطأ أثناء إنشاء الشركة';
    toast.error(errorMessage);
    // إذا كانت الشركة موجودة في الاستجابة، اختيارها
    if (error.response?.data?.company) {
      selectedCompanyId.value = error.response.data.company.id;
      selectedCompany.value = error.response.data.company;
      showCreateCompanyForm.value = false;
      newCompanyName.value = '';
    }
  } finally {
    isCreatingCompany.value = false;
  }
};
</script>

<template>
  <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 sm:p-6">
    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">رفع ملف Excel للشركة</h3>

    <!-- Company Select with Search -->
    <div class="mb-6">
      <InputLabel for="company_select" value="البحث عن الشركة (LLC)" />
      <div class="mt-1">
        <ModelListSelect
          v-model="selectedCompanyId"
          :list="companyOptions"
          optionValue="value"
          optionText="text"
          :placeholder="isSearching ? 'جاري البحث...' : 'ابحث واختر شركة...'"
          :disabled="isUploading"
          @searchchange="searchCompaniesForSelect"
        >
        </ModelListSelect>
      </div>

      <!-- Selected Company Display -->
      <div v-if="selectedCompany" class="mt-2 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-blue-900 dark:text-blue-200">{{ selectedCompany.name }}</p>
            <p v-if="selectedCompany.phone" class="text-xs text-blue-700 dark:text-blue-300 mt-1">
              {{ selectedCompany.phone }}
            </p>
          </div>
          <button
            @click="selectedCompany = null; selectedCompanyId = null;"
            class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
            :disabled="isUploading"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </div>
    </div>

    <!-- إدخال شركة جديدة - قسم منفصل -->
    <div class="mb-6">
      <div class="flex items-center justify-between mb-3">
        <InputLabel value="أو إدخال شركة جديدة" />
        <button
          @click="showCreateCompanyForm = !showCreateCompanyForm"
          class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 flex items-center"
          :disabled="isUploading"
        >
          <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          {{ showCreateCompanyForm ? 'إخفاء' : 'إظهار' }}
        </button>
      </div>

      <!-- Create Company Form -->
      <div
        v-if="showCreateCompanyForm"
        class="p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg border border-yellow-200 dark:border-yellow-800"
      >
        <div class="flex items-center mb-3">
          <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <h4 class="text-sm font-semibold text-yellow-900 dark:text-yellow-200">إنشاء شركة جديدة</h4>
        </div>
        
        <div class="space-y-3">
          <div>
            <InputLabel for="new_company_name" value="اسم الشركة *" />
            <TextInput
              id="new_company_name"
              v-model="newCompanyName"
              type="text"
              placeholder="مثال: W M I SHIPPING LLC"
              class="mt-1 block w-full"
              required
              :disabled="isCreatingCompany"
            />
          </div>
          
          <div>
            <InputLabel for="new_company_phone" value="رقم الهاتف (اختياري)" />
            <TextInput
              id="new_company_phone"
              v-model="companyPhone"
              type="text"
              placeholder="مثال: 07501234567"
              class="mt-1 block w-full"
              :disabled="isCreatingCompany"
            />
          </div>

          <div class="flex justify-end gap-2 pt-2">
            <button
              @click="showCreateCompanyForm = false; companyPhone = ''; newCompanyName = '';"
              class="px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-600"
              :disabled="isCreatingCompany"
            >
              إلغاء
            </button>
            <button
              @click="createNewCompany"
              :disabled="isCreatingCompany || !newCompanyName || newCompanyName.length < 2"
              class="px-4 py-2 text-sm font-medium text-white bg-yellow-600 border border-transparent rounded-md hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <span v-if="isCreatingCompany" class="flex items-center">
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                جاري الإنشاء...
              </span>
              <span v-else>إنشاء الشركة</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- File Upload - Drag & Drop -->
    <div class="mb-6">
      <InputLabel value="اختر ملف Excel" />
      <div
        @dragenter="handleDragEnter"
        @dragleave="handleDragLeave"
        @dragover="handleDragOver"
        @drop="handleDrop"
        :class="[
          'mt-2 border-2 border-dashed rounded-lg p-6 sm:p-12 text-center transition-colors',
          isDragging
            ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20'
            : 'border-gray-300 dark:border-gray-600 hover:border-gray-400 dark:hover:border-gray-500',
          isUploading ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'
        ]"
      >
        <input
          type="file"
          accept=".xlsx,.xls"
          @change="handleFileSelect"
          class="hidden"
          id="file-upload"
          :disabled="isUploading"
        />
        <label for="file-upload" class="cursor-pointer">
          <svg
            class="mx-auto h-12 w-12 text-gray-400"
            stroke="currentColor"
            fill="none"
            viewBox="0 0 48 48"
            aria-hidden="true"
          >
            <path
              d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"
              stroke-width="2"
              stroke-linecap="round"
              stroke-linejoin="round"
            />
          </svg>
          <div class="mt-4">
            <p class="text-sm text-gray-600 dark:text-gray-400">
              <span class="font-medium text-blue-600 hover:text-blue-500 dark:text-blue-400">اختر ملف</span>
              أو اسحب الملف هنا
            </p>
            <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">
              Excel (.xlsx, .xls) حتى 10MB
            </p>
          </div>
        </label>

        <!-- Selected File -->
        <div v-if="file" class="mt-4 p-3 bg-green-50 dark:bg-green-900/20 rounded-lg">
          <div class="flex items-center justify-between">
            <div class="flex items-center">
              <svg class="w-5 h-5 text-green-600 dark:text-green-400 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
              <div>
                <p class="text-sm font-medium text-green-900 dark:text-green-200">{{ file.name }}</p>
                <p class="text-xs text-green-700 dark:text-green-300">
                  {{ (file.size / 1024 / 1024).toFixed(2) }} MB
                  <span v-if="snoRow" class="mr-2">• تم العثور على S.NO في الصف {{ snoRow }}</span>
                </p>
              </div>
            </div>
            <div class="flex items-center gap-2">
              <button
                @click="previewExcel(file)"
                class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
                :disabled="isUploading || isLoadingPreview"
                title="معاينة الملف"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
              </button>
              <button
                @click="clearFile"
                class="text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-300"
                :disabled="isUploading"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
          </div>
        </div>

        <!-- Excel Preview -->
        <div v-if="showPreview && previewData.length > 0" class="mt-4 p-4 bg-gray-50 dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700">
          <div class="flex items-center justify-between mb-3">
            <h4 class="text-sm font-semibold text-gray-900 dark:text-white">
              معاينة الملف ({{ previewData.length }} صف)
            </h4>
            <button
              @click="showPreview = false"
              class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
          
          <div class="overflow-x-auto max-h-96 overflow-y-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
              <thead class="bg-gray-100 dark:bg-gray-800 sticky top-0">
                <tr>
                  <th
                    v-for="header in previewHeaders"
                    :key="header"
                    class="px-3 py-2 text-right text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider border border-gray-200 dark:border-gray-600"
                  >
                    {{ header }}
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                <tr v-for="(row, index) in previewData" :key="index" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                  <td
                    v-for="header in previewHeaders"
                    :key="header"
                    class="px-3 py-2 whitespace-nowrap text-xs text-gray-900 dark:text-gray-100 border border-gray-200 dark:border-gray-600"
                  >
                    {{ row[header] || '-' }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Loading Preview -->
        <div v-if="isLoadingPreview" class="mt-4 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
          <div class="flex items-center justify-center">
            <svg class="animate-spin h-5 w-5 text-blue-600 dark:text-blue-400 ml-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="text-sm text-blue-600 dark:text-blue-400">جاري تحميل المعاينة...</span>
          </div>
        </div>

        <!-- Upload Progress -->
        <div v-if="isUploading" class="mt-4">
          <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
            <div
              class="bg-blue-600 h-2.5 rounded-full transition-all duration-300"
              :style="{ width: uploadProgress + '%' }"
            ></div>
          </div>
          <p class="text-xs text-gray-600 dark:text-gray-400 mt-2">
            جاري الرفع... {{ uploadProgress }}%
          </p>
        </div>
      </div>
    </div>

    <!-- Upload Button -->
    <button
      @click="uploadFile"
      :disabled="!selectedCompany || !file || isUploading"
      class="w-full sm:w-auto inline-flex justify-center items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 disabled:cursor-not-allowed transition ease-in-out duration-150"
    >
      <svg v-if="isUploading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
      </svg>
      {{ isUploading ? 'جاري الرفع...' : 'رفع الملف' }}
    </button>
  </div>
</template>

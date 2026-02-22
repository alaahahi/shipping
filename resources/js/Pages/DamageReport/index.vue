<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link } from "@inertiajs/inertia-vue3";
import TextInput from "@/Components/TextInput.vue";
import print from "@/Components/icon/print.vue";
import edit from "@/Components/icon/edit.vue";
import trash from "@/Components/icon/trash.vue";
import ModalShowDamageReport from "@/Components/ModalShowDamageReport.vue";
import ModalDelCar from "@/Components/ModalDelCar.vue";
import InfiniteLoading from "v3-infinite-loading";
import "v3-infinite-loading/lib/style.css";
import debounce from "lodash/debounce";
import { useToast } from "vue-toastification";
import axios from "axios";
import { ref } from "vue";
import { useI18n } from "vue-i18n";

const { t } = useI18n();
const props = defineProps({
  owner_id: Number,
});

const toast = useToast();
let showModalDamageReport = ref(false);
let showModalDelReport = ref(false);
let formDamage = ref({
  driver_name: '',
  cmr_number: '',
  cars_count: 0,
  total_damage: 0,
  cars_info: [],
  created: getTodayDate(),
});
let formData = ref({});
let reports = ref([]);
let from = ref('');
let to = ref('');
let q = ref('');
let resetData = ref(0);
let page = 1;
let allReports = ref(0);

const refresh = () => {
  page = 1;
  reports.value = [];
  resetData.value += 1;
};

const getResultsReports = async ($state) => {
  try {
    const params = {
      limit: 100,
      page: page,
    };
    
    if (q.value) {
      params.q = q.value;
    }
    if (from.value) {
      params.from = from.value;
    }
    if (to.value) {
      params.to = to.value;
    }
    
    console.log('Request params:', params);
    
    const response = await axios.get(`/api/getIndexDamageReport`, { params });

    console.log('API Response:', response.data);
    console.log('Response status:', response.status);
    
    const json = response.data;
    
    if (json.error) {
      console.error('API Error:', json.error);
      toast.error("حدث خطأ: " + json.error, {
        timeout: 3000,
        position: "bottom-right",
        rtl: true,
      });
      $state.complete();
      return;
    }
    
    allReports.value = json.total || 0;
    console.log('Total reports:', allReports.value);
    console.log('Data array length:', json.data?.length || 0);

    if (json.data && Array.isArray(json.data) && json.data.length > 0) {
      console.log('Adding reports:', json.data.length);
      if (json.data.length < 100) {
        reports.value.push(...json.data);
        $state.complete();
      } else {
        reports.value.push(...json.data);
        $state.loaded();
      }
      page++;
    } else {
      console.log('No data found in response');
      console.log('Response structure:', Object.keys(json));
      $state.complete();
    }
  } catch (error) {
    console.error('Error fetching reports:', error);
    console.error('Error response:', error.response?.data);
    toast.error("حدث خطأ أثناء جلب البيانات: " + (error.response?.data?.error || error.message), {
      timeout: 3000,
      position: "bottom-right",
      rtl: true,
    });
    $state.complete();
  }
};

function getTodayDate() {
  const today = new Date();
  const year = today.getFullYear();
  const month = String(today.getMonth() + 1).padStart(2, "0");
  const day = String(today.getDate()).padStart(2, "0");
  return `${year}-${month}-${day}`;
}

function getFirstDayOfMonth() {
  const today = new Date();
  const year = today.getFullYear();
  const month = String(today.getMonth() + 1).padStart(2, "0");
  const firstDayOfMonth = "01";
  return `${year}-${month}-${firstDayOfMonth}`;
}

function formatDate(dateString) {
  if (!dateString) return '';
  
  // إذا كان التاريخ بصيغة ISO (2025-11-30T21:00:00.000000Z)
  if (dateString.includes('T')) {
    return dateString.split('T')[0];
  }
  
  // إذا كان التاريخ بصيغة Y-m-d
  return dateString;
}

function openModalDamageReport(report = null) {
  if (report) {
    formDamage.value = {
      id: report.id,
      driver_name: report.driver_name || '',
      cmr_number: report.cmr_number || '',
      cars_count: report.cars_count || 0,
      total_damage: report.total_damage || 0,
      cars_info: report.cars_info || [],
      created: report.created || getTodayDate(),
    };
  } else {
    formDamage.value = {
      driver_name: '',
      cmr_number: '',
      cars_count: 0,
      total_damage: 0,
      cars_info: [],
      created: getTodayDate(),
    };
  }
  showModalDamageReport.value = true;
}

function confirmSaveDamageReport(v) {
  const url = v.id ? '/api/updateDamageReport' : '/api/storeDamageReport';
  const method = v.id ? 'post' : 'post';
  
  axios[method](url, v)
    .then((response) => {
      showModalDamageReport.value = false;
      toast.success(v.id ? "تم التعديل بنجاح" : "تم الحفظ بنجاح", {
        timeout: 2000,
        position: "bottom-right",
        rtl: true,
      });
      
      // التحويل للطباعة مباشرة بعد الحفظ
      const reportId = response.data.id || response.data.data?.id;
      if (reportId) {
        setTimeout(() => {
          window.open(`/api/makeDamageReportPdf?doc_id=${reportId}`, '_blank');
        }, 500);
      }
      
      refresh();
    })
    .catch((error) => {
      showModalDamageReport.value = false;
      toast.error("حدث خطأ أثناء الحفظ", {
        timeout: 2000,
        position: "bottom-right",
        rtl: true,
      });
    });
}

function openModalDelReport(v) {
  formData.value = v;
  showModalDelReport.value = true;
}

function confirmDelReport(v) {
  axios
    .post("/api/deleteDamageReport", v)
    .then((response) => {
      showModalDelReport.value = false;
      toast.success("تم حذف التقرير بنجاح", {
        timeout: 3000,
        position: "bottom-right",
        rtl: true,
      });
      refresh();
    })
    .catch((error) => {
      console.error(error);
    });
}

function printReport(report) {
  window.open(`/api/makeDamageReportPdf?doc_id=${report.id}`, '_blank');
}

function editReport(report) {
  window.location = `/damage_report/${report.id}/edit`;
}

const debouncedRefresh = debounce(refresh, 500);
</script>

<template>
  <Head title="تقارير الضرر" />

  <ModalShowDamageReport
    :show="showModalDamageReport"
    :formDamage="formDamage"
    @save="confirmSaveDamageReport($event)"
    @close="showModalDamageReport = false"
  />

  <ModalDelCar
    :show="showModalDelReport"
    :formData="formData"
    @a="confirmDelReport($event)"
    @close="showModalDelReport = false"
  >
    <template #header>
      <h2 class="my-5 dark:text-white text-center">هل متأكد من حذف التقرير؟</h2>
    </template>
  </ModalDelCar>

  <AuthenticatedLayout>
    <div class="py-2">
      <div class="max-w-9xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm">
          <div class="p-6 dark:bg-gray-900">
            <div class="flex flex-col">
              <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-3 lg:gap-3 items-end">
                <div class="md:col-span-2">
                  <label class="block mb-1 text-sm font-medium dark:text-gray-200">بحث (اسم السائق، رقم CMR، رقم الشاصي)</label>
                  <form class="flex items-center max-w-5xl">
                    <div class="relative w-full">
                      <input
                        type="text"
                        v-model="q"
                        @input="debouncedRefresh"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                        placeholder="بحث..."
                      />
                    </div>
                  </form>
                </div>
                <div>
                  <label class="block mb-1 text-sm font-medium dark:text-gray-200">من تاريخ</label>
                  <TextInput
                    type="date"
                    v-model="from"
                    @input="debouncedRefresh"
                    class="block w-full"
                  />
                </div>
                <div>
                  <label class="block mb-1 text-sm font-medium dark:text-gray-200">إلى تاريخ</label>
                  <TextInput
                    type="date"
                    v-model="to"
                    @input="debouncedRefresh"
                    class="block w-full"
                  />
                </div>
                <div class="flex gap-2">
                  <button
                    @click="openModalDamageReport()"
                    class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 whitespace-nowrap"
                  >
                    تقرير جديد
                  </button>
                  <Link
                    :href="route('dashboard')"
                    class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600 whitespace-nowrap text-center"
                  >
                    العودة
                  </Link>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="py-2">
      <div class="max-w-9xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm">
          <div class="p-6 dark:bg-gray-900">
            <div class="overflow-x-auto">
              <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                  <tr>
                    <th scope="col" class="px-3 py-2 sm:px-4 sm:py-2">رقم</th>
                    <th scope="col" class="px-3 py-2 sm:px-4 sm:py-2">التاريخ</th>
                    <th scope="col" class="px-3 py-2 sm:px-4 sm:py-2">اسم السائق</th>
                    <th scope="col" class="px-3 py-2 sm:px-4 sm:py-2">رقم CMR</th>
                    <th scope="col" class="px-3 py-2 sm:px-4 sm:py-2">عدد السيارات</th>
                    <th scope="col" class="px-3 py-2 sm:px-4 sm:py-2">مجموع الضرر</th>
                    <th scope="col" class="px-3 py-2 sm:px-4 sm:py-2" style="width: 240px;">الإجراءات</th>
                  </tr>
                </thead>
                <tbody>
                  <tr
                    v-for="report in reports"
                    :key="report.id"
                    class="bg-white border-b dark:bg-gray-900 dark:border-gray-900 hover:bg-gray-50 dark:hover:bg-gray-600"
                  >
                    <td class="px-3 py-2 sm:px-4 sm:py-2 text-center">{{ report.id }}</td>
                    <td class="px-3 py-2 sm:px-4 sm:py-2 text-center">{{ formatDate(report.created) }}</td>
                    <td class="px-3 py-2 sm:px-4 sm:py-2 text-center">{{ report.driver_name }}</td>
                    <td class="px-3 py-2 sm:px-4 sm:py-2 text-center">{{ report.cmr_number }}</td>
                    <td class="px-3 py-2 sm:px-4 sm:py-2 text-center">{{ report.cars_count }}</td>
                    <td class="px-3 py-2 sm:px-4 sm:py-2 text-center">{{ report.total_damage }}$</td>
                    <td class="px-3 py-2 sm:px-4 sm:py-2 text-center">
                      <button
                        @click="printReport(report)"
                        class="px-2 py-1 mx-1 bg-blue-500 text-white rounded hover:bg-blue-600"
                        title="طباعة"
                      >
                        <print class="w-4 h-4" />
                      </button>
                      <button
                        v-if="$page.props.auth.user.type_id == 1"
                        @click="editReport(report)"
                        class="px-2 py-1 mx-1 bg-green-500 text-white rounded hover:bg-green-600"
                        title="تعديل"
                      >
                        <edit class="w-4 h-4" />
                      </button>
                      <button
                        @click="openModalDelReport(report)"
                        class="px-2 py-1 mx-1 bg-red-500 text-white rounded hover:bg-red-600"
                        title="حذف"
                      >
                        <trash class="w-4 h-4" />
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
              <InfiniteLoading :key="resetData" @infinite="getResultsReports" :reset="resetData">
                <template #spinner>
                  <div class="text-center py-4">جاري التحميل...</div>
                </template>
                <template #complete>
                  <div class="text-center py-4">تم تحميل جميع البيانات</div>
                </template>
                <template #error>
                  <div class="text-center py-4 text-red-500">حدث خطأ</div>
                </template>
              </InfiniteLoading>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>


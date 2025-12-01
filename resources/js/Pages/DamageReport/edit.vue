<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link } from "@inertiajs/inertia-vue3";
import ModalShowDamageReport from "@/Components/ModalShowDamageReport.vue";
import { useToast } from "vue-toastification";
import axios from "axios";
import { ref, onMounted } from "vue";

const props = defineProps({
  data: Object,
  owner_id: Number,
});

const toast = useToast();
let showModalDamageReport = ref(false);
let formDamage = ref({
  driver_name: '',
  cmr_number: '',
  cars_count: 0,
  total_damage: 0,
  cars_info: [],
  created: getTodayDate(),
});

function getTodayDate() {
  const today = new Date();
  const year = today.getFullYear();
  const month = String(today.getMonth() + 1).padStart(2, "0");
  const day = String(today.getDate()).padStart(2, "0");
  return `${year}-${month}-${day}`;
}

onMounted(() => {
  if (props.data) {
    formDamage.value = {
      id: props.data.id,
      driver_name: props.data.driver_name || '',
      cmr_number: props.data.cmr_number || '',
      cars_count: props.data.cars_count || 0,
      total_damage: props.data.total_damage || 0,
      cars_info: props.data.cars_info || [],
      created: props.data.created || getTodayDate(),
    };
    showModalDamageReport.value = true;
  }
});

function confirmSaveDamageReport(v) {
  axios.post('/api/updateDamageReport', v)
    .then((response) => {
      showModalDamageReport.value = false;
      toast.success("تم التعديل بنجاح", {
        timeout: 2000,
        position: "bottom-right",
        rtl: true,
      });
      setTimeout(() => {
        window.location = '/damage_report';
      }, 1000);
    })
    .catch((error) => {
      showModalDamageReport.value = false;
      toast.error("حدث خطأ أثناء التعديل", {
        timeout: 2000,
        position: "bottom-right",
        rtl: true,
      });
    });
}
</script>

<template>
  <Head title="تعديل تقرير الضرر" />

  <AuthenticatedLayout>
    <div class="py-2">
      <div class="max-w-9xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm">
          <div class="p-6 dark:bg-gray-900">
            <div class="flex justify-between items-center">
              <h2 class="text-xl font-bold dark:text-white">تعديل تقرير الضرر</h2>
              <Link
                :href="route('damage_report.index')"
                class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600"
              >
                العودة
              </Link>
            </div>
          </div>
        </div>
      </div>
    </div>

    <ModalShowDamageReport
      :show="showModalDamageReport"
      :formDamage="formDamage"
      @save="confirmSaveDamageReport($event)"
      @close="showModalDamageReport = false"
    />
  </AuthenticatedLayout>
</template>


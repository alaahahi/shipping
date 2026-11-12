<script setup>
import { inject, ref, watch } from "vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import ModalShowDamageReport from "@/Components/ModalShowDamageReport.vue";
import { useToast } from "vue-toastification";
import { TailwindPagination } from "laravel-vue-pagination";
import { useI18n } from "vue-i18n";

const { t } = useI18n();
const iranAxios = inject("iranAxios");
const toast = useToast();

const reports = ref({});
const q = ref("");
const from = ref("");
const to = ref("");
const loading = ref(false);
const showModal = ref(false);
const formDamage = ref({
  driver_name: "",
  cmr_number: "",
  cars_count: 0,
  total_damage: 0,
  cars_info: [],
  created: new Date().toISOString().slice(0, 10),
});

const fetchReports = async (page = 1) => {
  loading.value = true;
  try {
    const response = await iranAxios.get("/api/iran-damage-reports", {
      params: {
        page,
        q: q.value,
        from: from.value,
        to: to.value,
        limit: 25,
      },
    });
    reports.value = response.data;
  } catch (error) {
    console.error(error);
    toast.error(t("iran_toast_load_damage_fail"));
  } finally {
    loading.value = false;
  }
};

const openNew = () => {
  formDamage.value = {
    driver_name: "",
    cmr_number: "",
    cars_count: 0,
    total_damage: 0,
    cars_info: [],
    created: new Date().toISOString().slice(0, 10),
  };
  showModal.value = true;
};

const saveReport = async (payload) => {
  try {
    await iranAxios.post("/api/iran-damage-reports", payload);
    toast.success(t("iran_toast_damage_saved"));
    showModal.value = false;
    fetchReports();
  } catch (error) {
    toast.error(error.response?.data?.message || t("iran_toast_damage_save_fail"));
  }
};

const deleteReport = async (report) => {
  if (!confirm(t("iran_confirm_delete_damage", { id: report.id }))) return;
  try {
    await iranAxios.post("/api/iran-damage-reports/delete", { id: report.id });
    toast.success(t("iran_toast_damage_deleted"));
    fetchReports();
  } catch (error) {
    toast.error(error.response?.data?.message || t("iran_toast_damage_delete_fail"));
  }
};

const printReport = (report) => {
  window.open(`/api/iran-damage-reports/pdf?doc_id=${report.id}`, "_blank");
};

watch([q, from, to], () => fetchReports(), { immediate: true });
</script>

<template>
  <div>
    <ModalShowDamageReport
      :show="showModal"
      :formDamage="formDamage"
      @save="saveReport"
      @close="showModal = false"
    />

    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 mb-4">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
        <div>
          <InputLabel for="damage-q" :value="$t('iran_search')" />
          <TextInput id="damage-q" type="text" class="mt-1 block w-full" v-model="q" :placeholder="$t('iran_search_damage_ph')" />
        </div>
        <div>
          <InputLabel for="damage-from" :value="$t('iran_from_date')" />
          <TextInput id="damage-from" type="date" class="mt-1 block w-full" v-model="from" />
        </div>
        <div>
          <InputLabel for="damage-to" :value="$t('iran_to_date')" />
          <TextInput id="damage-to" type="date" class="mt-1 block w-full" v-model="to" />
        </div>
        <div class="pt-6">
          <button
            type="button"
            class="w-full px-4 py-2 font-bold text-white bg-blue-600 rounded hover:bg-blue-700"
            @click="openNew"
          >
            {{ $t("iran_new_damage") }}
          </button>
        </div>
      </div>
    </div>

    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-x-auto">
      <table class="w-full text-sm text-center text-gray-600 dark:text-gray-300">
        <thead class="bg-gray-50 dark:bg-gray-900 text-xs uppercase">
          <tr>
            <th class="px-3 py-3">#</th>
            <th class="px-3 py-3">{{ $t("iran_col_date") }}</th>
            <th class="px-3 py-3">{{ $t("iran_col_driver") }}</th>
            <th class="px-3 py-3">{{ $t("iran_col_cmr") }}</th>
            <th class="px-3 py-3">{{ $t("iran_col_cars") }}</th>
            <th class="px-3 py-3">{{ $t("iran_col_damage_total") }}</th>
            <th class="px-3 py-3 print:hidden">{{ $t("iran_col_actions") }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading">
            <td colspan="7" class="py-8 text-gray-400">{{ $t("iran_loading") }}</td>
          </tr>
          <tr v-else-if="!reports.data || reports.data.length === 0">
            <td colspan="7" class="py-8 text-gray-400">{{ $t("iran_no_damage") }}</td>
          </tr>
          <tr
            v-for="report in reports.data"
            :key="report.id"
            class="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700"
          >
            <td class="px-3 py-2 font-semibold">{{ report.id }}</td>
            <td class="px-3 py-2">{{ report.created }}</td>
            <td class="px-3 py-2">{{ report.driver_name || "—" }}</td>
            <td class="px-3 py-2">{{ report.cmr_number || "—" }}</td>
            <td class="px-3 py-2">{{ report.cars_count }}</td>
            <td class="px-3 py-2 font-bold">{{ report.total_damage }}$</td>
            <td class="px-3 py-2 print:hidden">
              <div class="flex justify-center gap-1 flex-wrap">
                <button
                  type="button"
                  class="px-2 py-1 text-white bg-gray-800 rounded text-xs"
                  @click="printReport(report)"
                >
                  {{ $t("iran_print") }}
                </button>
                <button
                  type="button"
                  class="px-2 py-1 text-white bg-red-500 rounded text-xs"
                  @click="deleteReport(report)"
                >
                  {{ $t("iran_delete") }}
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="mt-4 text-center" style="direction: ltr">
      <TailwindPagination
        v-if="reports.data && reports.data.length"
        :data="reports"
        :limit="2"
        @pagination-change-page="fetchReports"
      />
    </div>
  </div>
</template>

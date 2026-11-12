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

    <div class="mb-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-800 sm:p-5">
      <div class="grid grid-cols-1 items-end gap-3 md:grid-cols-4">
        <div>
          <InputLabel for="damage-q" :value="$t('iran_search')" class="!text-slate-600 dark:!text-slate-200" />
          <TextInput id="damage-q" type="text" class="mt-1.5 block w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white dark:placeholder-slate-400" v-model="q" :placeholder="$t('iran_search_damage_ph')" />
        </div>
        <div>
          <InputLabel for="damage-from" :value="$t('iran_from_date')" class="!text-slate-600 dark:!text-slate-200" />
          <TextInput id="damage-from" type="date" class="mt-1.5 block w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white" v-model="from" />
        </div>
        <div>
          <InputLabel for="damage-to" :value="$t('iran_to_date')" class="!text-slate-600 dark:!text-slate-200" />
          <TextInput id="damage-to" type="date" class="mt-1.5 block w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white" v-model="to" />
        </div>
        <div class="pt-1 md:pt-6">
          <button
            type="button"
            class="inline-flex w-full items-center justify-center rounded-xl bg-sky-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-sky-500"
            @click="openNew"
          >
            {{ $t("iran_new_damage") }}
          </button>
        </div>
      </div>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
      <div class="overflow-x-auto">
      <table class="w-full text-sm text-center text-slate-700 dark:text-slate-200">
        <thead>
          <tr class="border-b border-slate-200 bg-slate-50 text-[11px] font-bold uppercase tracking-wide text-slate-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300">
            <th class="px-3 py-3.5">#</th>
            <th class="px-3 py-3.5">{{ $t("iran_col_date") }}</th>
            <th class="px-3 py-3.5">{{ $t("iran_col_driver") }}</th>
            <th class="px-3 py-3.5">{{ $t("iran_col_cmr") }}</th>
            <th class="px-3 py-3.5">{{ $t("iran_col_cars") }}</th>
            <th class="px-3 py-3.5">{{ $t("iran_col_damage_total") }}</th>
            <th class="px-3 py-3.5 print:hidden">{{ $t("iran_col_actions") }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading">
            <td colspan="7" class="py-12 text-slate-400">{{ $t("iran_loading") }}</td>
          </tr>
          <tr v-else-if="!reports.data || reports.data.length === 0">
            <td colspan="7" class="py-12 text-slate-500 dark:text-slate-400">{{ $t("iran_no_damage") }}</td>
          </tr>
          <tr
            v-for="report in reports.data"
            :key="report.id"
            class="border-b border-slate-100 transition-colors last:border-0 hover:bg-slate-50/80 dark:border-slate-700/80 dark:hover:bg-slate-900/50"
          >
            <td class="px-3 py-3 font-bold tabular-nums">{{ report.id }}</td>
            <td class="px-3 py-3 tabular-nums">{{ report.created }}</td>
            <td class="px-3 py-3">{{ report.driver_name || "—" }}</td>
            <td class="px-3 py-3">{{ report.cmr_number || "—" }}</td>
            <td class="px-3 py-3 tabular-nums">{{ report.cars_count }}</td>
            <td class="px-3 py-3 font-bold tabular-nums text-rose-600 dark:text-rose-300">{{ report.total_damage }}$</td>
            <td class="px-3 py-3 print:hidden">
              <div class="flex flex-wrap justify-center gap-1.5">
                <button
                  type="button"
                  class="rounded-lg bg-slate-800 px-2.5 py-1.5 text-xs font-semibold text-white transition hover:bg-slate-700 dark:bg-slate-700"
                  @click="printReport(report)"
                >
                  {{ $t("iran_print") }}
                </button>
                <button
                  type="button"
                  class="rounded-lg bg-rose-600 px-2.5 py-1.5 text-xs font-semibold text-white transition hover:bg-rose-500"
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

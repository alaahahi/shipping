<script setup>
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import { TailwindPagination } from "laravel-vue-pagination";
import { ref, inject, onMounted, watch } from "vue";
import { useToast } from "vue-toastification";
import { useI18n } from "vue-i18n";

const { t } = useI18n();
const toast = useToast();
const iranAxios = inject("iranAxios");
const canDelete = inject("iranCanDelete", ref(false));

const cars = ref({});
const q = ref("");
const loading = ref(false);

const showForm = ref(false);
const editingId = ref(null);
const form = ref({ chassis_no: "", make: "", model: "", year: "", color: "", weight: "", notes: "" });

const fetchCars = async (page = 1) => {
  loading.value = true;
  try {
    const response = await iranAxios.get("/api/iran-invoice-cars", { params: { page, q: q.value, limit: 25 } });
    cars.value = response.data;
  } catch (error) {
    toast.error(t("iran_toast_load_cars_fail"));
  } finally {
    loading.value = false;
  }
};

const resetForm = () => {
  form.value = { chassis_no: "", make: "", model: "", year: "", color: "", weight: "", notes: "" };
  editingId.value = null;
};

const openCreate = () => {
  resetForm();
  showForm.value = true;
};

const openEdit = (car) => {
  editingId.value = car.id;
  form.value = {
    chassis_no: car.chassis_no || "",
    make: car.make || "",
    model: car.model || "",
    year: car.year || "",
    color: car.color || "",
    weight: car.weight || "",
    notes: car.notes || "",
  };
  showForm.value = true;
};

const saveCar = async () => {
  try {
    if (editingId.value) {
      await iranAxios.post(`/api/iran-invoice-cars/${editingId.value}`, form.value);
      toast.success(t("iran_toast_car_updated"));
    } else {
      await iranAxios.post("/api/iran-invoice-cars", form.value);
      toast.success(t("iran_toast_car_added"));
    }
    showForm.value = false;
    resetForm();
    fetchCars();
  } catch (error) {
    toast.error(t("iran_toast_car_save_fail"));
  }
};

const deleteCar = async (car) => {
  if (!confirm(t("iran_confirm_delete_car"))) return;
  try {
    await iranAxios.post(`/api/iran-invoice-cars/${car.id}/delete`);
    toast.success(t("iran_toast_car_deleted"));
    fetchCars();
  } catch (error) {
    toast.error(error.response?.data?.message || t("iran_toast_car_delete_fail"));
  }
};

const uploadAttachment = async (car, event) => {
  const files = event.target.files;
  if (!files?.length) return;
  let uploaded = 0;
  for (const file of files) {
    const data = new FormData();
    data.append("type", "car");
    data.append("id", car.id);
    data.append("file", file);
    try {
      await iranAxios.post("/api/iran-invoice-attachments", data, {
        headers: { "Content-Type": "multipart/form-data" },
      });
      uploaded++;
    } catch (error) {
      toast.error(t("iran_toast_attach_one_fail"));
      break;
    }
  }
  if (uploaded) {
    toast.success(t("iran_toast_attach_count", { n: uploaded }));
    fetchCars();
  }
  event.target.value = "";
};

const deleteAttachment = async (attachment) => {
  if (!confirm(t("iran_confirm_delete_attach"))) return;
  try {
    await iranAxios.post("/api/iran-invoice-attachments/delete", { id: attachment.id });
    toast.success(t("iran_toast_attach_deleted"));
    fetchCars();
  } catch (error) {
    toast.error(error.response?.data?.message || t("iran_toast_attach_delete_fail"));
  }
};

const fileUrl = (name) => (name ? `/public/uploads/${encodeURIComponent(name)}` : "");

let searchTimer = null;
watch(q, () => {
  clearTimeout(searchTimer);
  searchTimer = setTimeout(() => fetchCars(), 400);
});

onMounted(() => fetchCars());
</script>

<template>
  <div>
    <div class="mb-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-800 sm:p-5">
      <div class="grid grid-cols-1 items-end gap-3 md:grid-cols-3">
        <div>
          <InputLabel for="car_search" :value="$t('iran_search')" class="!text-slate-600 dark:!text-slate-200" />
          <TextInput id="car_search" type="text" class="mt-1.5 block w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white dark:placeholder-slate-400" v-model="q" :placeholder="$t('iran_search_car_ph')" />
        </div>
        <div class="md:col-start-3 pt-1 md:pt-6">
          <button type="button" @click="openCreate" class="inline-flex w-full items-center justify-center rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-emerald-500">
            {{ $t("iran_new_car") }}
          </button>
        </div>
      </div>
    </div>

    <div v-if="showForm" class="mb-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-800 sm:p-5">
      <h3 class="mb-3 text-base font-bold text-slate-900 dark:text-white">{{ editingId ? $t("iran_edit_car") : $t("iran_new_car_title") }}</h3>
      <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
        <div><InputLabel :value="$t('iran_col_chassis')" class="!text-slate-600 dark:!text-slate-200" /><TextInput type="text" class="mt-1.5 block w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white" v-model="form.chassis_no" /></div>
        <div><InputLabel :value="$t('iran_make')" class="!text-slate-600 dark:!text-slate-200" /><TextInput type="text" class="mt-1.5 block w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white" v-model="form.make" /></div>
        <div><InputLabel :value="$t('iran_model')" class="!text-slate-600 dark:!text-slate-200" /><TextInput type="text" class="mt-1.5 block w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white" v-model="form.model" /></div>
        <div><InputLabel :value="$t('iran_col_year')" class="!text-slate-600 dark:!text-slate-200" /><TextInput type="text" class="mt-1.5 block w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white" v-model="form.year" /></div>
        <div><InputLabel :value="$t('iran_col_color')" class="!text-slate-600 dark:!text-slate-200" /><TextInput type="text" class="mt-1.5 block w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white" v-model="form.color" /></div>
        <div><InputLabel :value="$t('iran_col_weight')" class="!text-slate-600 dark:!text-slate-200" /><TextInput type="text" class="mt-1.5 block w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white" v-model="form.weight" /></div>
        <div class="md:col-span-2"><InputLabel :value="$t('iran_notes')" class="!text-slate-600 dark:!text-slate-200" /><TextInput type="text" class="mt-1.5 block w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white" v-model="form.notes" /></div>
      </div>
      <div class="mt-4 flex justify-end gap-2">
        <button type="button" @click="showForm = false" class="rounded-xl bg-slate-200 px-4 py-2 text-sm font-semibold text-slate-800 transition hover:bg-slate-300 dark:bg-slate-700 dark:text-slate-100 dark:hover:bg-slate-600">{{ $t("iran_cancel") }}</button>
        <button type="button" @click="saveCar" class="rounded-xl bg-emerald-600 px-5 py-2 text-sm font-bold text-white shadow-sm transition hover:bg-emerald-500">{{ $t("iran_save") }}</button>
      </div>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
      <div class="overflow-x-auto">
      <table class="w-full text-sm text-center text-slate-700 dark:text-slate-200">
        <thead>
          <tr class="border-b border-slate-200 bg-slate-50 text-[11px] font-bold uppercase tracking-wide text-slate-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300">
            <th class="px-3 py-3.5">{{ $t("iran_col_chassis") }}</th>
            <th class="px-3 py-3.5">{{ $t("iran_col_make_model") }}</th>
            <th class="px-3 py-3.5">{{ $t("iran_col_year") }}</th>
            <th class="px-3 py-3.5">{{ $t("iran_col_color") }}</th>
            <th class="px-3 py-3.5">{{ $t("iran_col_weight") }}</th>
            <th class="px-3 py-3.5">{{ $t("iran_col_attachments") }}</th>
            <th class="px-3 py-3.5">{{ $t("iran_col_actions") }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading"><td colspan="7" class="py-12 text-slate-400">{{ $t("iran_loading") }}</td></tr>
          <tr v-else-if="!cars.data || cars.data.length === 0"><td colspan="7" class="py-12 text-slate-500 dark:text-slate-400">{{ $t("iran_no_cars") }}</td></tr>
          <tr v-for="car in cars.data" :key="car.id" class="border-b border-slate-100 transition-colors last:border-0 hover:bg-slate-50/80 dark:border-slate-700/80 dark:hover:bg-slate-900/50">
            <td class="px-3 py-3 font-bold tabular-nums text-slate-900 dark:text-white">{{ car.chassis_no || "—" }}</td>
            <td class="px-3 py-3">{{ [car.make, car.model].filter(Boolean).join(" ") || "—" }}</td>
            <td class="px-3 py-3 tabular-nums">{{ car.year || "—" }}</td>
            <td class="px-3 py-3">{{ car.color || "—" }}</td>
            <td class="px-3 py-3 tabular-nums">{{ car.weight || "—" }}</td>
            <td class="px-3 py-3">
              <div class="flex flex-wrap items-center justify-center gap-1.5">
                <a
                  v-for="att in (car.attachments || [])"
                  :key="att.id"
                  :href="fileUrl(att.file_name)"
                  target="_blank"
                  class="inline-flex items-center gap-1 rounded-lg bg-slate-100 px-2 py-1 text-xs font-medium text-slate-700 dark:bg-slate-700 dark:text-slate-100"
                >
                  📎
                  <button v-if="canDelete" type="button" @click.prevent="deleteAttachment(att)" class="font-bold text-rose-600 dark:text-rose-300">×</button>
                </a>
                <label class="cursor-pointer rounded-lg bg-sky-600 px-2.5 py-1 text-xs font-semibold text-white transition hover:bg-sky-500">
                  +
                  <input type="file" class="hidden" accept="image/*,.pdf" multiple @change="uploadAttachment(car, $event)" />
                </label>
              </div>
            </td>
            <td class="px-3 py-3">
              <div class="flex flex-wrap justify-center gap-1.5">
                <button type="button" @click="openEdit(car)" class="rounded-lg bg-sky-600 px-2.5 py-1.5 text-xs font-semibold text-white transition hover:bg-sky-500">{{ $t("iran_edit") }}</button>
                <button v-if="canDelete" type="button" @click="deleteCar(car)" class="rounded-lg bg-rose-600 px-2.5 py-1.5 text-xs font-semibold text-white transition hover:bg-rose-500">{{ $t("iran_delete") }}</button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
      </div>
    </div>

    <div class="mt-4 text-center" style="direction: ltr">
      <TailwindPagination v-if="cars.data && cars.data.length" :data="cars" @pagination-change-page="fetchCars" :limit="2" />
    </div>
  </div>
</template>

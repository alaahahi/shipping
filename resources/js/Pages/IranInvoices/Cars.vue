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
    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 mb-4">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-3 items-end">
        <div>
          <InputLabel for="car_search" :value="$t('iran_search')" />
          <TextInput id="car_search" type="text" class="mt-1 block w-full" v-model="q" :placeholder="$t('iran_search_car_ph')" />
        </div>
        <div class="md:col-start-3 pt-6">
          <button @click="openCreate" class="block w-full text-center px-4 py-2 font-bold text-white bg-green-600 rounded hover:bg-green-700">
            {{ $t("iran_new_car") }}
          </button>
        </div>
      </div>
    </div>

    <div v-if="showForm" class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 mb-4">
      <h3 class="font-semibold mb-3 dark:text-gray-200">{{ editingId ? $t("iran_edit_car") : $t("iran_new_car_title") }}</h3>
      <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <div><InputLabel :value="$t('iran_col_chassis')" /><TextInput type="text" class="mt-1 block w-full" v-model="form.chassis_no" /></div>
        <div><InputLabel :value="$t('iran_make')" /><TextInput type="text" class="mt-1 block w-full" v-model="form.make" /></div>
        <div><InputLabel :value="$t('iran_model')" /><TextInput type="text" class="mt-1 block w-full" v-model="form.model" /></div>
        <div><InputLabel :value="$t('iran_col_year')" /><TextInput type="text" class="mt-1 block w-full" v-model="form.year" /></div>
        <div><InputLabel :value="$t('iran_col_color')" /><TextInput type="text" class="mt-1 block w-full" v-model="form.color" /></div>
        <div><InputLabel :value="$t('iran_col_weight')" /><TextInput type="text" class="mt-1 block w-full" v-model="form.weight" /></div>
        <div class="md:col-span-2"><InputLabel :value="$t('iran_notes')" /><TextInput type="text" class="mt-1 block w-full" v-model="form.notes" /></div>
      </div>
      <div class="mt-4 flex justify-end gap-2">
        <button @click="showForm = false" class="px-4 py-2 bg-gray-300 dark:bg-gray-600 rounded">{{ $t("iran_cancel") }}</button>
        <button @click="saveCar" class="px-5 py-2 bg-green-600 text-white font-bold rounded">{{ $t("iran_save") }}</button>
      </div>
    </div>

    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-x-auto">
      <table class="w-full text-sm text-center text-gray-600 dark:text-gray-300">
        <thead class="bg-gray-50 dark:bg-gray-900 text-xs uppercase">
          <tr>
            <th class="px-3 py-3">{{ $t("iran_col_chassis") }}</th>
            <th class="px-3 py-3">{{ $t("iran_col_make_model") }}</th>
            <th class="px-3 py-3">{{ $t("iran_col_year") }}</th>
            <th class="px-3 py-3">{{ $t("iran_col_color") }}</th>
            <th class="px-3 py-3">{{ $t("iran_col_weight") }}</th>
            <th class="px-3 py-3">{{ $t("iran_col_attachments") }}</th>
            <th class="px-3 py-3">{{ $t("iran_col_actions") }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading"><td colspan="7" class="py-8 text-gray-400">{{ $t("iran_loading") }}</td></tr>
          <tr v-else-if="!cars.data || cars.data.length === 0"><td colspan="7" class="py-8 text-gray-400">{{ $t("iran_no_cars") }}</td></tr>
          <tr v-for="car in cars.data" :key="car.id" class="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700">
            <td class="px-3 py-2 font-semibold">{{ car.chassis_no || "—" }}</td>
            <td class="px-3 py-2">{{ [car.make, car.model].filter(Boolean).join(" ") || "—" }}</td>
            <td class="px-3 py-2">{{ car.year || "—" }}</td>
            <td class="px-3 py-2">{{ car.color || "—" }}</td>
            <td class="px-3 py-2">{{ car.weight || "—" }}</td>
            <td class="px-3 py-2">
              <div class="flex flex-wrap gap-1 justify-center items-center">
                <a
                  v-for="att in (car.attachments || [])"
                  :key="att.id"
                  :href="fileUrl(att.file_name)"
                  target="_blank"
                  class="px-2 py-1 bg-gray-200 dark:bg-gray-600 rounded text-xs inline-flex items-center gap-1"
                >
                  📎
                  <button v-if="canDelete" @click.prevent="deleteAttachment(att)" class="text-red-600 font-bold">×</button>
                </a>
                <label class="px-2 py-1 bg-indigo-500 text-white rounded text-xs cursor-pointer">
                  +
                  <input type="file" class="hidden" accept="image/*,.pdf" multiple @change="uploadAttachment(car, $event)" />
                </label>
              </div>
            </td>
            <td class="px-3 py-2">
              <div class="flex justify-center gap-1">
                <button @click="openEdit(car)" class="px-2 py-1 bg-blue-500 text-white rounded text-xs">{{ $t("iran_edit") }}</button>
                <button v-if="canDelete" @click="deleteCar(car)" class="px-2 py-1 bg-red-500 text-white rounded text-xs">{{ $t("iran_delete") }}</button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="mt-4 text-center" style="direction: ltr">
      <TailwindPagination v-if="cars.data && cars.data.length" :data="cars" @pagination-change-page="fetchCars" :limit="2" />
    </div>
  </div>
</template>

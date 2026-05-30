<script setup>
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import { TailwindPagination } from "laravel-vue-pagination";
import { ref, onMounted, watch } from "vue";
import axios from "axios";
import { useToast } from "vue-toastification";

const toast = useToast();

const cars = ref({});
const q = ref("");
const loading = ref(false);

const showForm = ref(false);
const editingId = ref(null);
const form = ref({ chassis_no: "", make: "", model: "", year: "", color: "", weight: "", notes: "" });

const fetchCars = async (page = 1) => {
  loading.value = true;
  try {
    const response = await axios.get("/api/iran-invoice-cars", { params: { page, q: q.value, limit: 25 } });
    cars.value = response.data;
  } catch (error) {
    toast.error("تعذر تحميل السيارات");
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
      await axios.post(`/api/iran-invoice-cars/${editingId.value}`, form.value);
      toast.success("تم تحديث السيارة");
    } else {
      await axios.post("/api/iran-invoice-cars", form.value);
      toast.success("تمت إضافة السيارة");
    }
    showForm.value = false;
    resetForm();
    fetchCars();
  } catch (error) {
    toast.error("تعذر حفظ السيارة");
  }
};

const deleteCar = async (car) => {
  if (!confirm("هل تريد حذف هذه السيارة؟")) return;
  try {
    await axios.post(`/api/iran-invoice-cars/${car.id}/delete`);
    toast.success("تم حذف السيارة");
    fetchCars();
  } catch (error) {
    toast.error("تعذر حذف السيارة");
  }
};

const uploadAttachment = async (car, event) => {
  const file = event.target.files[0];
  if (!file) return;
  const data = new FormData();
  data.append("type", "car");
  data.append("id", car.id);
  data.append("file", file);
  try {
    await axios.post("/api/iran-invoice-attachments", data, {
      headers: { "Content-Type": "multipart/form-data" },
    });
    toast.success("تم رفع المرفق");
    fetchCars();
  } catch (error) {
    toast.error("تعذر رفع المرفق");
  } finally {
    event.target.value = "";
  }
};

const deleteAttachment = async (attachment) => {
  if (!confirm("حذف المرفق؟")) return;
  try {
    await axios.post("/api/iran-invoice-attachments/delete", { id: attachment.id });
    toast.success("تم حذف المرفق");
    fetchCars();
  } catch (error) {
    toast.error("تعذر حذف المرفق");
  }
};

const fileUrl = (name) => `/uploads/${name}`;

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
          <InputLabel for="car_search" value="بحث" />
          <TextInput id="car_search" type="text" class="mt-1 block w-full" v-model="q" placeholder="الشاصي / النوع / الموديل" />
        </div>
        <div class="md:col-start-3 pt-6">
          <button @click="openCreate" class="block w-full text-center px-4 py-2 font-bold text-white bg-green-600 rounded hover:bg-green-700">
            + إضافة سيارة
          </button>
        </div>
      </div>
    </div>

    <div v-if="showForm" class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 mb-4">
      <h3 class="font-semibold mb-3 dark:text-gray-200">{{ editingId ? "تعديل سيارة" : "سيارة جديدة" }}</h3>
      <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <div><InputLabel value="Chassis No" /><TextInput type="text" class="mt-1 block w-full" v-model="form.chassis_no" /></div>
        <div><InputLabel value="Make" /><TextInput type="text" class="mt-1 block w-full" v-model="form.make" /></div>
        <div><InputLabel value="Model" /><TextInput type="text" class="mt-1 block w-full" v-model="form.model" /></div>
        <div><InputLabel value="Year" /><TextInput type="text" class="mt-1 block w-full" v-model="form.year" /></div>
        <div><InputLabel value="Color" /><TextInput type="text" class="mt-1 block w-full" v-model="form.color" /></div>
        <div><InputLabel value="Weight" /><TextInput type="text" class="mt-1 block w-full" v-model="form.weight" /></div>
        <div class="md:col-span-2"><InputLabel value="Notes" /><TextInput type="text" class="mt-1 block w-full" v-model="form.notes" /></div>
      </div>
      <div class="mt-4 flex justify-end gap-2">
        <button @click="showForm = false" class="px-4 py-2 bg-gray-300 dark:bg-gray-600 rounded">إلغاء</button>
        <button @click="saveCar" class="px-5 py-2 bg-green-600 text-white font-bold rounded">حفظ</button>
      </div>
    </div>

    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-x-auto">
      <table class="w-full text-sm text-center text-gray-600 dark:text-gray-300">
        <thead class="bg-gray-50 dark:bg-gray-900 text-xs uppercase">
          <tr>
            <th class="px-3 py-3">Chassis No</th>
            <th class="px-3 py-3">Make / Model</th>
            <th class="px-3 py-3">Year</th>
            <th class="px-3 py-3">Color</th>
            <th class="px-3 py-3">Weight</th>
            <th class="px-3 py-3">المرفقات</th>
            <th class="px-3 py-3">تنفيذ</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading"><td colspan="7" class="py-8 text-gray-400">جاري التحميل...</td></tr>
          <tr v-else-if="!cars.data || cars.data.length === 0"><td colspan="7" class="py-8 text-gray-400">لا توجد سيارات</td></tr>
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
                  <button @click.prevent="deleteAttachment(att)" class="text-red-600 font-bold">×</button>
                </a>
                <label class="px-2 py-1 bg-indigo-500 text-white rounded text-xs cursor-pointer">
                  +
                  <input type="file" class="hidden" @change="uploadAttachment(car, $event)" />
                </label>
              </div>
            </td>
            <td class="px-3 py-2">
              <div class="flex justify-center gap-1">
                <button @click="openEdit(car)" class="px-2 py-1 bg-blue-500 text-white rounded text-xs">تعديل</button>
                <button @click="deleteCar(car)" class="px-2 py-1 bg-red-500 text-white rounded text-xs">حذف</button>
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

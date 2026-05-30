<script setup>
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import { TailwindPagination } from "laravel-vue-pagination";
import { ref, onMounted, watch } from "vue";
import axios from "axios";
import { useToast } from "vue-toastification";

const toast = useToast();

const transfers = ref({});
const q = ref("");
const from = ref("");
const to = ref("");
const showArchived = ref(false);
const loading = ref(false);

const showForm = ref(false);
const editingId = ref(null);
const form = ref({
  transfer_date: new Date().toISOString().slice(0, 10),
  amount: "",
  currency: "USD",
  reference_no: "",
  from_text: "",
  to_text: "",
  notes: "",
});

const fetchTransfers = async (page = 1) => {
  loading.value = true;
  try {
    const response = await axios.get("/api/iran-invoice-transfers", {
      params: { page, q: q.value, from: from.value, to: to.value, archived: showArchived.value ? 1 : 0, limit: 25 },
    });
    transfers.value = response.data;
  } catch (error) {
    toast.error("تعذر تحميل الحولات");
  } finally {
    loading.value = false;
  }
};

const resetForm = () => {
  form.value = {
    transfer_date: new Date().toISOString().slice(0, 10),
    amount: "",
    currency: "USD",
    reference_no: "",
    from_text: "",
    to_text: "",
    notes: "",
  };
  editingId.value = null;
};

const openCreate = () => {
  resetForm();
  showForm.value = true;
};

const openEdit = (t) => {
  editingId.value = t.id;
  form.value = {
    transfer_date: t.transfer_date ? String(t.transfer_date).slice(0, 10) : "",
    amount: t.amount ?? "",
    currency: t.currency || "USD",
    reference_no: t.reference_no || "",
    from_text: t.from_text || "",
    to_text: t.to_text || "",
    notes: t.notes || "",
  };
  showForm.value = true;
};

const saveTransfer = async () => {
  try {
    if (editingId.value) {
      await axios.post(`/api/iran-invoice-transfers/${editingId.value}`, form.value);
      toast.success("تم تحديث الحولة");
    } else {
      await axios.post("/api/iran-invoice-transfers", form.value);
      toast.success("تمت إضافة الحولة");
    }
    showForm.value = false;
    resetForm();
    fetchTransfers();
  } catch (error) {
    toast.error("تعذر حفظ الحولة");
  }
};

const archiveTransfer = async (t) => {
  try {
    await axios.post(`/api/iran-invoice-transfers/${t.id}/archive`);
    fetchTransfers();
  } catch (error) {
    toast.error("تعذر الأرشفة");
  }
};

const deleteTransfer = async (t) => {
  if (!confirm("حذف الحولة؟")) return;
  try {
    await axios.post(`/api/iran-invoice-transfers/${t.id}/delete`);
    toast.success("تم حذف الحولة");
    fetchTransfers();
  } catch (error) {
    toast.error("تعذر حذف الحولة");
  }
};

const formatAmount = (value) => {
  if (value === null || value === undefined || value === "") return "—";
  return Number(value).toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

let searchTimer = null;
watch([q, from, to, showArchived], () => {
  clearTimeout(searchTimer);
  searchTimer = setTimeout(() => fetchTransfers(), 400);
});

onMounted(() => fetchTransfers());
</script>

<template>
  <div>
    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 mb-4">
      <div class="grid grid-cols-1 md:grid-cols-5 gap-3 items-end">
        <div>
          <InputLabel for="t_search" value="بحث" />
          <TextInput id="t_search" type="text" class="mt-1 block w-full" v-model="q" placeholder="مرجع / من / إلى" />
        </div>
        <div>
          <InputLabel for="t_from" value="من تاريخ" />
          <TextInput id="t_from" type="date" class="mt-1 block w-full" v-model="from" />
        </div>
        <div>
          <InputLabel for="t_to" value="إلى تاريخ" />
          <TextInput id="t_to" type="date" class="mt-1 block w-full" v-model="to" />
        </div>
        <div class="flex items-center gap-2 pt-6">
          <input id="t_archived" type="checkbox" v-model="showArchived" class="rounded border-gray-300" />
          <label for="t_archived" class="text-sm text-gray-700 dark:text-gray-300">المؤرشفة</label>
        </div>
        <div class="pt-6">
          <button @click="openCreate" class="block w-full text-center px-4 py-2 font-bold text-white bg-green-600 rounded hover:bg-green-700">
            + حولة جديدة
          </button>
        </div>
      </div>
    </div>

    <div v-if="showForm" class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 mb-4">
      <h3 class="font-semibold mb-3 dark:text-gray-200">{{ editingId ? "تعديل حولة" : "حولة جديدة" }}</h3>
      <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <div><InputLabel value="التاريخ" /><TextInput type="date" class="mt-1 block w-full" v-model="form.transfer_date" /></div>
        <div><InputLabel value="المبلغ" /><TextInput type="number" step="0.01" class="mt-1 block w-full" v-model="form.amount" /></div>
        <div>
          <InputLabel value="العملة" />
          <select v-model="form.currency" class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600">
            <option value="USD">USD</option>
            <option value="IQD">IQD</option>
            <option value="AED">AED</option>
            <option value="IRR">IRR</option>
          </select>
        </div>
        <div><InputLabel value="رقم المرجع" /><TextInput type="text" class="mt-1 block w-full" v-model="form.reference_no" /></div>
        <div><InputLabel value="من" /><TextInput type="text" class="mt-1 block w-full" v-model="form.from_text" /></div>
        <div><InputLabel value="إلى" /><TextInput type="text" class="mt-1 block w-full" v-model="form.to_text" /></div>
        <div class="md:col-span-2"><InputLabel value="ملاحظات" /><TextInput type="text" class="mt-1 block w-full" v-model="form.notes" /></div>
      </div>
      <div class="mt-4 flex justify-end gap-2">
        <button @click="showForm = false" class="px-4 py-2 bg-gray-300 dark:bg-gray-600 rounded">إلغاء</button>
        <button @click="saveTransfer" class="px-5 py-2 bg-green-600 text-white font-bold rounded">حفظ</button>
      </div>
    </div>

    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-x-auto">
      <table class="w-full text-sm text-center text-gray-600 dark:text-gray-300">
        <thead class="bg-gray-50 dark:bg-gray-900 text-xs uppercase">
          <tr>
            <th class="px-3 py-3">التاريخ</th>
            <th class="px-3 py-3">المبلغ</th>
            <th class="px-3 py-3">المرجع</th>
            <th class="px-3 py-3">من</th>
            <th class="px-3 py-3">إلى</th>
            <th class="px-3 py-3">الفاتورة</th>
            <th class="px-3 py-3">تنفيذ</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading"><td colspan="7" class="py-8 text-gray-400">جاري التحميل...</td></tr>
          <tr v-else-if="!transfers.data || transfers.data.length === 0"><td colspan="7" class="py-8 text-gray-400">لا توجد حولات</td></tr>
          <tr v-for="t in transfers.data" :key="t.id" class="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700">
            <td class="px-3 py-2">{{ t.transfer_date }}</td>
            <td class="px-3 py-2 font-semibold">{{ formatAmount(t.amount) }} {{ t.currency }}</td>
            <td class="px-3 py-2">{{ t.reference_no || "—" }}</td>
            <td class="px-3 py-2">{{ t.from_text || "—" }}</td>
            <td class="px-3 py-2">{{ t.to_text || "—" }}</td>
            <td class="px-3 py-2">{{ t.invoice?.invoice_no || "—" }}</td>
            <td class="px-3 py-2">
              <div class="flex justify-center gap-1">
                <button @click="openEdit(t)" class="px-2 py-1 bg-blue-500 text-white rounded text-xs">تعديل</button>
                <button @click="archiveTransfer(t)" class="px-2 py-1 bg-amber-500 text-white rounded text-xs">
                  {{ t.is_archived ? "استرجاع" : "أرشفة" }}
                </button>
                <button @click="deleteTransfer(t)" class="px-2 py-1 bg-red-500 text-white rounded text-xs">حذف</button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="mt-4 text-center" style="direction: ltr">
      <TailwindPagination v-if="transfers.data && transfers.data.length" :data="transfers" @pagination-change-page="fetchTransfers" :limit="2" />
    </div>
  </div>
</template>

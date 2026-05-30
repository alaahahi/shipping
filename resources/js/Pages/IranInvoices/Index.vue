<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link } from "@inertiajs/inertia-vue3";
import { TailwindPagination } from "laravel-vue-pagination";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import { ref, watch, onMounted } from "vue";
import axios from "axios";
import { useToast } from "vue-toastification";
import IranInvoiceCars from "./Cars.vue";
import IranInvoiceTransfers from "./Transfers.vue";

const props = defineProps({
  owner_id: Number,
});

const toast = useToast();

const activeTab = ref("invoices"); // invoices | cars | transfers
const showArchived = ref(false);

const invoices = ref({});
const q = ref("");
const from = ref("");
const to = ref("");
const loading = ref(false);

const fetchInvoices = async (page = 1) => {
  loading.value = true;
  try {
    const response = await axios.get("/api/iran-invoices", {
      params: {
        page,
        q: q.value,
        from: from.value,
        to: to.value,
        archived: showArchived.value ? 1 : 0,
        limit: 25,
      },
    });
    invoices.value = response.data;
  } catch (error) {
    console.error(error);
    toast.error("تعذر تحميل الفواتير");
  } finally {
    loading.value = false;
  }
};

const archiveInvoice = async (invoice) => {
  try {
    await axios.post(`/api/iran-invoices/${invoice.id}/archive`);
    toast.success("تمت أرشفة الفاتورة");
    fetchInvoices();
  } catch (error) {
    toast.error("تعذر أرشفة الفاتورة");
  }
};

const unarchiveInvoice = async (invoice) => {
  try {
    await axios.post(`/api/iran-invoices/${invoice.id}/unarchive`);
    toast.success("تم إلغاء الأرشفة");
    fetchInvoices();
  } catch (error) {
    toast.error("تعذر إلغاء الأرشفة");
  }
};

const deleteInvoice = async (invoice) => {
  if (!confirm(`هل تريد حذف الفاتورة ${invoice.invoice_no || invoice.id}؟`)) return;
  try {
    await axios.post(`/api/iran-invoices/${invoice.id}/delete`);
    toast.success("تم حذف الفاتورة");
    fetchInvoices();
  } catch (error) {
    toast.error("تعذر حذف الفاتورة");
  }
};

const formatPrice = (value) => {
  if (value === null || value === undefined || value === "") return "—";
  return Number(value).toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

const fileUrl = (name) => (name ? `/uploads/${encodeURIComponent(name)}` : "");

const uploadAttachment = async (invoice, event) => {
  const file = event.target.files[0];
  if (!file) return;
  const data = new FormData();
  data.append("type", "invoice");
  data.append("id", invoice.id);
  data.append("file", file);
  try {
    const response = await axios.post("/api/iran-invoice-attachments", data, {
      headers: { "Content-Type": "multipart/form-data" },
    });
    if (!invoice.attachments) invoice.attachments = [];
    invoice.attachments.push(response.data);
    toast.success("تم رفع المرفق");
  } catch (error) {
    toast.error("تعذر رفع المرفق");
  } finally {
    event.target.value = "";
  }
};

const deleteAttachment = async (invoice, attachment) => {
  if (!confirm("حذف المرفق؟")) return;
  try {
    await axios.post("/api/iran-invoice-attachments/delete", { id: attachment.id });
    invoice.attachments = (invoice.attachments || []).filter((a) => a.id !== attachment.id);
    toast.success("تم حذف المرفق");
  } catch (error) {
    toast.error("تعذر حذف المرفق");
  }
};

watch([showArchived, q, from, to], () => {
  if (activeTab.value === "invoices") {
    fetchInvoices();
  }
});

watch(activeTab, (tab) => {
  if (tab === "invoices") {
    fetchInvoices();
  }
});

onMounted(() => {
  fetchInvoices();
});
</script>

<template>
  <Head title="Iran Invoices" />
  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
        فواتير إيران
      </h2>
    </template>

    <div class="py-8">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Tabs -->
        <div class="mb-6 border-b border-gray-200 dark:border-gray-700">
          <nav class="-mb-px flex gap-6">
            <button
              @click="activeTab = 'invoices'"
              :class="[
                activeTab === 'invoices'
                  ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400',
                'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm',
              ]"
            >
              الفواتير
            </button>
            <button
              @click="activeTab = 'cars'"
              :class="[
                activeTab === 'cars'
                  ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400',
                'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm',
              ]"
            >
              السيارات
            </button>
            <button
              @click="activeTab = 'transfers'"
              :class="[
                activeTab === 'transfers'
                  ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400',
                'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm',
              ]"
            >
              الحولات
            </button>
          </nav>
        </div>

        <!-- Invoices Tab -->
        <div v-show="activeTab === 'invoices'">
          <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 mb-4">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-3 items-end">
              <div>
                <InputLabel for="search" value="بحث" />
                <TextInput id="search" type="text" class="mt-1 block w-full" v-model="q" placeholder="رقم الفاتورة / الشاصي" />
              </div>
              <div>
                <InputLabel for="from" value="من تاريخ" />
                <TextInput id="from" type="date" class="mt-1 block w-full" v-model="from" />
              </div>
              <div>
                <InputLabel for="to" value="إلى تاريخ" />
                <TextInput id="to" type="date" class="mt-1 block w-full" v-model="to" />
              </div>
              <div class="flex items-center gap-2 pt-6">
                <input id="archived" type="checkbox" v-model="showArchived" class="rounded border-gray-300" />
                <label for="archived" class="text-sm text-gray-700 dark:text-gray-300">عرض المؤرشفة</label>
              </div>
              <div class="pt-6">
                <Link
                  :href="route('iranInvoices.create')"
                  class="block text-center px-4 py-2 font-bold text-white bg-green-600 rounded hover:bg-green-700"
                >
                  + فاتورة جديدة
                </Link>
              </div>
            </div>
          </div>

          <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-x-auto">
            <table class="w-full text-sm text-center text-gray-600 dark:text-gray-300">
              <thead class="bg-gray-50 dark:bg-gray-900 text-xs uppercase">
                <tr>
                  <th class="px-3 py-3">Invoice No</th>
                  <th class="px-3 py-3">Date</th>
                  <th class="px-3 py-3">Carrier</th>
                  <th class="px-3 py-3">Consignee</th>
                  <th class="px-3 py-3">Cars</th>
                  <th class="px-3 py-3">Total</th>
                  <th class="px-3 py-3 print:hidden">المرفقات</th>
                  <th class="px-3 py-3 print:hidden">تنفيذ</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="loading">
                  <td colspan="8" class="py-8 text-gray-400">جاري التحميل...</td>
                </tr>
                <tr v-else-if="!invoices.data || invoices.data.length === 0">
                  <td colspan="8" class="py-8 text-gray-400">لا توجد فواتير</td>
                </tr>
                <tr
                  v-for="invoice in invoices.data"
                  :key="invoice.id"
                  class="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700"
                >
                  <td class="px-3 py-2 font-semibold">{{ invoice.invoice_no }}</td>
                  <td class="px-3 py-2">{{ invoice.invoice_date }}</td>
                  <td class="px-3 py-2">{{ invoice.carrier_name || invoice.carrier?.name || "—" }}</td>
                  <td class="px-3 py-2">{{ invoice.consignee_name || invoice.consignee?.name || "—" }}</td>
                  <td class="px-3 py-2">{{ invoice.items_count }}</td>
                  <td class="px-3 py-2">{{ formatPrice(invoice.total_price) }} {{ invoice.currency }}</td>
                  <td class="px-3 py-2 print:hidden">
                    <div class="flex flex-wrap gap-1 justify-center items-center">
                      <a
                        v-for="att in (invoice.attachments || [])"
                        :key="att.id"
                        :href="fileUrl(att.file_name)"
                        target="_blank"
                        class="px-2 py-1 bg-gray-200 dark:bg-gray-600 rounded text-xs inline-flex items-center gap-1"
                      >
                        📎
                        <button @click.prevent="deleteAttachment(invoice, att)" class="text-red-600 font-bold">×</button>
                      </a>
                      <label class="px-2 py-1 bg-indigo-500 text-white rounded text-xs cursor-pointer">
                        +
                        <input type="file" class="hidden" accept="image/*,.pdf" @change="uploadAttachment(invoice, $event)" />
                      </label>
                    </div>
                  </td>
                  <td class="px-3 py-2 print:hidden">
                    <div class="flex justify-center gap-1 flex-wrap">
                      <a
                        :href="route('iranInvoices.print', invoice.id)"
                        target="_blank"
                        class="px-2 py-1 text-white bg-gray-800 rounded text-xs"
                      >طباعة</a>
                      <Link
                        :href="route('iranInvoices.edit', invoice.id)"
                        class="px-2 py-1 text-white bg-blue-500 rounded text-xs"
                      >تعديل</Link>
                      <button
                        v-if="!showArchived"
                        @click="archiveInvoice(invoice)"
                        class="px-2 py-1 text-white bg-amber-500 rounded text-xs"
                      >أرشفة</button>
                      <button
                        v-else
                        @click="unarchiveInvoice(invoice)"
                        class="px-2 py-1 text-white bg-teal-500 rounded text-xs"
                      >استرجاع</button>
                      <button
                        @click="deleteInvoice(invoice)"
                        class="px-2 py-1 text-white bg-red-500 rounded text-xs"
                      >حذف</button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="mt-4 text-center" style="direction: ltr">
            <TailwindPagination
              v-if="invoices.data && invoices.data.length"
              :data="invoices"
              @pagination-change-page="fetchInvoices"
              :limit="2"
            />
          </div>
        </div>

        <!-- Cars Tab -->
        <IranInvoiceCars v-if="activeTab === 'cars'" />

        <!-- Transfers Tab -->
        <IranInvoiceTransfers v-if="activeTab === 'transfers'" />
      </div>
    </div>
  </AuthenticatedLayout>
</template>

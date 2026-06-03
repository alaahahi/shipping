<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link } from "@inertiajs/inertia-vue3";
import { Inertia } from "@inertiajs/inertia";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import { ref, onMounted, computed } from "vue";
import axios from "axios";
import { useToast } from "vue-toastification";

const props = defineProps({
  owner_id: Number,
  invoice_id: { type: Number, default: null },
});

const toast = useToast();

const carriers = ref([]);
const consignees = ref([]);
const saving = ref(false);
const attachments = ref([]);
const nextInvoiceNo = ref("");

const form = ref({
  invoice_no: "",
  invoice_date: todayLocal(),
  carrier_id: "",
  consignee_id: "",
  destination: "",
  currency: "USD",
  notes: "",
  total_price: "",
  items: [],
});

const isEdit = computed(() => !!props.invoice_id);

/** Avoid timezone shift when Laravel returns ISO date strings (UTC). */
const toDateInput = (value) => {
  if (!value) return "";
  const str = String(value);
  if (!str.includes("T")) {
    return str.slice(0, 10);
  }
  const d = new Date(str);
  if (Number.isNaN(d.getTime())) {
    return str.slice(0, 10);
  }
  const y = d.getUTCFullYear();
  const m = String(d.getUTCMonth() + 1).padStart(2, "0");
  const day = String(d.getUTCDate()).padStart(2, "0");
  return `${y}-${m}-${day}`;
};

const todayLocal = () => {
  const d = new Date();
  return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, "0")}-${String(d.getDate()).padStart(2, "0")}`;
};

const emptyItem = () => ({
  id: null,
  car_id: null,
  chassis_no: "",
  make: "",
  model: "",
  year: "",
  color: "",
  weight: "",
  unit_price: "",
  notes: "",
  attachments: [],
});

const addItem = () => {
  form.value.items.push(emptyItem());
};

const removeItem = (index) => {
  form.value.items.splice(index, 1);
};

const computedTotal = computed(() => {
  const priced = form.value.items.filter((i) => i.unit_price !== "" && i.unit_price !== null);
  if (priced.length === 0) return null;
  return priced.reduce((sum, i) => sum + Number(i.unit_price || 0), 0);
});

const fetchLookups = async () => {
  try {
    const [c, cn] = await Promise.all([
      axios.get("/api/iran-invoice-carriers"),
      axios.get("/api/iran-invoice-consignees"),
    ]);
    carriers.value = c.data || [];
    consignees.value = cn.data || [];
  } catch (error) {
    console.error(error);
  }
};

const fetchNextInvoiceNo = async () => {
  try {
    const response = await axios.get("/api/iran-invoices/next-no");
    nextInvoiceNo.value = response.data.invoice_no || "";
  } catch (error) {
    console.error(error);
  }
};

const fetchInvoice = async () => {
  if (!props.invoice_id) return;
  try {
    const response = await axios.get(`/api/iran-invoices/${props.invoice_id}`);
    const data = response.data;
    form.value = {
      invoice_no: data.invoice_no || "",
      invoice_date: toDateInput(data.invoice_date),
      carrier_id: data.carrier_id || "",
      consignee_id: data.consignee_id || "",
      destination: data.destination || "",
      currency: data.currency || "USD",
      notes: data.notes || "",
      total_price: data.total_price ?? "",
      items: (data.items || []).map((i) => ({
        id: i.id,
        car_id: i.car_id,
        chassis_no: i.chassis_no || "",
        make: i.make || "",
        model: i.model || "",
        year: i.year || "",
        color: i.color || "",
        weight: i.weight || "",
        unit_price: i.unit_price ?? "",
        notes: i.notes || "",
        attachments: i.attachments || [],
      })),
    };
    attachments.value = data.attachments || [];
  } catch (error) {
    console.error(error);
    toast.error("تعذر تحميل الفاتورة");
  }
};

const addCarrierInline = async () => {
  const name = prompt("اسم الجهة الناقلة الجديدة:");
  if (!name) return;
  try {
    const response = await axios.post("/api/iran-invoice-carriers", { name });
    carriers.value.push(response.data);
    form.value.carrier_id = response.data.id;
    toast.success("تمت إضافة الجهة الناقلة");
  } catch (error) {
    toast.error("تعذر إضافة الجهة الناقلة");
  }
};

const addConsigneeInline = async () => {
  const name = prompt("اسم المستخدم / المستفيد الجديد:");
  if (!name) return;
  try {
    const response = await axios.post("/api/iran-invoice-consignees", { name });
    consignees.value.push(response.data);
    form.value.consignee_id = response.data.id;
    toast.success("تمت إضافة المستفيد");
  } catch (error) {
    toast.error("تعذر إضافة المستفيد");
  }
};

const fileUrl = (name) => (name ? `/public/uploads/${encodeURIComponent(name)}` : "");
const thumbUrl = (name) => (name ? `/public/uploadsResized/${encodeURIComponent(name)}` : "");

const isImageFile = (name) => /\.(jpe?g|png|gif|webp|bmp)$/i.test(name || "");

const uploadAttachment = async (event) => {
  const files = event.target.files;
  if (!files?.length || !props.invoice_id) return;
  let uploaded = 0;
  for (const file of files) {
    const data = new FormData();
    data.append("type", "invoice");
    data.append("id", props.invoice_id);
    data.append("file", file);
    try {
      const response = await axios.post("/api/iran-invoice-attachments", data, {
        headers: { "Content-Type": "multipart/form-data" },
      });
      attachments.value.push(response.data);
      uploaded++;
    } catch (error) {
      toast.error("تعذر رفع أحد المرفقات");
      break;
    }
  }
  if (uploaded) toast.success(`تم رفع ${uploaded} مرفق`);
  event.target.value = "";
};

const uploadItemAttachment = async (item, event) => {
  const files = event.target.files;
  if (!files?.length || !item.id) return;
  if (!item.attachments) item.attachments = [];
  let uploaded = 0;
  for (const file of files) {
    const data = new FormData();
    data.append("type", "item");
    data.append("id", item.id);
    data.append("file", file);
    try {
      const response = await axios.post("/api/iran-invoice-attachments", data, {
        headers: { "Content-Type": "multipart/form-data" },
      });
      item.attachments.push(response.data);
      uploaded++;
    } catch (error) {
      toast.error("تعذر رفع أحد المرفقات");
      break;
    }
  }
  if (uploaded) toast.success(`تم رفع ${uploaded} مرفق`);
  event.target.value = "";
};

const deleteItemAttachment = async (item, attachment) => {
  if (!confirm("حذف المرفق؟")) return;
  try {
    await axios.post("/api/iran-invoice-attachments/delete", { id: attachment.id });
    item.attachments = (item.attachments || []).filter((a) => a.id !== attachment.id);
    toast.success("تم حذف المرفق");
  } catch (error) {
    toast.error("تعذر حذف المرفق");
  }
};

const deleteAttachment = async (attachment) => {
  if (!confirm("حذف المرفق؟")) return;
  try {
    await axios.post("/api/iran-invoice-attachments/delete", { id: attachment.id });
    attachments.value = attachments.value.filter((a) => a.id !== attachment.id);
    toast.success("تم حذف المرفق");
  } catch (error) {
    toast.error("تعذر حذف المرفق");
  }
};

const save = async () => {
  if (form.value.items.length === 0) {
    toast.error("أضف سيارة واحدة على الأقل");
    return;
  }
  saving.value = true;
  let succeeded = false;
  try {
    const payload = {
      ...form.value,
      items: form.value.items.map((item) => ({
        id: item.id || undefined,
        car_id: item.car_id,
        chassis_no: item.chassis_no,
        make: item.make,
        model: item.model,
        year: item.year,
        color: item.color,
        weight: item.weight,
        unit_price: item.unit_price,
        notes: item.notes,
      })),
    };
    if (!String(payload.invoice_no || "").trim()) {
      delete payload.invoice_no;
    }
    if (isEdit.value) {
      await axios.post(`/api/iran-invoices/${props.invoice_id}`, payload);
    } else {
      await axios.post("/api/iran-invoices", payload);
    }
    succeeded = true;
  } catch (error) {
    console.error(error);
    toast.error("تعذر حفظ الفاتورة");
  } finally {
    saving.value = false;
  }

  // Navigation/toast kept outside the try so a navigation error never
  // triggers the failure toast after a successful save (200).
  if (succeeded) {
    toast.success(isEdit.value ? "تم تحديث الفاتورة" : "تم حفظ الفاتورة");
    Inertia.visit(route("iranInvoices.index"));
  }
};

onMounted(async () => {
  await fetchLookups();
  if (isEdit.value) {
    await fetchInvoice();
  } else {
    addItem();
    await fetchNextInvoiceNo();
  }
});
</script>

<template>
  <Head :title="isEdit ? 'Edit Iran Invoice' : 'New Iran Invoice'" />
  <AuthenticatedLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
          {{ isEdit ? "تعديل فاتورة إيران" : "فاتورة إيران جديدة" }}
        </h2>
        <Link :href="route('iranInvoices.index')" class="text-sm text-indigo-600 dark:text-indigo-400">
          ← رجوع للقائمة
        </Link>
      </div>
    </template>

    <div class="py-8">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
          <!-- Invoice header -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div>
              <InputLabel for="invoice_no" value="رقم الفاتورة (اختياري - يُولّد تلقائياً)" />
              <TextInput
                id="invoice_no"
                type="text"
                class="mt-1 block w-full"
                v-model="form.invoice_no"
                :placeholder="isEdit ? '' : (nextInvoiceNo || 'IR-2026-0001')"
              />
              <p v-if="!isEdit && nextInvoiceNo" class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                الرقم التالي: {{ nextInvoiceNo }}
              </p>
            </div>
            <div>
              <InputLabel for="invoice_date" value="التاريخ" />
              <TextInput id="invoice_date" type="date" class="mt-1 block w-full" v-model="form.invoice_date" />
            </div>
            <div>
              <InputLabel for="currency" value="العملة" />
              <select id="currency" v-model="form.currency" class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600">
                <option value="USD">USD</option>
                <option value="IQD">IQD</option>
                <option value="AED">AED</option>
                <option value="IRR">IRR</option>
              </select>
            </div>
            <div>
              <InputLabel for="carrier" value="الجهة الناقلة (Carrier)" />
              <div class="flex gap-2 mt-1">
                <select id="carrier" v-model="form.carrier_id" class="block w-full rounded-md border-gray-300 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600">
                  <option value="">— اختر —</option>
                  <option v-for="c in carriers" :key="c.id" :value="c.id">{{ c.name }}</option>
                </select>
                <button type="button" @click="addCarrierInline" class="px-3 bg-indigo-600 text-white rounded">+</button>
              </div>
            </div>
            <div>
              <InputLabel for="consignee" value="المستخدم / المستفيد (Consignee)" />
              <div class="flex gap-2 mt-1">
                <select id="consignee" v-model="form.consignee_id" class="block w-full rounded-md border-gray-300 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600">
                  <option value="">— اختر —</option>
                  <option v-for="c in consignees" :key="c.id" :value="c.id">{{ c.name }}</option>
                </select>
                <button type="button" @click="addConsigneeInline" class="px-3 bg-indigo-600 text-white rounded">+</button>
              </div>
            </div>
            <div>
              <InputLabel for="destination" value="الوجهة (TRANSIT TO / Destination CIP)" />
              <TextInput id="destination" type="text" class="mt-1 block w-full" v-model="form.destination" placeholder="ANZALI HASSAN ROAD" />
            </div>
            <div>
              <InputLabel for="total_price" value="الإجمالي (اختياري - يُحسب تلقائياً)" />
              <TextInput id="total_price" type="number" step="0.01" class="mt-1 block w-full" v-model="form.total_price" :placeholder="computedTotal !== null ? String(computedTotal) : 'فارغ'" />
            </div>
          </div>

          <!-- Items table -->
          <div class="flex justify-between items-center mb-2">
            <h3 class="font-semibold text-gray-700 dark:text-gray-200">السيارات في الفاتورة</h3>
            <button type="button" @click="addItem" class="px-3 py-1 bg-green-600 text-white rounded text-sm">+ إضافة سيارة</button>
          </div>
          <div class="overflow-x-auto border rounded-lg dark:border-gray-700">
            <table class="w-full text-sm text-center">
              <thead class="bg-gray-50 dark:bg-gray-900 text-xs uppercase text-gray-500 dark:text-gray-400">
                <tr>
                  <th class="px-2 py-2">#</th>
                  <th class="px-2 py-2">Chassis No</th>
                  <th class="px-2 py-2">Make</th>
                  <th class="px-2 py-2">Model</th>
                  <th class="px-2 py-2">Year</th>
                  <th class="px-2 py-2">Color</th>
                  <th class="px-2 py-2">Weight</th>
                  <th class="px-2 py-2">Unit Price</th>
                  <th class="px-2 py-2">Notes</th>
                  <th v-if="isEdit" class="px-2 py-2 print:hidden">صور السيارة</th>
                  <th class="px-2 py-2"></th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(item, index) in form.items" :key="item.id || `new-${index}`" class="border-t dark:border-gray-700">
                  <td class="px-2 py-1">{{ index + 1 }}</td>
                  <td class="px-2 py-1"><input v-model="item.chassis_no" class="w-28 rounded border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white text-sm" /></td>
                  <td class="px-2 py-1"><input v-model="item.make" class="w-24 rounded border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white text-sm" /></td>
                  <td class="px-2 py-1"><input v-model="item.model" class="w-24 rounded border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white text-sm" /></td>
                  <td class="px-2 py-1"><input v-model="item.year" class="w-16 rounded border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white text-sm" /></td>
                  <td class="px-2 py-1"><input v-model="item.color" class="w-20 rounded border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white text-sm" /></td>
                  <td class="px-2 py-1"><input v-model="item.weight" class="w-16 rounded border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white text-sm" /></td>
                  <td class="px-2 py-1"><input v-model="item.unit_price" type="number" step="0.01" placeholder="فارغ" class="w-20 rounded border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white text-sm" /></td>
                  <td class="px-2 py-1"><input v-model="item.notes" class="w-28 rounded border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white text-sm" /></td>
                  <td v-if="isEdit" class="px-2 py-1 print:hidden">
                    <div v-if="item.id" class="flex flex-wrap gap-1 justify-center items-center max-w-[140px] mx-auto">
                      <a
                        v-for="att in (item.attachments || [])"
                        :key="att.id"
                        :href="fileUrl(att.file_name)"
                        target="_blank"
                        class="relative inline-block"
                        :title="att.original_name || att.file_name"
                      >
                        <img
                          v-if="isImageFile(att.file_name)"
                          :src="thumbUrl(att.file_name)"
                          class="h-10 w-10 object-cover rounded border dark:border-gray-600"
                          @error="(e) => { e.target.src = fileUrl(att.file_name); }"
                        />
                        <span v-else class="inline-flex h-10 w-10 items-center justify-center bg-gray-200 dark:bg-gray-600 rounded text-xs">📎</span>
                        <button
                          type="button"
                          @click.prevent="deleteItemAttachment(item, att)"
                          class="absolute -top-1 -right-1 h-4 w-4 bg-red-600 text-white rounded-full text-xs leading-none"
                        >×</button>
                      </a>
                      <label class="h-10 w-10 flex items-center justify-center bg-indigo-500 text-white rounded cursor-pointer text-lg" title="إرفاق صور">
                        +
                        <input type="file" class="hidden" accept="image/*,.pdf" multiple @change="uploadItemAttachment(item, $event)" />
                      </label>
                    </div>
                    <span v-else class="text-xs text-gray-400">احفظ أولاً</span>
                  </td>
                  <td class="px-2 py-1">
                    <button type="button" @click="removeItem(index)" class="px-2 py-1 bg-red-500 text-white rounded text-xs">حذف</button>
                  </td>
                </tr>
                <tr v-if="form.items.length === 0">
                  <td :colspan="isEdit ? 11 : 10" class="py-6 text-gray-400">لا توجد سيارات - اضغط "إضافة سيارة"</td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="mt-4">
            <InputLabel for="notes" value="ملاحظات الفاتورة" />
            <textarea id="notes" v-model="form.notes" rows="2" class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600"></textarea>
          </div>

          <!-- Attachments (edit only — not included on printed invoice) -->
          <div v-if="isEdit" class="mt-6 print:hidden">
            <div class="flex justify-between items-center mb-2">
              <h3 class="font-semibold text-gray-700 dark:text-gray-200">المرفقات (صور / ملفات)</h3>
              <label class="px-3 py-1 bg-indigo-600 text-white rounded text-sm cursor-pointer">
                + إرفاق ملف
                <input type="file" class="hidden" accept="image/*,.pdf" multiple @change="uploadAttachment" />
              </label>
            </div>
            <div class="overflow-x-auto border rounded-lg dark:border-gray-700">
              <table class="w-full text-sm text-center">
                <thead class="bg-gray-50 dark:bg-gray-900 text-xs uppercase text-gray-500 dark:text-gray-400">
                  <tr>
                    <th class="px-3 py-2 w-20">معاينة</th>
                    <th class="px-3 py-2">اسم الملف</th>
                    <th class="px-3 py-2 w-28">تاريخ الرفع</th>
                    <th class="px-3 py-2 w-24">تنفيذ</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-if="attachments.length === 0">
                    <td colspan="4" class="py-6 text-gray-400">لا توجد مرفقات — اضغط «إرفاق ملف»</td>
                  </tr>
                  <tr
                    v-for="att in attachments"
                    :key="att.id"
                    class="border-t dark:border-gray-700"
                  >
                    <td class="px-3 py-2">
                      <a :href="fileUrl(att.file_name)" target="_blank" class="inline-block">
                        <img
                          v-if="isImageFile(att.file_name)"
                          :src="thumbUrl(att.file_name)"
                          :alt="att.original_name"
                          class="h-14 w-14 object-cover rounded border dark:border-gray-600 mx-auto"
                          @error="(e) => { e.target.src = fileUrl(att.file_name); }"
                        />
                        <span v-else class="text-2xl">📎</span>
                      </a>
                    </td>
                    <td class="px-3 py-2 text-start dark:text-gray-200">
                      <a :href="fileUrl(att.file_name)" target="_blank" class="text-indigo-600 dark:text-indigo-400 hover:underline">
                        {{ att.original_name || att.file_name }}
                      </a>
                    </td>
                    <td class="px-3 py-2 text-gray-500 dark:text-gray-400">{{ att.created_at ? String(att.created_at).slice(0, 10) : "—" }}</td>
                    <td class="px-3 py-2">
                      <button type="button" @click="deleteAttachment(att)" class="px-2 py-1 bg-red-500 text-white rounded text-xs">حذف</button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
          <p v-else class="mt-4 text-sm text-gray-500 dark:text-gray-400 print:hidden">
            يمكن إرفاق الصور والملفات بعد حفظ الفاتورة من صفحة التعديل.
          </p>

          <div class="mt-6 flex justify-end gap-3">
            <Link :href="route('iranInvoices.index')" class="px-5 py-2 bg-gray-300 dark:bg-gray-600 rounded">إلغاء</Link>
            <button @click="save" :disabled="saving" class="px-6 py-2 bg-green-600 text-white font-bold rounded disabled:opacity-60">
              {{ saving ? "جاري الحفظ..." : "حفظ الفاتورة" }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

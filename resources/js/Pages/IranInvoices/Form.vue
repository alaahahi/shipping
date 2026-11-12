<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link } from "@inertiajs/inertia-vue3";
import { Inertia } from "@inertiajs/inertia";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import IranPinGate from "@/Components/IranPinGate.vue";
import { ref, computed, watch } from "vue";
import { useToast } from "vue-toastification";
import { useI18n } from "vue-i18n";
import { useIranPageAccess } from "@/composables/useIranPageAccess";

const props = defineProps({
  owner_id: Number,
  invoice_id: { type: Number, default: null },
  iran_unlocked: { type: Boolean, default: false },
  iran_can_delete: { type: Boolean, default: false },
  iran_is_admin: { type: Boolean, default: false },
});

const { t } = useI18n();
const { unlocked, canDelete, pin, pinError, unlocking, iranAxios } = useIranPageAccess(props);

const toast = useToast();

const carriers = ref([]);
const consignees = ref([]);
const saving = ref(false);
const attachments = ref([]);
const nextInvoiceNo = ref("");

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
      iranAxios.get("/api/iran-invoice-carriers"),
      iranAxios.get("/api/iran-invoice-consignees"),
    ]);
    carriers.value = c.data || [];
    consignees.value = cn.data || [];
  } catch (error) {
    console.error(error);
  }
};

const fetchNextInvoiceNo = async () => {
  try {
    const response = await iranAxios.get("/api/iran-invoices/next-no");
    nextInvoiceNo.value = response.data.invoice_no || "";
  } catch (error) {
    console.error(error);
  }
};

const fetchInvoice = async () => {
  if (!props.invoice_id) return;
  try {
    const response = await iranAxios.get(`/api/iran-invoices/${props.invoice_id}`);
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
    const response = await iranAxios.post("/api/iran-invoice-carriers", { name });
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
    const response = await iranAxios.post("/api/iran-invoice-consignees", { name });
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
      const response = await iranAxios.post("/api/iran-invoice-attachments", data, {
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
      const response = await iranAxios.post("/api/iran-invoice-attachments", data, {
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
    await iranAxios.post("/api/iran-invoice-attachments/delete", { id: attachment.id });
    item.attachments = (item.attachments || []).filter((a) => a.id !== attachment.id);
    toast.success("تم حذف المرفق");
  } catch (error) {
    toast.error(error.response?.data?.message || "تعذر حذف المرفق");
  }
};

const deleteAttachment = async (attachment) => {
  if (!confirm("حذف المرفق؟")) return;
  try {
    await iranAxios.post("/api/iran-invoice-attachments/delete", { id: attachment.id });
    attachments.value = attachments.value.filter((a) => a.id !== attachment.id);
    toast.success("تم حذف المرفق");
  } catch (error) {
    toast.error(error.response?.data?.message || "تعذر حذف المرفق");
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
      await iranAxios.post(`/api/iran-invoices/${props.invoice_id}`, payload);
    } else {
      await iranAxios.post("/api/iran-invoices", payload);
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

watch(
  unlocked,
  async (isUnlocked) => {
    if (!isUnlocked) return;
    await fetchLookups();
    if (isEdit.value) {
      await fetchInvoice();
    } else if (form.value.items.length === 0) {
      addItem();
      await fetchNextInvoiceNo();
    }
  },
  { immediate: true }
);
</script>

<template>
  <Head :title="isEdit ? $t('iran_head_edit') : $t('iran_head_new')" />
  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between gap-3">
        <h2 class="text-xl font-bold leading-tight text-slate-900 dark:text-white">
          {{ isEdit ? $t("iran_form_title_edit") : $t("iran_form_title_new") }}
        </h2>
        <Link :href="route('iranInvoices.index')" class="text-sm font-semibold text-emerald-600 transition hover:text-emerald-500 dark:text-emerald-300">
          ← {{ $t("iran_form_back") }}
        </Link>
      </div>
    </template>

    <IranPinGate :unlocked="unlocked" :pin="pin" :error="pinError" :unlocking="unlocking" @update:pin="pin = $event">
    <div class="py-8">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-800 sm:p-6">
          <!-- Invoice header -->
          <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-3">
            <div>
              <InputLabel for="invoice_no" :value="$t('iran_form_invoice_no')" class="!text-slate-600 dark:!text-slate-200" />
              <TextInput
                id="invoice_no"
                type="text"
                class="mt-1.5 block w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white dark:placeholder-slate-400"
                v-model="form.invoice_no"
                :placeholder="isEdit ? '' : (nextInvoiceNo || 'IR-2026-0001')"
              />
              <p v-if="!isEdit && nextInvoiceNo" class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                {{ $t("iran_form_next_no", { no: nextInvoiceNo }) }}
              </p>
            </div>
            <div>
              <InputLabel for="invoice_date" :value="$t('iran_form_date')" class="!text-slate-600 dark:!text-slate-200" />
              <TextInput id="invoice_date" type="date" class="mt-1.5 block w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white" v-model="form.invoice_date" />
            </div>
            <div>
              <InputLabel for="currency" :value="$t('iran_currency')" class="!text-slate-600 dark:!text-slate-200" />
              <select id="currency" v-model="form.currency" class="mt-1.5 block w-full rounded-xl border-slate-300 bg-white text-slate-900 dark:border-slate-600 dark:bg-slate-900 dark:text-white">
                <option value="USD">USD</option>
                <option value="IQD">IQD</option>
                <option value="AED">AED</option>
                <option value="IRR">IRR</option>
              </select>
            </div>
            <div>
              <InputLabel for="carrier" :value="$t('iran_form_carrier')" class="!text-slate-600 dark:!text-slate-200" />
              <div class="mt-1.5 flex gap-2">
                <select id="carrier" v-model="form.carrier_id" class="block w-full rounded-xl border-slate-300 bg-white text-slate-900 dark:border-slate-600 dark:bg-slate-900 dark:text-white">
                  <option value="">{{ $t("iran_form_choose") }}</option>
                  <option v-for="c in carriers" :key="c.id" :value="c.id">{{ c.name }}</option>
                </select>
                <button type="button" @click="addCarrierInline" class="rounded-xl bg-sky-600 px-3 font-bold text-white transition hover:bg-sky-500">+</button>
              </div>
            </div>
            <div>
              <InputLabel for="consignee" :value="$t('iran_form_consignee')" class="!text-slate-600 dark:!text-slate-200" />
              <div class="mt-1.5 flex gap-2">
                <select id="consignee" v-model="form.consignee_id" class="block w-full rounded-xl border-slate-300 bg-white text-slate-900 dark:border-slate-600 dark:bg-slate-900 dark:text-white">
                  <option value="">{{ $t("iran_form_choose") }}</option>
                  <option v-for="c in consignees" :key="c.id" :value="c.id">{{ c.name }}</option>
                </select>
                <button type="button" @click="addConsigneeInline" class="rounded-xl bg-sky-600 px-3 font-bold text-white transition hover:bg-sky-500">+</button>
              </div>
            </div>
            <div>
              <InputLabel for="destination" :value="$t('iran_form_destination')" class="!text-slate-600 dark:!text-slate-200" />
              <TextInput id="destination" type="text" class="mt-1.5 block w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white dark:placeholder-slate-400" v-model="form.destination" placeholder="ANZALI HASSAN ROAD" />
            </div>
            <div>
              <InputLabel for="total_price" :value="$t('iran_form_total_optional')" class="!text-slate-600 dark:!text-slate-200" />
              <TextInput id="total_price" type="number" step="0.01" class="mt-1.5 block w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white dark:placeholder-slate-400" v-model="form.total_price" :placeholder="computedTotal !== null ? String(computedTotal) : $t('iran_form_empty')" />
            </div>
          </div>

          <!-- Items table -->
          <div class="mb-3 flex items-center justify-between gap-3">
            <h3 class="text-base font-bold text-slate-900 dark:text-white">{{ $t("iran_form_cars_in_invoice") }}</h3>
            <button type="button" @click="addItem" class="rounded-xl bg-emerald-600 px-3 py-1.5 text-sm font-bold text-white shadow-sm transition hover:bg-emerald-500">{{ $t("iran_form_add_car") }}</button>
          </div>
          <div class="overflow-hidden rounded-2xl border border-slate-200 dark:border-slate-700">
            <div class="overflow-x-auto">
            <table class="w-full text-center text-sm text-slate-700 dark:text-slate-200">
              <thead>
                <tr class="border-b border-slate-200 bg-slate-50 text-[11px] font-bold uppercase tracking-wide text-slate-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300">
                  <th class="px-2 py-3">#</th>
                  <th class="px-2 py-3">{{ $t("iran_col_chassis") }}</th>
                  <th class="px-2 py-3">{{ $t("iran_make") }}</th>
                  <th class="px-2 py-3">{{ $t("iran_model") }}</th>
                  <th class="px-2 py-3">{{ $t("iran_col_year") }}</th>
                  <th class="px-2 py-3">{{ $t("iran_col_color") }}</th>
                  <th class="px-2 py-3">{{ $t("iran_col_weight") }}</th>
                  <th class="px-2 py-3">{{ $t("iran_col_amount") }}</th>
                  <th class="px-2 py-3">{{ $t("iran_notes") }}</th>
                  <th v-if="isEdit" class="px-2 py-3 print:hidden">{{ $t("iran_form_car_photos") }}</th>
                  <th class="px-2 py-3"></th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(item, index) in form.items" :key="item.id || `new-${index}`" class="border-t border-slate-100 dark:border-slate-700/80">
                  <td class="px-2 py-1.5 font-bold tabular-nums">{{ index + 1 }}</td>
                  <td class="px-2 py-1.5"><input v-model="item.chassis_no" class="w-28 rounded-lg border-slate-300 bg-white text-sm text-slate-900 dark:border-slate-600 dark:bg-slate-900 dark:text-white" /></td>
                  <td class="px-2 py-1.5"><input v-model="item.make" class="w-24 rounded-lg border-slate-300 bg-white text-sm text-slate-900 dark:border-slate-600 dark:bg-slate-900 dark:text-white" /></td>
                  <td class="px-2 py-1.5"><input v-model="item.model" class="w-24 rounded-lg border-slate-300 bg-white text-sm text-slate-900 dark:border-slate-600 dark:bg-slate-900 dark:text-white" /></td>
                  <td class="px-2 py-1.5"><input v-model="item.year" class="w-16 rounded-lg border-slate-300 bg-white text-sm text-slate-900 dark:border-slate-600 dark:bg-slate-900 dark:text-white" /></td>
                  <td class="px-2 py-1.5"><input v-model="item.color" class="w-20 rounded-lg border-slate-300 bg-white text-sm text-slate-900 dark:border-slate-600 dark:bg-slate-900 dark:text-white" /></td>
                  <td class="px-2 py-1.5"><input v-model="item.weight" class="w-16 rounded-lg border-slate-300 bg-white text-sm text-slate-900 dark:border-slate-600 dark:bg-slate-900 dark:text-white" /></td>
                  <td class="px-2 py-1.5"><input v-model="item.unit_price" type="number" step="0.01" :placeholder="$t('iran_form_empty')" class="w-20 rounded-lg border-slate-300 bg-white text-sm text-slate-900 dark:border-slate-600 dark:bg-slate-900 dark:text-white dark:placeholder-slate-400" /></td>
                  <td class="px-2 py-1.5"><input v-model="item.notes" class="w-28 rounded-lg border-slate-300 bg-white text-sm text-slate-900 dark:border-slate-600 dark:bg-slate-900 dark:text-white" /></td>
                  <td v-if="isEdit" class="px-2 py-1.5 print:hidden">
                    <div v-if="item.id" class="mx-auto flex max-w-[140px] flex-wrap items-center justify-center gap-1">
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
                          class="h-10 w-10 rounded-lg border border-slate-300 object-cover dark:border-slate-600"
                          @error="(e) => { e.target.src = fileUrl(att.file_name); }"
                        />
                        <span v-else class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-slate-100 text-xs dark:bg-slate-700">📎</span>
                        <button
                          v-if="canDelete"
                          type="button"
                          @click.prevent="deleteItemAttachment(item, att)"
                          class="absolute -right-1 -top-1 h-4 w-4 rounded-full bg-rose-600 text-xs leading-none text-white"
                        >×</button>
                      </a>
                      <label class="flex h-10 w-10 cursor-pointer items-center justify-center rounded-lg bg-sky-600 text-lg text-white transition hover:bg-sky-500" title="إرفاق صور">
                        +
                        <input type="file" class="hidden" accept="image/*,.pdf" multiple @change="uploadItemAttachment(item, $event)" />
                      </label>
                    </div>
                    <span v-else class="text-xs text-slate-400">احفظ أولاً</span>
                  </td>
                  <td class="px-2 py-1.5">
                    <button type="button" @click="removeItem(index)" class="rounded-lg bg-rose-600 px-2.5 py-1 text-xs font-semibold text-white transition hover:bg-rose-500">{{ $t("iran_delete") }}</button>
                  </td>
                </tr>
                <tr v-if="form.items.length === 0">
                  <td :colspan="isEdit ? 11 : 10" class="py-10 text-slate-500 dark:text-slate-400">لا توجد سيارات - اضغط "إضافة سيارة"</td>
                </tr>
              </tbody>
            </table>
            </div>
          </div>

          <div class="mt-4">
            <InputLabel for="notes" value="ملاحظات الفاتورة" class="!text-slate-600 dark:!text-slate-200" />
            <textarea id="notes" v-model="form.notes" rows="2" class="mt-1.5 block w-full rounded-xl border-slate-300 bg-white text-slate-900 dark:border-slate-600 dark:bg-slate-900 dark:text-white"></textarea>
          </div>

          <!-- Attachments (edit only — not included on printed invoice) -->
          <div v-if="isEdit" class="mt-6 print:hidden">
            <div class="mb-3 flex items-center justify-between gap-3">
              <h3 class="text-base font-bold text-slate-900 dark:text-white">المرفقات (صور / ملفات)</h3>
              <label class="cursor-pointer rounded-xl bg-sky-600 px-3 py-1.5 text-sm font-semibold text-white transition hover:bg-sky-500">
                + إرفاق ملف
                <input type="file" class="hidden" accept="image/*,.pdf" multiple @change="uploadAttachment" />
              </label>
            </div>
            <div class="overflow-hidden rounded-2xl border border-slate-200 dark:border-slate-700">
              <div class="overflow-x-auto">
              <table class="w-full text-center text-sm text-slate-700 dark:text-slate-200">
                <thead>
                  <tr class="border-b border-slate-200 bg-slate-50 text-[11px] font-bold uppercase tracking-wide text-slate-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300">
                    <th class="w-20 px-3 py-3">معاينة</th>
                    <th class="px-3 py-3">اسم الملف</th>
                    <th class="w-28 px-3 py-3">تاريخ الرفع</th>
                    <th class="w-24 px-3 py-3">تنفيذ</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-if="attachments.length === 0">
                    <td colspan="4" class="py-10 text-slate-500 dark:text-slate-400">لا توجد مرفقات — اضغط «إرفاق ملف»</td>
                  </tr>
                  <tr
                    v-for="att in attachments"
                    :key="att.id"
                    class="border-t border-slate-100 dark:border-slate-700/80"
                  >
                    <td class="px-3 py-2">
                      <a :href="fileUrl(att.file_name)" target="_blank" class="inline-block">
                        <img
                          v-if="isImageFile(att.file_name)"
                          :src="thumbUrl(att.file_name)"
                          :alt="att.original_name"
                          class="mx-auto h-14 w-14 rounded-lg border border-slate-300 object-cover dark:border-slate-600"
                          @error="(e) => { e.target.src = fileUrl(att.file_name); }"
                        />
                        <span v-else class="text-2xl">📎</span>
                      </a>
                    </td>
                    <td class="px-3 py-2 text-start dark:text-slate-100">
                      <a :href="fileUrl(att.file_name)" target="_blank" class="font-medium text-sky-600 hover:underline dark:text-sky-300">
                        {{ att.original_name || att.file_name }}
                      </a>
                    </td>
                    <td class="px-3 py-2 tabular-nums text-slate-500 dark:text-slate-400">{{ att.created_at ? String(att.created_at).slice(0, 10) : "—" }}</td>
                    <td class="px-3 py-2">
                      <button v-if="canDelete" type="button" @click="deleteAttachment(att)" class="rounded-lg bg-rose-600 px-2.5 py-1.5 text-xs font-semibold text-white transition hover:bg-rose-500">{{ $t("iran_delete") }}</button>
                    </td>
                  </tr>
                </tbody>
              </table>
              </div>
            </div>
          </div>
          <p v-else class="mt-4 text-sm text-slate-500 dark:text-slate-400 print:hidden">
            {{ $t("iran_form_save_first") }}
          </p>

          <div class="mt-6 flex justify-end gap-3">
            <Link :href="route('iranInvoices.index')" class="rounded-xl bg-slate-200 px-5 py-2.5 text-sm font-semibold text-slate-800 transition hover:bg-slate-300 dark:bg-slate-700 dark:text-slate-100 dark:hover:bg-slate-600">{{ $t("iran_cancel") }}</Link>
            <button type="button" @click="save" :disabled="saving" class="rounded-xl bg-emerald-600 px-6 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-emerald-500 disabled:opacity-60">
              {{ saving ? $t("iran_loading") : $t("iran_save") }}
            </button>
          </div>
        </div>
      </div>
    </div>
    </IranPinGate>
  </AuthenticatedLayout>
</template>

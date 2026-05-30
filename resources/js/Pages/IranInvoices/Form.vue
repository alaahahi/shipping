<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, router } from "@inertiajs/inertia-vue3";
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

const form = ref({
  invoice_no: "",
  invoice_date: new Date().toISOString().slice(0, 10),
  carrier_id: "",
  consignee_id: "",
  currency: "USD",
  notes: "",
  total_price: "",
  items: [],
});

const isEdit = computed(() => !!props.invoice_id);

const emptyItem = () => ({
  car_id: null,
  chassis_no: "",
  make: "",
  model: "",
  year: "",
  color: "",
  weight: "",
  unit_price: "",
  notes: "",
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

const fetchInvoice = async () => {
  if (!props.invoice_id) return;
  try {
    const response = await axios.get(`/api/iran-invoices/${props.invoice_id}`);
    const data = response.data;
    form.value = {
      invoice_no: data.invoice_no || "",
      invoice_date: data.invoice_date ? String(data.invoice_date).slice(0, 10) : "",
      carrier_id: data.carrier_id || "",
      consignee_id: data.consignee_id || "",
      currency: data.currency || "USD",
      notes: data.notes || "",
      total_price: data.total_price ?? "",
      items: (data.items || []).map((i) => ({
        car_id: i.car_id,
        chassis_no: i.chassis_no || "",
        make: i.make || "",
        model: i.model || "",
        year: i.year || "",
        color: i.color || "",
        weight: i.weight || "",
        unit_price: i.unit_price ?? "",
        notes: i.notes || "",
      })),
    };
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

const save = async () => {
  if (form.value.items.length === 0) {
    toast.error("أضف سيارة واحدة على الأقل");
    return;
  }
  saving.value = true;
  try {
    const payload = { ...form.value };
    if (isEdit.value) {
      await axios.post(`/api/iran-invoices/${props.invoice_id}`, payload);
      toast.success("تم تحديث الفاتورة");
    } else {
      await axios.post("/api/iran-invoices", payload);
      toast.success("تم حفظ الفاتورة");
    }
    router.visit(route("iranInvoices.index"));
  } catch (error) {
    console.error(error);
    toast.error("تعذر حفظ الفاتورة");
  } finally {
    saving.value = false;
  }
};

onMounted(async () => {
  await fetchLookups();
  if (isEdit.value) {
    await fetchInvoice();
  } else {
    addItem();
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
              <TextInput id="invoice_no" type="text" class="mt-1 block w-full" v-model="form.invoice_no" />
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
                  <th class="px-2 py-2"></th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(item, index) in form.items" :key="index" class="border-t dark:border-gray-700">
                  <td class="px-2 py-1">{{ index + 1 }}</td>
                  <td class="px-2 py-1"><input v-model="item.chassis_no" class="w-28 rounded border-gray-300 dark:bg-gray-700 dark:border-gray-600 text-sm" /></td>
                  <td class="px-2 py-1"><input v-model="item.make" class="w-24 rounded border-gray-300 dark:bg-gray-700 dark:border-gray-600 text-sm" /></td>
                  <td class="px-2 py-1"><input v-model="item.model" class="w-24 rounded border-gray-300 dark:bg-gray-700 dark:border-gray-600 text-sm" /></td>
                  <td class="px-2 py-1"><input v-model="item.year" class="w-16 rounded border-gray-300 dark:bg-gray-700 dark:border-gray-600 text-sm" /></td>
                  <td class="px-2 py-1"><input v-model="item.color" class="w-20 rounded border-gray-300 dark:bg-gray-700 dark:border-gray-600 text-sm" /></td>
                  <td class="px-2 py-1"><input v-model="item.weight" class="w-16 rounded border-gray-300 dark:bg-gray-700 dark:border-gray-600 text-sm" /></td>
                  <td class="px-2 py-1"><input v-model="item.unit_price" type="number" step="0.01" placeholder="فارغ" class="w-20 rounded border-gray-300 dark:bg-gray-700 dark:border-gray-600 text-sm" /></td>
                  <td class="px-2 py-1"><input v-model="item.notes" class="w-28 rounded border-gray-300 dark:bg-gray-700 dark:border-gray-600 text-sm" /></td>
                  <td class="px-2 py-1">
                    <button type="button" @click="removeItem(index)" class="px-2 py-1 bg-red-500 text-white rounded text-xs">حذف</button>
                  </td>
                </tr>
                <tr v-if="form.items.length === 0">
                  <td colspan="10" class="py-6 text-gray-400">لا توجد سيارات - اضغط "إضافة سيارة"</td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="mt-4">
            <InputLabel for="notes" value="ملاحظات الفاتورة" />
            <textarea id="notes" v-model="form.notes" rows="2" class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600"></textarea>
          </div>

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

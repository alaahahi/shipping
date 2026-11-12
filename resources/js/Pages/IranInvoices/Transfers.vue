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
    const response = await iranAxios.get("/api/iran-invoice-transfers", {
      params: { page, q: q.value, from: from.value, to: to.value, archived: showArchived.value ? 1 : 0, limit: 25 },
    });
    transfers.value = response.data;
  } catch (error) {
    toast.error(t("iran_toast_load_transfers_fail"));
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

const openEdit = (row) => {
  editingId.value = row.id;
  form.value = {
    transfer_date: row.transfer_date ? String(row.transfer_date).slice(0, 10) : "",
    amount: row.amount ?? "",
    currency: row.currency || "USD",
    reference_no: row.reference_no || "",
    from_text: row.from_text || "",
    to_text: row.to_text || "",
    notes: row.notes || "",
  };
  showForm.value = true;
};

const saveTransfer = async () => {
  try {
    if (editingId.value) {
      await iranAxios.post(`/api/iran-invoice-transfers/${editingId.value}`, form.value);
      toast.success(t("iran_toast_transfer_updated"));
    } else {
      await iranAxios.post("/api/iran-invoice-transfers", form.value);
      toast.success(t("iran_toast_transfer_added"));
    }
    showForm.value = false;
    resetForm();
    fetchTransfers();
  } catch (error) {
    toast.error(t("iran_toast_transfer_save_fail"));
  }
};

const archiveTransfer = async (row) => {
  try {
    await iranAxios.post(`/api/iran-invoice-transfers/${row.id}/archive`);
    fetchTransfers();
  } catch (error) {
    toast.error(t("iran_toast_archive_generic_fail"));
  }
};

const deleteTransfer = async (row) => {
  if (!confirm(t("iran_confirm_delete_transfer"))) return;
  try {
    await iranAxios.post(`/api/iran-invoice-transfers/${row.id}/delete`);
    toast.success(t("iran_toast_transfer_deleted"));
    fetchTransfers();
  } catch (error) {
    toast.error(error.response?.data?.message || t("iran_toast_transfer_delete_fail"));
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
          <InputLabel for="t_search" :value="$t('iran_search')" />
          <TextInput id="t_search" type="text" class="mt-1 block w-full" v-model="q" :placeholder="$t('iran_search_transfer_ph')" />
        </div>
        <div>
          <InputLabel for="t_from" :value="$t('iran_from_date')" />
          <TextInput id="t_from" type="date" class="mt-1 block w-full" v-model="from" />
        </div>
        <div>
          <InputLabel for="t_to" :value="$t('iran_to_date')" />
          <TextInput id="t_to" type="date" class="mt-1 block w-full" v-model="to" />
        </div>
        <div class="flex items-center gap-2 pt-6">
          <input id="t_archived" type="checkbox" v-model="showArchived" class="rounded border-gray-300" />
          <label for="t_archived" class="text-sm text-gray-700 dark:text-gray-300">{{ $t("iran_archived") }}</label>
        </div>
        <div class="pt-6">
          <button @click="openCreate" class="block w-full text-center px-4 py-2 font-bold text-white bg-green-600 rounded hover:bg-green-700">
            {{ $t("iran_new_transfer") }}
          </button>
        </div>
      </div>
    </div>

    <div v-if="showForm" class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 mb-4">
      <h3 class="font-semibold mb-3 dark:text-gray-200">{{ editingId ? $t("iran_edit_transfer") : $t("iran_new_transfer_title") }}</h3>
      <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <div><InputLabel :value="$t('iran_col_date')" /><TextInput type="date" class="mt-1 block w-full" v-model="form.transfer_date" /></div>
        <div><InputLabel :value="$t('iran_col_amount')" /><TextInput type="number" step="0.01" class="mt-1 block w-full" v-model="form.amount" /></div>
        <div>
          <InputLabel :value="$t('iran_currency')" />
          <select v-model="form.currency" class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600">
            <option value="USD">USD</option>
            <option value="IQD">IQD</option>
            <option value="AED">AED</option>
            <option value="IRR">IRR</option>
          </select>
        </div>
        <div><InputLabel :value="$t('iran_reference_no')" /><TextInput type="text" class="mt-1 block w-full" v-model="form.reference_no" /></div>
        <div><InputLabel :value="$t('iran_col_from')" /><TextInput type="text" class="mt-1 block w-full" v-model="form.from_text" /></div>
        <div><InputLabel :value="$t('iran_col_to')" /><TextInput type="text" class="mt-1 block w-full" v-model="form.to_text" /></div>
        <div class="md:col-span-2"><InputLabel :value="$t('iran_notes')" /><TextInput type="text" class="mt-1 block w-full" v-model="form.notes" /></div>
      </div>
      <div class="mt-4 flex justify-end gap-2">
        <button @click="showForm = false" class="px-4 py-2 bg-gray-300 dark:bg-gray-600 rounded">{{ $t("iran_cancel") }}</button>
        <button @click="saveTransfer" class="px-5 py-2 bg-green-600 text-white font-bold rounded">{{ $t("iran_save") }}</button>
      </div>
    </div>

    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-x-auto">
      <table class="w-full text-sm text-center text-gray-600 dark:text-gray-300">
        <thead class="bg-gray-50 dark:bg-gray-900 text-xs uppercase">
          <tr>
            <th class="px-3 py-3">{{ $t("iran_col_date") }}</th>
            <th class="px-3 py-3">{{ $t("iran_col_amount") }}</th>
            <th class="px-3 py-3">{{ $t("iran_col_reference") }}</th>
            <th class="px-3 py-3">{{ $t("iran_col_from") }}</th>
            <th class="px-3 py-3">{{ $t("iran_col_to") }}</th>
            <th class="px-3 py-3">{{ $t("iran_col_invoice") }}</th>
            <th class="px-3 py-3">{{ $t("iran_col_actions") }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading"><td colspan="7" class="py-8 text-gray-400">{{ $t("iran_loading") }}</td></tr>
          <tr v-else-if="!transfers.data || transfers.data.length === 0"><td colspan="7" class="py-8 text-gray-400">{{ $t("iran_no_transfers") }}</td></tr>
          <tr v-for="row in transfers.data" :key="row.id" class="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700">
            <td class="px-3 py-2">{{ row.transfer_date }}</td>
            <td class="px-3 py-2 font-semibold">{{ formatAmount(row.amount) }} {{ row.currency }}</td>
            <td class="px-3 py-2">{{ row.reference_no || "—" }}</td>
            <td class="px-3 py-2">{{ row.from_text || "—" }}</td>
            <td class="px-3 py-2">{{ row.to_text || "—" }}</td>
            <td class="px-3 py-2">{{ row.invoice?.invoice_no || "—" }}</td>
            <td class="px-3 py-2">
              <div class="flex justify-center gap-1">
                <button @click="openEdit(row)" class="px-2 py-1 bg-blue-500 text-white rounded text-xs">{{ $t("iran_edit") }}</button>
                <button @click="archiveTransfer(row)" class="px-2 py-1 bg-amber-500 text-white rounded text-xs">
                  {{ row.is_archived ? $t("iran_restore") : $t("iran_archive") }}
                </button>
                <button v-if="canDelete" @click="deleteTransfer(row)" class="px-2 py-1 bg-red-500 text-white rounded text-xs">{{ $t("iran_delete") }}</button>
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

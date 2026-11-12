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
    <div class="mb-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-800 sm:p-5">
      <div class="grid grid-cols-1 items-end gap-3 md:grid-cols-5">
        <div>
          <InputLabel for="t_search" :value="$t('iran_search')" class="!text-slate-600 dark:!text-slate-200" />
          <TextInput id="t_search" type="text" class="mt-1.5 block w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white dark:placeholder-slate-400" v-model="q" :placeholder="$t('iran_search_transfer_ph')" />
        </div>
        <div>
          <InputLabel for="t_from" :value="$t('iran_from_date')" class="!text-slate-600 dark:!text-slate-200" />
          <TextInput id="t_from" type="date" class="mt-1.5 block w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white" v-model="from" />
        </div>
        <div>
          <InputLabel for="t_to" :value="$t('iran_to_date')" class="!text-slate-600 dark:!text-slate-200" />
          <TextInput id="t_to" type="date" class="mt-1.5 block w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white" v-model="to" />
        </div>
        <div class="flex items-center gap-2.5 pt-2 md:pt-7">
          <input id="t_archived" type="checkbox" v-model="showArchived" class="h-4 w-4 rounded border-slate-400 text-emerald-600 focus:ring-emerald-500 dark:border-slate-500 dark:bg-slate-900" />
          <label for="t_archived" class="text-sm font-medium text-slate-700 dark:text-slate-200">{{ $t("iran_archived") }}</label>
        </div>
        <div class="pt-1 md:pt-6">
          <button type="button" @click="openCreate" class="inline-flex w-full items-center justify-center rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-emerald-500">
            {{ $t("iran_new_transfer") }}
          </button>
        </div>
      </div>
    </div>

    <div v-if="showForm" class="mb-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-800 sm:p-5">
      <h3 class="mb-3 text-base font-bold text-slate-900 dark:text-white">{{ editingId ? $t("iran_edit_transfer") : $t("iran_new_transfer_title") }}</h3>
      <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
        <div><InputLabel :value="$t('iran_col_date')" class="!text-slate-600 dark:!text-slate-200" /><TextInput type="date" class="mt-1.5 block w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white" v-model="form.transfer_date" /></div>
        <div><InputLabel :value="$t('iran_col_amount')" class="!text-slate-600 dark:!text-slate-200" /><TextInput type="number" step="0.01" class="mt-1.5 block w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white" v-model="form.amount" /></div>
        <div>
          <InputLabel :value="$t('iran_currency')" class="!text-slate-600 dark:!text-slate-200" />
          <select v-model="form.currency" class="mt-1.5 block w-full rounded-xl border-slate-300 bg-white text-slate-900 dark:border-slate-600 dark:bg-slate-900 dark:text-white">
            <option value="USD">USD</option>
            <option value="IQD">IQD</option>
            <option value="AED">AED</option>
            <option value="IRR">IRR</option>
          </select>
        </div>
        <div><InputLabel :value="$t('iran_reference_no')" class="!text-slate-600 dark:!text-slate-200" /><TextInput type="text" class="mt-1.5 block w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white" v-model="form.reference_no" /></div>
        <div><InputLabel :value="$t('iran_col_from')" class="!text-slate-600 dark:!text-slate-200" /><TextInput type="text" class="mt-1.5 block w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white" v-model="form.from_text" /></div>
        <div><InputLabel :value="$t('iran_col_to')" class="!text-slate-600 dark:!text-slate-200" /><TextInput type="text" class="mt-1.5 block w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white" v-model="form.to_text" /></div>
        <div class="md:col-span-2"><InputLabel :value="$t('iran_notes')" class="!text-slate-600 dark:!text-slate-200" /><TextInput type="text" class="mt-1.5 block w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white" v-model="form.notes" /></div>
      </div>
      <div class="mt-4 flex justify-end gap-2">
        <button type="button" @click="showForm = false" class="rounded-xl bg-slate-200 px-4 py-2 text-sm font-semibold text-slate-800 transition hover:bg-slate-300 dark:bg-slate-700 dark:text-slate-100 dark:hover:bg-slate-600">{{ $t("iran_cancel") }}</button>
        <button type="button" @click="saveTransfer" class="rounded-xl bg-emerald-600 px-5 py-2 text-sm font-bold text-white shadow-sm transition hover:bg-emerald-500">{{ $t("iran_save") }}</button>
      </div>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
      <div class="overflow-x-auto">
      <table class="w-full text-sm text-center text-slate-700 dark:text-slate-200">
        <thead>
          <tr class="border-b border-slate-200 bg-slate-50 text-[11px] font-bold uppercase tracking-wide text-slate-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300">
            <th class="px-3 py-3.5">{{ $t("iran_col_date") }}</th>
            <th class="px-3 py-3.5">{{ $t("iran_col_amount") }}</th>
            <th class="px-3 py-3.5">{{ $t("iran_col_reference") }}</th>
            <th class="px-3 py-3.5">{{ $t("iran_col_from") }}</th>
            <th class="px-3 py-3.5">{{ $t("iran_col_to") }}</th>
            <th class="px-3 py-3.5">{{ $t("iran_col_invoice") }}</th>
            <th class="px-3 py-3.5">{{ $t("iran_col_actions") }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading"><td colspan="7" class="py-12 text-slate-400">{{ $t("iran_loading") }}</td></tr>
          <tr v-else-if="!transfers.data || transfers.data.length === 0"><td colspan="7" class="py-12 text-slate-500 dark:text-slate-400">{{ $t("iran_no_transfers") }}</td></tr>
          <tr v-for="row in transfers.data" :key="row.id" class="border-b border-slate-100 transition-colors last:border-0 hover:bg-slate-50/80 dark:border-slate-700/80 dark:hover:bg-slate-900/50">
            <td class="px-3 py-3 tabular-nums">{{ row.transfer_date }}</td>
            <td class="px-3 py-3 font-bold tabular-nums text-emerald-700 dark:text-emerald-300">{{ formatAmount(row.amount) }} {{ row.currency }}</td>
            <td class="px-3 py-3">{{ row.reference_no || "—" }}</td>
            <td class="px-3 py-3">{{ row.from_text || "—" }}</td>
            <td class="px-3 py-3">{{ row.to_text || "—" }}</td>
            <td class="px-3 py-3 font-semibold tabular-nums">{{ row.invoice?.invoice_no || "—" }}</td>
            <td class="px-3 py-3">
              <div class="flex flex-wrap justify-center gap-1.5">
                <button type="button" @click="openEdit(row)" class="rounded-lg bg-sky-600 px-2.5 py-1.5 text-xs font-semibold text-white transition hover:bg-sky-500">{{ $t("iran_edit") }}</button>
                <button type="button" @click="archiveTransfer(row)" class="rounded-lg bg-amber-500 px-2.5 py-1.5 text-xs font-semibold text-white transition hover:bg-amber-400">
                  {{ row.is_archived ? $t("iran_restore") : $t("iran_archive") }}
                </button>
                <button v-if="canDelete" type="button" @click="deleteTransfer(row)" class="rounded-lg bg-rose-600 px-2.5 py-1.5 text-xs font-semibold text-white transition hover:bg-rose-500">{{ $t("iran_delete") }}</button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
      </div>
    </div>

    <div class="mt-4 text-center" style="direction: ltr">
      <TailwindPagination v-if="transfers.data && transfers.data.length" :data="transfers" @pagination-change-page="fetchTransfers" :limit="2" />
    </div>
  </div>
</template>

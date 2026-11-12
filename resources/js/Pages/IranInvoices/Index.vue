<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link } from "@inertiajs/inertia-vue3";
import { TailwindPagination } from "laravel-vue-pagination";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import IranPinGate from "@/Components/IranPinGate.vue";
import { ref, watch, provide, computed } from "vue";
import { useToast } from "vue-toastification";
import { useI18n } from "vue-i18n";
import IranInvoiceCars from "./Cars.vue";
import IranInvoiceTransfers from "./Transfers.vue";
import IranDamageReports from "./DamageReports.vue";
import { useIranPageAccess } from "@/composables/useIranPageAccess";

const props = defineProps({
  owner_id: Number,
  iran_unlocked: { type: Boolean, default: false },
  iran_can_delete: { type: Boolean, default: false },
  iran_is_admin: { type: Boolean, default: false },
  iran_token_guest: { type: Boolean, default: false },
});

const { t } = useI18n();
const { unlocked, canDelete, pin, pinError, unlocking, iranAxios } = useIranPageAccess(props);
provide("iranAxios", iranAxios);
provide("iranCanDelete", canDelete);

const toast = useToast();

/** Token-guest (أدمن التوكن): hide cars/transfers registry tabs. */
const isTokenGuest = computed(() => !!props.iran_token_guest);
const showRegistryTabs = computed(() => !isTokenGuest.value);

const activeTab = ref("invoices"); // invoices | cars | transfers | damage
const showArchived = ref(false);

const invoices = ref({});
const q = ref("");
const from = ref("");
const to = ref("");
const loading = ref(false);

const fetchInvoices = async (page = 1) => {
  loading.value = true;
  try {
    const response = await iranAxios.get("/api/iran-invoices", {
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
    toast.error(t("iran_toast_load_invoices_fail"));
  } finally {
    loading.value = false;
  }
};

const archiveInvoice = async (invoice) => {
  try {
    await iranAxios.post(`/api/iran-invoices/${invoice.id}/archive`);
    toast.success(t("iran_toast_archived"));
    fetchInvoices();
  } catch (error) {
    toast.error(t("iran_toast_archive_fail"));
  }
};

const unarchiveInvoice = async (invoice) => {
  try {
    await iranAxios.post(`/api/iran-invoices/${invoice.id}/unarchive`);
    toast.success(t("iran_toast_unarchived"));
    fetchInvoices();
  } catch (error) {
    toast.error(t("iran_toast_unarchive_fail"));
  }
};

const deleteInvoice = async (invoice) => {
  if (!confirm(t("iran_confirm_delete_invoice", { no: invoice.invoice_no || invoice.id }))) return;
  try {
    await iranAxios.post(`/api/iran-invoices/${invoice.id}/delete`);
    toast.success(t("iran_toast_deleted"));
    fetchInvoices();
  } catch (error) {
    toast.error(error.response?.data?.message || t("iran_toast_delete_fail"));
  }
};

const formatPrice = (value) => {
  if (value === null || value === undefined || value === "") return "—";
  return Number(value).toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

const fileUrl = (name) => (name ? `/public/uploads/${encodeURIComponent(name)}` : "");

const uploadAttachment = async (invoice, event) => {
  const file = event.target.files[0];
  if (!file) return;
  const data = new FormData();
  data.append("type", "invoice");
  data.append("id", invoice.id);
  data.append("file", file);
  try {
    const response = await iranAxios.post("/api/iran-invoice-attachments", data, {
      headers: { "Content-Type": "multipart/form-data" },
    });
    if (!invoice.attachments) invoice.attachments = [];
    invoice.attachments.push(response.data);
    toast.success(t("iran_toast_attach_ok"));
  } catch (error) {
    toast.error(t("iran_toast_attach_fail"));
  } finally {
    event.target.value = "";
  }
};

const deleteAttachment = async (invoice, attachment) => {
  if (!confirm(t("iran_confirm_delete_attach"))) return;
  try {
    await iranAxios.post("/api/iran-invoice-attachments/delete", { id: attachment.id });
    invoice.attachments = (invoice.attachments || []).filter((a) => a.id !== attachment.id);
    toast.success(t("iran_toast_attach_deleted"));
  } catch (error) {
    toast.error(error.response?.data?.message || t("iran_toast_attach_delete_fail"));
  }
};

watch([showArchived, q, from, to], () => {
  if (unlocked.value && activeTab.value === "invoices") {
    fetchInvoices();
  }
});

watch(activeTab, (tab) => {
  if (unlocked.value && tab === "invoices") {
    fetchInvoices();
  }
  if (!showRegistryTabs.value && (tab === "cars" || tab === "transfers")) {
    activeTab.value = "damage";
  }
});

watch(showRegistryTabs, (allowed) => {
  if (!allowed && (activeTab.value === "cars" || activeTab.value === "transfers")) {
    activeTab.value = "damage";
  }
});

watch(
  unlocked,
  (isUnlocked) => {
    if (isUnlocked && activeTab.value === "invoices") {
      fetchInvoices();
    }
  },
  { immediate: true }
);
</script>

<template>
  <Head :title="$t('iran_head_list')" />
  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
        {{ $t("iran_invoices_title") }}
      </h2>
    </template>

    <IranPinGate :unlocked="unlocked" :pin="pin" :error="pinError" :unlocking="unlocking" @update:pin="pin = $event">
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
              {{ $t("iran_tab_invoices") }}
            </button>
            <button
              v-if="showRegistryTabs"
              @click="activeTab = 'cars'"
              :class="[
                activeTab === 'cars'
                  ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400',
                'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm',
              ]"
            >
              {{ $t("iran_tab_cars") }}
            </button>
            <button
              v-if="showRegistryTabs"
              @click="activeTab = 'transfers'"
              :class="[
                activeTab === 'transfers'
                  ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400',
                'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm',
              ]"
            >
              {{ $t("iran_tab_transfers") }}
            </button>
            <button
              @click="activeTab = 'damage'"
              :class="[
                activeTab === 'damage'
                  ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400',
                'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm',
              ]"
            >
              {{ $t("iran_tab_damage") }}
            </button>
          </nav>
        </div>

        <!-- Invoices Tab -->
        <div v-show="activeTab === 'invoices'">
          <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 mb-4">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-3 items-end">
              <div>
                <InputLabel for="search" :value="$t('iran_search')" />
                <TextInput id="search" type="text" class="mt-1 block w-full" v-model="q" :placeholder="$t('iran_search_invoice_ph')" />
              </div>
              <div>
                <InputLabel for="from" :value="$t('iran_from_date')" />
                <TextInput id="from" type="date" class="mt-1 block w-full" v-model="from" />
              </div>
              <div>
                <InputLabel for="to" :value="$t('iran_to_date')" />
                <TextInput id="to" type="date" class="mt-1 block w-full" v-model="to" />
              </div>
              <div class="flex items-center gap-2 pt-6">
                <input id="archived" type="checkbox" v-model="showArchived" class="rounded border-gray-300" />
                <label for="archived" class="text-sm text-gray-700 dark:text-gray-300">{{ $t("iran_show_archived") }}</label>
              </div>
              <div class="pt-6">
                <Link
                  :href="route('iranInvoices.create')"
                  class="block text-center px-4 py-2 font-bold text-white bg-green-600 rounded hover:bg-green-700"
                >
                  {{ $t("iran_new_invoice") }}
                </Link>
              </div>
            </div>
          </div>

          <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-x-auto">
            <table class="w-full text-sm text-center text-gray-600 dark:text-gray-300">
              <thead class="bg-gray-50 dark:bg-gray-900 text-xs uppercase">
                <tr>
                  <th class="px-3 py-3">{{ $t("iran_col_invoice_no") }}</th>
                  <th class="px-3 py-3">{{ $t("iran_col_date") }}</th>
                  <th class="px-3 py-3">{{ $t("iran_col_carrier") }}</th>
                  <th class="px-3 py-3">{{ $t("iran_col_consignee") }}</th>
                  <th class="px-3 py-3">{{ $t("iran_col_cars") }}</th>
                  <th class="px-3 py-3">{{ $t("iran_col_total") }}</th>
                  <th class="px-3 py-3">{{ $t("iran_col_created_by") }}</th>
                  <th class="px-3 py-3 print:hidden">{{ $t("iran_col_attachments") }}</th>
                  <th class="px-3 py-3 print:hidden">{{ $t("iran_col_actions") }}</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="loading">
                  <td colspan="9" class="py-8 text-gray-400">{{ $t("iran_loading") }}</td>
                </tr>
                <tr v-else-if="!invoices.data || invoices.data.length === 0">
                  <td colspan="9" class="py-8 text-gray-400">{{ $t("iran_no_invoices") }}</td>
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
                  <td class="px-3 py-2">{{ invoice.created_by_name || invoice.creator?.name || "—" }}</td>
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
                        <button v-if="canDelete" @click.prevent="deleteAttachment(invoice, att)" class="text-red-600 font-bold">×</button>
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
                      >{{ $t("iran_print") }}</a>
                      <Link
                        :href="route('iranInvoices.edit', invoice.id)"
                        class="px-2 py-1 text-white bg-blue-500 rounded text-xs"
                      >{{ $t("iran_edit") }}</Link>
                      <button
                        v-if="!showArchived"
                        @click="archiveInvoice(invoice)"
                        class="px-2 py-1 text-white bg-amber-500 rounded text-xs"
                      >{{ $t("iran_archive") }}</button>
                      <button
                        v-else
                        @click="unarchiveInvoice(invoice)"
                        class="px-2 py-1 text-white bg-teal-500 rounded text-xs"
                      >{{ $t("iran_restore") }}</button>
                      <button
                        v-if="canDelete"
                        @click="deleteInvoice(invoice)"
                        class="px-2 py-1 text-white bg-red-500 rounded text-xs"
                      >{{ $t("iran_delete") }}</button>
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

        <!-- Cars Tab (hidden for token guest / أدمن التوكن) -->
        <IranInvoiceCars v-if="showRegistryTabs && activeTab === 'cars'" />

        <!-- Transfers Tab (hidden for token guest / أدمن التوكن) -->
        <IranInvoiceTransfers v-if="showRegistryTabs && activeTab === 'transfers'" />

        <!-- Damage reports tab (replacement for token-guest registry tabs) -->
        <IranDamageReports v-if="activeTab === 'damage'" />
      </div>
    </div>
    </IranPinGate>
  </AuthenticatedLayout>
</template>

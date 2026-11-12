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

const tabClass = (active) =>
  [
    "whitespace-nowrap rounded-xl px-4 py-2.5 text-sm font-semibold transition",
    active
      ? "bg-white text-slate-900 shadow-sm dark:bg-slate-700 dark:text-white"
      : "text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-100",
  ].join(" ");

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
      <div>
        <h2 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white">
          {{ $t("iran_invoices_title") }}
        </h2>
        <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">
          {{ $t("iran_tab_invoices") }} · {{ $t("iran_tab_damage") }}
        </p>
      </div>
    </template>

    <IranPinGate :unlocked="unlocked" :pin="pin" :error="pinError" :unlocking="unlocking" @update:pin="pin = $event">
    <div class="py-6 sm:py-8">
      <div class="mx-auto max-w-7xl space-y-5 px-4 sm:px-6 lg:px-8">
        <!-- Tabs -->
        <div class="overflow-x-auto">
          <nav
            class="inline-flex min-w-full gap-1 rounded-2xl border border-slate-200 bg-slate-100/80 p-1 dark:border-slate-700 dark:bg-slate-900/80 sm:min-w-0"
            role="tablist"
          >
            <button
              type="button"
              role="tab"
              :aria-selected="activeTab === 'invoices'"
              @click="activeTab = 'invoices'"
              :class="tabClass(activeTab === 'invoices')"
            >
              {{ $t("iran_tab_invoices") }}
            </button>
            <button
              v-if="showRegistryTabs"
              type="button"
              role="tab"
              :aria-selected="activeTab === 'cars'"
              @click="activeTab = 'cars'"
              :class="tabClass(activeTab === 'cars')"
            >
              {{ $t("iran_tab_cars") }}
            </button>
            <button
              v-if="showRegistryTabs"
              type="button"
              role="tab"
              :aria-selected="activeTab === 'transfers'"
              @click="activeTab = 'transfers'"
              :class="tabClass(activeTab === 'transfers')"
            >
              {{ $t("iran_tab_transfers") }}
            </button>
            <button
              type="button"
              role="tab"
              :aria-selected="activeTab === 'damage'"
              @click="activeTab = 'damage'"
              :class="tabClass(activeTab === 'damage')"
            >
              {{ $t("iran_tab_damage") }}
            </button>
          </nav>
        </div>

        <!-- Invoices Tab -->
        <div v-show="activeTab === 'invoices'" class="space-y-4">
          <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-800 sm:p-5">
            <div class="grid grid-cols-1 items-end gap-3 md:grid-cols-5">
              <div>
                <InputLabel for="search" :value="$t('iran_search')" class="!text-slate-600 dark:!text-slate-200" />
                <TextInput
                  id="search"
                  type="text"
                  class="mt-1.5 block w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white dark:placeholder-slate-400"
                  v-model="q"
                  :placeholder="$t('iran_search_invoice_ph')"
                />
              </div>
              <div>
                <InputLabel for="from" :value="$t('iran_from_date')" class="!text-slate-600 dark:!text-slate-200" />
                <TextInput
                  id="from"
                  type="date"
                  class="mt-1.5 block w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white"
                  v-model="from"
                />
              </div>
              <div>
                <InputLabel for="to" :value="$t('iran_to_date')" class="!text-slate-600 dark:!text-slate-200" />
                <TextInput
                  id="to"
                  type="date"
                  class="mt-1.5 block w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white"
                  v-model="to"
                />
              </div>
              <div class="flex items-center gap-2.5 pt-2 md:pt-7">
                <input
                  id="archived"
                  type="checkbox"
                  v-model="showArchived"
                  class="h-4 w-4 rounded border-slate-400 text-emerald-600 focus:ring-emerald-500 dark:border-slate-500 dark:bg-slate-900"
                />
                <label for="archived" class="text-sm font-medium text-slate-700 dark:text-slate-200">
                  {{ $t("iran_show_archived") }}
                </label>
              </div>
              <div class="pt-1 md:pt-6">
                <Link
                  :href="route('iranInvoices.create')"
                  class="inline-flex w-full items-center justify-center rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-emerald-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-500"
                >
                  {{ $t("iran_new_invoice") }}
                </Link>
              </div>
            </div>
          </div>

          <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
            <div class="overflow-x-auto">
              <table class="w-full min-w-[920px] text-sm text-center text-slate-700 dark:text-slate-200">
                <thead>
                  <tr class="border-b border-slate-200 bg-slate-50 text-[11px] font-bold uppercase tracking-wide text-slate-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300">
                    <th class="px-3 py-3.5">{{ $t("iran_col_invoice_no") }}</th>
                    <th class="px-3 py-3.5">{{ $t("iran_col_date") }}</th>
                    <th class="px-3 py-3.5">{{ $t("iran_col_carrier") }}</th>
                    <th class="px-3 py-3.5">{{ $t("iran_col_consignee") }}</th>
                    <th class="px-3 py-3.5">{{ $t("iran_col_cars") }}</th>
                    <th class="px-3 py-3.5">{{ $t("iran_col_total") }}</th>
                    <th class="px-3 py-3.5">{{ $t("iran_col_created_by") }}</th>
                    <th class="px-3 py-3.5 print:hidden">{{ $t("iran_col_attachments") }}</th>
                    <th class="px-3 py-3.5 print:hidden">{{ $t("iran_col_actions") }}</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-if="loading">
                    <td colspan="9" class="px-4 py-14">
                      <div class="mx-auto flex max-w-xs flex-col items-center gap-3 text-slate-400 dark:text-slate-500">
                        <span class="iran-spinner h-8 w-8 rounded-full border-2 border-slate-300 border-t-emerald-500 dark:border-slate-600 dark:border-t-emerald-400" />
                        <span class="text-sm font-medium">{{ $t("iran_loading") }}</span>
                      </div>
                    </td>
                  </tr>
                  <tr v-else-if="!invoices.data || invoices.data.length === 0">
                    <td colspan="9" class="px-4 py-14">
                      <div class="mx-auto max-w-sm text-center">
                        <p class="text-base font-semibold text-slate-700 dark:text-slate-200">{{ $t("iran_no_invoices") }}</p>
                        <Link
                          :href="route('iranInvoices.create')"
                          class="mt-4 inline-flex rounded-xl bg-emerald-600 px-4 py-2 text-sm font-bold text-white transition hover:bg-emerald-500"
                        >
                          {{ $t("iran_new_invoice") }}
                        </Link>
                      </div>
                    </td>
                  </tr>
                  <tr
                    v-for="invoice in invoices.data"
                    :key="invoice.id"
                    class="border-b border-slate-100 transition-colors last:border-0 hover:bg-slate-50/80 dark:border-slate-700/80 dark:hover:bg-slate-900/50"
                  >
                    <td class="px-3 py-3 font-bold tabular-nums text-slate-900 dark:text-white">{{ invoice.invoice_no }}</td>
                    <td class="px-3 py-3 tabular-nums">{{ invoice.invoice_date }}</td>
                    <td class="max-w-[10rem] truncate px-3 py-3" :title="invoice.carrier_name || invoice.carrier?.name || ''">
                      {{ invoice.carrier_name || invoice.carrier?.name || "—" }}
                    </td>
                    <td class="max-w-[10rem] truncate px-3 py-3" :title="invoice.consignee_name || invoice.consignee?.name || ''">
                      {{ invoice.consignee_name || invoice.consignee?.name || "—" }}
                    </td>
                    <td class="px-3 py-3">
                      <span class="inline-flex min-w-[1.75rem] justify-center rounded-lg bg-slate-100 px-2 py-0.5 text-xs font-bold tabular-nums dark:bg-slate-700">
                        {{ invoice.items_count }}
                      </span>
                    </td>
                    <td class="px-3 py-3 font-semibold tabular-nums text-slate-900 dark:text-slate-100">
                      {{ formatPrice(invoice.total_price) }}
                      <span class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ invoice.currency }}</span>
                    </td>
                    <td class="px-3 py-3 text-slate-600 dark:text-slate-300">
                      {{ invoice.created_by_name || invoice.creator?.name || "—" }}
                    </td>
                    <td class="px-3 py-3 print:hidden">
                      <div class="flex flex-wrap items-center justify-center gap-1.5">
                        <a
                          v-for="att in (invoice.attachments || [])"
                          :key="att.id"
                          :href="fileUrl(att.file_name)"
                          target="_blank"
                          class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-slate-50 px-2 py-1 text-xs dark:border-slate-600 dark:bg-slate-900"
                        >
                          📎
                          <button
                            v-if="canDelete"
                            type="button"
                            @click.prevent="deleteAttachment(invoice, att)"
                            class="font-bold text-rose-500 hover:text-rose-400"
                          >×</button>
                        </a>
                        <label class="cursor-pointer rounded-lg bg-sky-600 px-2.5 py-1 text-xs font-bold text-white transition hover:bg-sky-500">
                          +
                          <input type="file" class="hidden" accept="image/*,.pdf" @change="uploadAttachment(invoice, $event)" />
                        </label>
                      </div>
                    </td>
                    <td class="px-3 py-3 print:hidden">
                      <div class="flex flex-wrap justify-center gap-1.5">
                        <a
                          :href="route('iranInvoices.print', invoice.id)"
                          target="_blank"
                          class="rounded-lg bg-slate-800 px-2.5 py-1.5 text-xs font-semibold text-white transition hover:bg-slate-700 dark:bg-slate-700 dark:hover:bg-slate-600"
                        >{{ $t("iran_print") }}</a>
                        <Link
                          :href="route('iranInvoices.edit', invoice.id)"
                          class="rounded-lg bg-sky-600 px-2.5 py-1.5 text-xs font-semibold text-white transition hover:bg-sky-500"
                        >{{ $t("iran_edit") }}</Link>
                        <button
                          v-if="!showArchived"
                          type="button"
                          @click="archiveInvoice(invoice)"
                          class="rounded-lg bg-amber-500 px-2.5 py-1.5 text-xs font-semibold text-white transition hover:bg-amber-400"
                        >{{ $t("iran_archive") }}</button>
                        <button
                          v-else
                          type="button"
                          @click="unarchiveInvoice(invoice)"
                          class="rounded-lg bg-teal-600 px-2.5 py-1.5 text-xs font-semibold text-white transition hover:bg-teal-500"
                        >{{ $t("iran_restore") }}</button>
                        <button
                          v-if="canDelete"
                          type="button"
                          @click="deleteInvoice(invoice)"
                          class="rounded-lg bg-rose-600 px-2.5 py-1.5 text-xs font-semibold text-white transition hover:bg-rose-500"
                        >{{ $t("iran_delete") }}</button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <div class="text-center" style="direction: ltr">
            <TailwindPagination
              v-if="invoices.data && invoices.data.length"
              :data="invoices"
              @pagination-change-page="fetchInvoices"
              :limit="2"
            />
          </div>
        </div>

        <IranInvoiceCars v-if="showRegistryTabs && activeTab === 'cars'" />
        <IranInvoiceTransfers v-if="showRegistryTabs && activeTab === 'transfers'" />
        <IranDamageReports v-if="activeTab === 'damage'" />
      </div>
    </div>
    </IranPinGate>
  </AuthenticatedLayout>
</template>

<style scoped>
.iran-spinner {
  animation: iran-spin 0.7s linear infinite;
}
@keyframes iran-spin {
  to {
    transform: rotate(360deg);
  }
}
</style>

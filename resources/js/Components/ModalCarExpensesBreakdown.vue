<script setup>
import { ref, watch, computed } from "vue";
import axios from "axios";
import { useToast } from "vue-toastification";
import Modal from "@/Components/Modal.vue";
import CarExpenseLinesEditor from "@/Components/CarExpenseLinesEditor.vue";
import { seedExpenseBreakdownFromLegacy } from "@/utils/seedExpenseBreakdown";

const props = defineProps({
  show: Boolean,
  car: {
    type: Object,
    default: null,
  },
  mode: {
    type: String,
    default: "sales",
    validator: (v) => ["sales", "purchase"].includes(v),
  },
});

const emit = defineEmits(["close", "saved"]);

const toast = useToast();
const form = ref(null);
const saving = ref(false);
const useExpenseLines = ref(false);
const breakdownDirty = ref(false);

const displayAmount = computed(() => {
  if (!form.value) return 0;
  return props.mode === "sales"
    ? Number(form.value.expenses_s) || 0
    : Number(form.value.expenses) || 0;
});

const carTitle = computed(() => {
  if (!form.value) return "";
  return [form.value.car_type, form.value.vin, form.value.year]
    .filter(Boolean)
    .join(" — ");
});

function resetFromCar() {
  if (!props.car) {
    form.value = null;
    return;
  }
  form.value = {
    ...props.car,
    expenses_breakdown: Array.isArray(props.car.expenses_breakdown)
      ? JSON.parse(JSON.stringify(props.car.expenses_breakdown))
      : [],
  };
  delete form.value._expenseBreakdownSeeded;
  seedExpenseBreakdownFromLegacy(form.value, props.mode);
  if (!Array.isArray(form.value.expenses_breakdown)) {
    form.value.expenses_breakdown = [];
  }
  useExpenseLines.value = (form.value.expenses_breakdown || []).length > 0;
  breakdownDirty.value = false;
}

watch(
  () => [props.show, props.car?.id, props.mode],
  () => {
    if (props.show && props.car) {
      resetFromCar();
    }
  },
  { immediate: true }
);

function onExpenseTotal(total) {
  if (!form.value) return;
  if (useExpenseLines.value || breakdownDirty.value) {
    if (props.mode === "sales") {
      form.value.expenses_s = total;
    } else {
      form.value.expenses = total;
    }
  }
}

function onUseLinesChange(active) {
  breakdownDirty.value = true;
  useExpenseLines.value = active;
}

function preparePayload() {
  const payload = { ...form.value };
  delete payload._expenseBreakdownSeeded;

  if (!Array.isArray(payload.expenses_breakdown)) {
    delete payload.expenses_breakdown;
    return payload;
  }

  const validLines = payload.expenses_breakdown.filter(
    (item) => String(item.description || "").trim() !== ""
  );

  if (validLines.length > 0) {
    payload.expenses_breakdown = validLines.map(({ description, purchase, sales }) => ({
      description,
      purchase: Number(purchase) || 0,
      sales: sales === null || sales === undefined || sales === ""
        ? null
        : Number(sales) || 0,
    }));
  } else if (useExpenseLines.value || breakdownDirty.value) {
    payload.expenses_breakdown = [];
  } else {
    delete payload.expenses_breakdown;
  }

  return payload;
}

async function save() {
  if (!form.value?.id || saving.value) return;
  saving.value = true;
  try {
    const payload = preparePayload();
    const url = props.mode === "sales" ? "/api/updateCarsS" : "/api/updateCarsP";
    await axios.post(url, payload);
    toast.success("تم حفظ المصاريف", {
      timeout: 2000,
      position: "bottom-right",
      rtl: true,
    });
    emit("saved", payload);
    emit("close");
  } catch (e) {
    console.error(e);
    toast.error("فشل حفظ المصاريف", {
      timeout: 2500,
      position: "bottom-right",
      rtl: true,
    });
  } finally {
    saving.value = false;
  }
}

function printExpenses() {
  if (!form.value?.id) return;
  const userId = form.value.client_id;
  if (!userId) {
    toast.warning("لا يوجد تاجر مرتبط بالسيارة للطباعة", {
      timeout: 2500,
      position: "bottom-right",
      rtl: true,
    });
    return;
  }
  window.open(
    `/api/getIndexAccountsSelas?user_id=${userId}&print=6&car_id=${form.value.id}`,
    "_blank"
  );
}
</script>

<template>
  <Modal :show="show" @close="$emit('close')">
    <template #header>
      <div class="flex items-start justify-between gap-3 w-full">
        <div>
          <h3 class="text-lg font-semibold dark:text-gray-100">تفصيل المصاريف</h3>
          <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ carTitle }}</p>
        </div>
        <div class="text-indigo-600 dark:text-indigo-300 font-bold text-xl">
          {{ displayAmount }}$
        </div>
      </div>
    </template>

    <template #body>
      <div v-if="form" class="space-y-3">
        <CarExpenseLinesEditor
          :mode="mode"
          v-model="form.expenses_breakdown"
          @total-change="onExpenseTotal"
          @use-lines-change="onUseLinesChange"
        />

        <div class="grid grid-cols-2 gap-3">
          <div v-if="mode === 'purchase'">
            <label class="dark:text-gray-200 text-sm">مجموع المشتريات</label>
            <input
              type="number"
              class="mt-1 block w-full rounded border-gray-300 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
              v-model.number="form.expenses"
              :readonly="useExpenseLines"
            />
          </div>
          <div v-else>
            <label class="dark:text-gray-200 text-sm">مجموع المبيعات</label>
            <input
              type="number"
              class="mt-1 block w-full rounded border-gray-300 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
              v-model.number="form.expenses_s"
              :readonly="useExpenseLines"
            />
          </div>
        </div>
      </div>
    </template>

    <template #footer>
      <div class="w-full space-y-2">
        <button
          type="button"
          class="w-full py-3 bg-slate-600 text-white rounded"
          @click="printExpenses"
        >
          طباعة
        </button>
        <div class="flex flex-row w-full">
          <div class="basis-1/2 px-2">
            <button
              type="button"
              class="modal-default-button w-full py-3 bg-gray-500 text-white rounded"
              @click="$emit('close')"
            >
              إغلاق
            </button>
          </div>
          <div class="basis-1/2 px-2">
            <button
              type="button"
              class="modal-default-button w-full py-3 bg-rose-600 text-white rounded disabled:opacity-50"
              :disabled="saving"
              @click="save"
            >
              {{ saving ? "جاري الحفظ..." : "حفظ" }}
            </button>
          </div>
        </div>
      </div>
    </template>
  </Modal>
</template>

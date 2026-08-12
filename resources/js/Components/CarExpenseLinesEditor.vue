<script setup>
import { computed, watch, onMounted } from "vue";

const props = defineProps({
  mode: {
    type: String,
    default: "purchase",
    validator: (value) => ["purchase", "sales"].includes(value),
  },
  modelValue: {
    type: Array,
    default: () => [],
  },
});

const emit = defineEmits(["update:modelValue", "totalChange", "useLinesChange"]);

const items = computed({
  get() {
    return Array.isArray(props.modelValue) ? props.modelValue : [];
  },
  set(value) {
    emit("update:modelValue", value);
  },
});

const useLines = computed(() => items.value.length > 0);

function emitTotal() {
  const total = props.mode === "purchase"
    ? items.value.reduce((sum, item) => sum + (Number(item.purchase) || 0), 0)
    : items.value.reduce((sum, item) => {
        const sales = item.sales ?? item.purchase;
        return sum + (Number(sales) || 0);
      }, 0);

  emit("totalChange", total);
  emit("useLinesChange", items.value.length > 0);
}

function addLine() {
  const next = [
    ...items.value,
    {
      description: "",
      purchase: 0,
      sales: props.mode === "sales" ? 0 : null,
    },
  ];
  emit("update:modelValue", next);
  emit("useLinesChange", true);
}

function removeLine(index) {
  const next = [...items.value];
  next.splice(index, 1);
  emit("update:modelValue", next);
  emitTotal();
}

function initSalesFromPurchase() {
  if (props.mode !== "sales" || !items.value.length) {
    return;
  }

  const next = items.value.map((item) => ({
    ...item,
    sales:
      item.sales === null || item.sales === undefined || item.sales === ""
        ? Number(item.purchase) || 0
        : Number(item.sales) || 0,
  }));

  const changed = JSON.stringify(next) !== JSON.stringify(items.value);
  if (changed) {
    emit("update:modelValue", next);
  }
}

onMounted(() => {
  initSalesFromPurchase();
  emitTotal();
});

watch(
  () => props.modelValue,
  () => {
    initSalesFromPurchase();
    emitTotal();
  },
  { deep: true }
);

defineExpose({ useLines });
</script>

<template>
  <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-3 mb-4">
    <div class="flex items-center justify-between gap-2 mb-3">
      <label class="dark:text-gray-200 font-medium">تفصيل المصاريف</label>
      <button
        v-if="mode === 'purchase'"
        type="button"
        class="px-3 py-1 text-sm bg-indigo-600 text-white rounded hover:bg-indigo-700"
        @click="addLine"
      >
        + إضافة بند
      </button>
    </div>

    <p v-if="!items.length" class="text-xs text-gray-500 dark:text-gray-400 mb-2">
      اختياري: أضف بنوداً (وصف + مبلغ). إن لم تُستخدم البنود، يبقى الإدخال اليدوي كما هو للسيارات القديمة.
    </p>

    <div v-if="items.length" class="overflow-x-auto">
      <table class="w-full text-sm border-collapse">
        <thead>
          <tr class="bg-gray-100 dark:bg-gray-800">
            <th class="border dark:border-gray-700 px-2 py-1 text-right">الوصف</th>
            <th v-if="mode === 'purchase'" class="border dark:border-gray-700 px-2 py-1 text-center w-28">
              مبلغ شراء $
            </th>
            <template v-else>
              <th class="border dark:border-gray-700 px-2 py-1 text-center w-24">شراء $</th>
              <th class="border dark:border-gray-700 px-2 py-1 text-center w-28">مبيعات $</th>
            </template>
            <th class="border dark:border-gray-700 px-2 py-1 w-12"></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(item, index) in items" :key="index">
            <td class="border dark:border-gray-700 px-1 py-1">
              <input
                type="text"
                class="w-full rounded border-gray-300 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900 text-sm"
                v-model="item.description"
                :readonly="mode === 'sales'"
                placeholder="مثال: رافعة"
              />
            </td>
            <td v-if="mode === 'purchase'" class="border dark:border-gray-700 px-1 py-1">
              <input
                type="number"
                min="0"
                class="w-full rounded border-gray-300 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900 text-sm text-center"
                v-model.number="item.purchase"
              />
            </td>
            <template v-else>
              <td class="border dark:border-gray-700 px-1 py-1 text-center text-gray-500 dark:text-gray-400">
                {{ item.purchase ?? 0 }}
              </td>
              <td class="border dark:border-gray-700 px-1 py-1">
                <input
                  type="number"
                  min="0"
                  class="w-full rounded border-gray-300 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900 text-sm text-center"
                  v-model.number="item.sales"
                />
              </td>
            </template>
            <td class="border dark:border-gray-700 px-1 py-1 text-center">
              <button
                v-if="mode === 'purchase'"
                type="button"
                class="text-red-600 font-bold px-1"
                @click="removeLine(index)"
              >
                ×
              </button>
            </td>
          </tr>
        </tbody>
      </table>

      <div class="mt-2 text-sm font-semibold text-indigo-700 dark:text-indigo-300 text-left">
        مجموع المصاريف:
        {{
          mode === "purchase"
            ? items.reduce((sum, item) => sum + (Number(item.purchase) || 0), 0)
            : items.reduce((sum, item) => sum + (Number(item.sales ?? item.purchase) || 0), 0)
        }}
        $
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, computed, onMounted, onBeforeUnmount } from 'vue';
import debounce from 'lodash/debounce';
import axios from 'axios';

const props = defineProps({
  modelValue: {
    type: [Number, String, null],
    default: null,
  },
  selectedUser: {
    type: Object,
    default: null,
  },
  endpoint: {
    type: String,
    default: '/api/system-config/car-expenses-wallets',
  },
  placeholder: {
    type: String,
    default: 'ابحث بالاسم أو نوع الزبون...',
  },
});

const emit = defineEmits(['update:modelValue', 'update:selectedUser']);

const open = ref(false);
const search = ref('');
const loading = ref(false);
const results = ref([]);
const meta = ref({ current_page: 1, last_page: 1, total: 0 });
const rootEl = ref(null);
const selected = ref(props.selectedUser);

watch(
  () => props.selectedUser,
  (value) => {
    selected.value = value;
  }
);

const displayText = computed(() => {
  if (!selected.value?.name) {
    return '';
  }
  const type = selected.value.type_label || selected.value.type || '—';
  return `${selected.value.name} — ${type}`;
});

async function fetchPage(page = 1) {
  loading.value = true;
  try {
    const { data } = await axios.get(props.endpoint, {
      params: {
        q: search.value || undefined,
        page,
      },
    });
    results.value = data.data || [];
    meta.value = {
      current_page: data.current_page || 1,
      last_page: data.last_page || 1,
      total: data.total || 0,
    };
  } catch (e) {
    results.value = [];
    meta.value = { current_page: 1, last_page: 1, total: 0 };
  } finally {
    loading.value = false;
  }
}

const debouncedSearch = debounce(() => {
  if (open.value) {
    fetchPage(1);
  }
}, 300);

watch(search, () => {
  debouncedSearch();
});

function toggle() {
  open.value = !open.value;
  if (open.value) {
    search.value = '';
    fetchPage(1);
  }
}

function selectItem(item) {
  selected.value = item;
  emit('update:modelValue', item.id);
  emit('update:selectedUser', item);
  open.value = false;
}

function clearSelection(e) {
  e.stopPropagation();
  selected.value = null;
  emit('update:modelValue', null);
  emit('update:selectedUser', null);
}

function onDocClick(e) {
  if (!open.value || !rootEl.value) {
    return;
  }
  if (!rootEl.value.contains(e.target)) {
    open.value = false;
  }
}

onMounted(() => {
  document.addEventListener('click', onDocClick);
});

onBeforeUnmount(() => {
  document.removeEventListener('click', onDocClick);
  debouncedSearch.cancel?.();
});
</script>

<template>
  <div ref="rootEl" class="relative w-full">
    <div
      role="button"
      tabindex="0"
      class="mt-1 flex w-full cursor-pointer items-center justify-between gap-2 rounded-md border border-gray-300 bg-white px-3 py-2 text-start text-sm text-gray-900 shadow-sm transition hover:border-indigo-400 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 dark:hover:border-indigo-400"
      @click="toggle"
      @keydown.enter.prevent="toggle"
      @keydown.space.prevent="toggle"
    >
      <span v-if="displayText" class="truncate">{{ displayText }}</span>
      <span v-else class="truncate text-gray-400 dark:text-gray-500">اختر قاصة...</span>
      <span class="flex shrink-0 items-center gap-1">
        <button
          v-if="selected"
          type="button"
          class="rounded p-0.5 text-gray-400 hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-800 dark:hover:text-gray-200"
          title="مسح"
          @click="clearSelection"
        >
          ✕
        </button>
        <svg class="h-4 w-4 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
          <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.17l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
        </svg>
      </span>
    </div>

    <div
      v-if="open"
      class="absolute z-50 mt-1 w-full overflow-hidden rounded-md border border-gray-200 bg-white shadow-lg dark:border-gray-600 dark:bg-gray-900"
    >
      <div class="border-b border-gray-200 p-2 dark:border-gray-700">
        <input
          v-model="search"
          type="search"
          :placeholder="placeholder"
          class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 placeholder:text-gray-400 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 dark:placeholder:text-gray-500"
          autofocus
          @keydown.esc="open = false"
        />
      </div>

      <div class="max-h-64 overflow-y-auto">
        <div v-if="loading" class="px-3 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
          جاري التحميل...
        </div>
        <div
          v-else-if="!results.length"
          class="px-3 py-4 text-center text-sm text-gray-500 dark:text-gray-400"
        >
          لا توجد نتائج
        </div>
        <button
          v-for="item in results"
          :key="item.id"
          type="button"
          class="flex w-full items-center justify-between gap-3 border-b border-gray-100 px-3 py-2.5 text-start text-sm last:border-b-0 hover:bg-indigo-50 dark:border-gray-800 dark:hover:bg-gray-800"
          :class="Number(modelValue) === Number(item.id) ? 'bg-indigo-50 dark:bg-indigo-950/40' : ''"
          @click="selectItem(item)"
        >
          <span class="truncate font-medium text-gray-900 dark:text-gray-100">{{ item.name }}</span>
          <span class="shrink-0 rounded bg-gray-100 px-2 py-0.5 text-xs text-gray-700 dark:bg-gray-800 dark:text-gray-300">
            {{ item.type_label || item.type || '—' }}
          </span>
        </button>
      </div>

      <div
        v-if="meta.last_page > 1"
        class="flex items-center justify-between gap-2 border-t border-gray-200 px-2 py-2 dark:border-gray-700"
      >
        <button
          type="button"
          class="rounded px-2 py-1 text-xs text-gray-700 hover:bg-gray-100 disabled:opacity-40 dark:text-gray-300 dark:hover:bg-gray-800"
          :disabled="loading || meta.current_page <= 1"
          @click="fetchPage(meta.current_page - 1)"
        >
          السابق
        </button>
        <span class="text-xs text-gray-500 dark:text-gray-400">
          {{ meta.current_page }} / {{ meta.last_page }}
          <span class="mx-1">·</span>
          {{ meta.total }}
        </span>
        <button
          type="button"
          class="rounded px-2 py-1 text-xs text-gray-700 hover:bg-gray-100 disabled:opacity-40 dark:text-gray-300 dark:hover:bg-gray-800"
          :disabled="loading || meta.current_page >= meta.last_page"
          @click="fetchPage(meta.current_page + 1)"
        >
          التالي
        </button>
      </div>
    </div>
  </div>
</template>

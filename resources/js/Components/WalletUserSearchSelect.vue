<script setup>
import { ref, watch, onMounted } from 'vue';
import debounce from 'lodash/debounce';
import axios from 'axios';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
  modelValue: {
    type: [Number, String],
    default: null,
  },
  selectedUser: {
    type: Object,
    default: null,
  },
});

const emit = defineEmits(['update:modelValue', 'update:selectedUser']);

const search = ref('');
const loading = ref(false);
const options = ref([]);
const page = ref(1);
const lastPage = ref(1);
const total = ref(0);

function syncSelectedIntoOptions() {
  if (!props.selectedUser?.id) {
    return;
  }
  const exists = options.value.some((o) => Number(o.id) === Number(props.selectedUser.id));
  if (!exists) {
    options.value = [props.selectedUser, ...options.value];
  }
}

async function fetchOptions(pageNum = 1) {
  loading.value = true;
  page.value = pageNum;
  try {
    const { data } = await axios.get('/api/system-config/car-expenses-wallets', {
      params: {
        q: search.value || undefined,
        page: pageNum,
      },
    });
    options.value = data.data || [];
    lastPage.value = data.last_page || 1;
    total.value = data.total || 0;
    syncSelectedIntoOptions();
  } catch (e) {
    options.value = [];
    lastPage.value = 1;
    total.value = 0;
    syncSelectedIntoOptions();
  } finally {
    loading.value = false;
  }
}

const debouncedFetch = debounce(() => fetchOptions(1), 300);

watch(search, () => {
  debouncedFetch();
});

watch(
  () => props.selectedUser,
  () => {
    syncSelectedIntoOptions();
  }
);

function onSelectChange(e) {
  const id = e.target.value;
  if (!id) {
    emit('update:modelValue', null);
    emit('update:selectedUser', null);
    return;
  }
  const found = options.value.find((o) => String(o.id) === String(id));
  emit('update:modelValue', Number(id));
  emit('update:selectedUser', found || { id: Number(id), name: String(id), type_label: '—' });
}

onMounted(() => {
  fetchOptions(1);
});
</script>

<template>
  <div class="mt-2 space-y-2">
    <TextInput
      v-model="search"
      type="search"
      class="mt-1 block w-full dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600"
      placeholder="ابحث بالاسم أو الرقم أو نوع الزبون..."
    />

    <select
      class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
      :value="modelValue ?? ''"
      :disabled="loading"
      @change="onSelectChange"
    >
      <option value="">— اختر قاصة —</option>
      <option
        v-for="item in options"
        :key="item.id"
        :value="item.id"
      >
        {{ item.name }} — {{ item.type_label || item.type || '—' }} (#{{ item.id }})
      </option>
    </select>

    <div class="flex items-center justify-between gap-2 text-xs text-gray-600 dark:text-gray-400">
      <button
        type="button"
        class="rounded border border-gray-300 px-2 py-1 hover:bg-gray-100 disabled:opacity-40 dark:border-gray-600 dark:hover:bg-gray-800"
        :disabled="loading || page <= 1"
        @click="fetchOptions(page - 1)"
      >
        السابق
      </button>
      <span>
        <span v-if="loading">جاري التحميل...</span>
        <span v-else>{{ page }} / {{ lastPage }} · {{ total }}</span>
      </span>
      <button
        type="button"
        class="rounded border border-gray-300 px-2 py-1 hover:bg-gray-100 disabled:opacity-40 dark:border-gray-600 dark:hover:bg-gray-800"
        :disabled="loading || page >= lastPage"
        @click="fetchOptions(page + 1)"
      >
        التالي
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { formatNumber } from '@/utils/numberFormat';

const props = defineProps({
  transfers: {
    type: Array,
    default: () => [],
  },
});

const expandedRows = ref([]);

const toggleRow = (id) => {
  const index = expandedRows.value.indexOf(id);
  if (index > -1) {
    expandedRows.value.splice(index, 1);
  } else {
    expandedRows.value.push(id);
  }
};

const getStatusBadgeClass = (status) => {
  if (status === 'تم الأستلام' || status === 'تم الاستلام') {
    return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200';
  }
  return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200';
};
</script>

<template>
  <div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
      <thead class="bg-gray-50 dark:bg-gray-900">
        <tr>
          <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-12">
          </th>
          <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
            الرقم
          </th>
          <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
            المبلغ
          </th>
          <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
            الرسوم
          </th>
          <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
            الصافي
          </th>
          <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
            الحالة
          </th>
          <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
            التاريخ
          </th>
        </tr>
      </thead>
      <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
        <template v-if="transfers.length === 0">
          <tr>
            <td colspan="7" class="px-4 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
              لا توجد حولات
            </td>
          </tr>
        </template>
        <template v-for="(transfer, index) in transfers" :key="transfer.id || index">
          <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer" @click="toggleRow(transfer.id)">
            <td class="px-4 py-3 whitespace-nowrap">
              <svg 
                class="w-5 h-5 transition-transform"
                :class="{ 'rotate-90': expandedRows.includes(transfer.id) }"
                fill="none" 
                stroke="currentColor" 
                viewBox="0 0 24 24"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
            </td>
            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
              {{ transfer.no || transfer.id }}
            </td>
            <td class="px-4 py-3 whitespace-nowrap text-sm font-semibold text-gray-900 dark:text-gray-100">
              {{ formatNumber(transfer.amount || 0) }}
            </td>
            <td class="px-4 py-3 whitespace-nowrap text-sm text-orange-600 dark:text-orange-400">
              {{ formatNumber(transfer.fee || 0) }}
            </td>
            <td class="px-4 py-3 whitespace-nowrap text-sm font-semibold text-green-600 dark:text-green-400">
              {{ formatNumber((transfer.amount || 0) - (transfer.fee || 0)) }}
            </td>
            <td class="px-4 py-3 whitespace-nowrap">
              <span 
                class="px-2 py-1 text-xs font-semibold rounded-full"
                :class="getStatusBadgeClass(transfer.stauts || transfer.status)"
              >
                {{ transfer.stauts || transfer.status || 'قيد التسليم' }}
              </span>
            </td>
            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
              {{ new Date(transfer.created_at).toLocaleDateString('ar-EG') }}
            </td>
          </tr>
          <tr v-if="expandedRows.includes(transfer.id)" class="bg-gray-50 dark:bg-gray-900">
            <td colspan="7" class="px-4 py-4">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div v-if="transfer.sender_note">
                  <p class="font-medium text-gray-700 dark:text-gray-300 mb-1">ملاحظة المرسل:</p>
                  <p class="text-gray-600 dark:text-gray-400">{{ transfer.sender_note }}</p>
                </div>
                <div v-if="transfer.receiver_note">
                  <p class="font-medium text-gray-700 dark:text-gray-300 mb-1">ملاحظة المستقبل:</p>
                  <p class="text-gray-600 dark:text-gray-400">{{ transfer.receiver_note }}</p>
                </div>
                <div v-if="transfer.note">
                  <p class="font-medium text-gray-700 dark:text-gray-300 mb-1">ملاحظات:</p>
                  <p class="text-gray-600 dark:text-gray-400">{{ transfer.note }}</p>
                </div>
                <div v-if="transfer.currency">
                  <p class="font-medium text-gray-700 dark:text-gray-300 mb-1">العملة:</p>
                  <p class="text-gray-600 dark:text-gray-400">{{ transfer.currency }}</p>
                </div>
              </div>
            </td>
          </tr>
        </template>
      </tbody>
    </table>
  </div>
</template>

